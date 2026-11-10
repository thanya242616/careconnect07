<?php

session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'smtp_mailer.php';

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function renderRecoveryPage(string $content): void
{
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CareConnect - Password Recovery</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f2f7fb; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .recovery-box { background-color: white; width: 350px; padding: 35px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); }
        h1, h2 { text-align: center; }
        h1 { color: #1769aa; margin-bottom: 5px; }
        h2 { color: #333; font-size: 22px; margin-bottom: 18px; }
        p { line-height: 1.5; }
        label { display: block; margin-bottom: 6px; font-weight: bold; color: #444; }
        input { width: 100%; padding: 12px; margin-bottom: 18px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #1769aa; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #0d4f82; }
        .message { padding: 10px; border-radius: 5px; background-color: #eef6fc; color: #24516f; }
        .back-link { text-align: center; margin-top: 18px; }
    </style>
</head>
<body>
    <main class="recovery-box">
        <h1>CareConnect</h1>
        <h2>Password Recovery</h2>
        ' . $content . '
        <p class="back-link"><a href="login.html">Back to login</a></p>
    </main>
</body>
</html>';
}

function recoveryForm(string $csrfToken, string $mode, string $message, string $fields): void
{
    $content = '';

    if ($message !== '') {
        $content .= '<p class="message" role="status">' . escapeHtml($message) . '</p>';
    }

    $content .= '<form method="POST" action="forgot_password.php">'
        . '<input type="hidden" name="csrf_token" value="' . escapeHtml($csrfToken) . '">'
        . '<input type="hidden" name="mode" value="' . escapeHtml($mode) . '">'
        . $fields
        . '</form>';

    renderRecoveryPage($content);
}

function ensureRecoveryTable(mysqli $conn): void
{
    $sql = "CREATE TABLE IF NOT EXISTS patient_password_resets (
                patient_id INT NOT NULL PRIMARY KEY,
                otp_hash VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_patient_password_resets_expiry (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conn->query($sql)) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
}

function showEmailRequest(string $csrfToken, string $message = ''): void
{
    recoveryForm(
        $csrfToken,
        'request',
        $message,
        '<p>Enter your Patient ID and the email address registered to that account. We will send a one-time reset code if they match.</p>'
        . '<label for="patient_id">Patient ID</label>'
        . '<input type="text" id="patient_id" name="patient_id" inputmode="numeric" '
        . 'pattern="[0-9]+" maxlength="10" autocomplete="off" required>'
        . '<label for="email">Registered email</label>'
        . '<input type="email" id="email" name="email" maxlength="254" autocomplete="email" required>'
        . '<button type="submit">Send OTP</button>'
    );
}

function isLocalDemoOtpEnabled(): bool
{
    $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';

    return getenv('CARECONNECT_DEMO_OTP') === '1'
        && is_string($remoteAddress)
        && in_array($remoteAddress, ['127.0.0.1', '::1'], true);
}

if (empty($_SESSION['recovery_csrf_token'])) {
    $_SESSION['recovery_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['recovery_csrf_token'];

ensureRecoveryTable($conn);

$mode = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (is_string($_POST['mode'] ?? null) ? $_POST['mode'] : '')
    : 'request';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals($csrfToken, $submittedToken)
    ) {
        http_response_code(400);
        showEmailRequest($csrfToken, 'Your session expired. Please try again.');
        $conn->close();
        exit;
    }
}

if ($mode === 'request') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        showEmailRequest($csrfToken);
        $conn->close();
        exit;
    }

    $email = $_POST['email'] ?? '';
    $email = is_string($email) ? trim($email) : '';
    $patientIdInput = $_POST['patient_id'] ?? '';
    $patientIdInput = is_string($patientIdInput) ? trim($patientIdInput) : '';
    $genericMessage = 'If that email is registered, a reset code will be sent. Check your inbox.';

    if (
        !ctype_digit($patientIdInput) ||
        ($requestedPatientId = filter_var($patientIdInput, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ])) === false ||
        $email === '' ||
        strlen($email) > 254 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        showEmailRequest($csrfToken, $genericMessage);
        $conn->close();
        exit;
    }

    $demoOtpMode = isLocalDemoOtpEnabled();
    if (!$demoOtpMode && careConnectSmtpConfiguration() === null) {
        showEmailRequest(
            $csrfToken,
            'Password reset email is not configured. Please ask the site administrator to configure SMTP.'
        );
        $conn->close();
        exit;
    }

    $patientStmt = $conn->prepare(
        'SELECT patient_id FROM patients WHERE patient_id = ? AND email = ?'
    );
    if (!$patientStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $patientStmt->bind_param('is', $requestedPatientId, $email);
    if (!$patientStmt->execute()) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $patientStmt->store_result();

    if ($patientStmt->num_rows !== 1) {
        $patientStmt->close();
        showEmailRequest($csrfToken, $genericMessage);
        $conn->close();
        exit;
    }

    $patientStmt->bind_result($patientId);
    $patientStmt->fetch();
    $patientStmt->close();

    $recentStmt = $conn->prepare(
        'SELECT created_at FROM patient_password_resets WHERE patient_id = ?'
    );
    if (!$recentStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $recentStmt->bind_param('i', $patientId);
    if (!$recentStmt->execute()) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $recentStmt->bind_result($createdAt);
    $hasRecentRequest = $recentStmt->fetch();
    $recentStmt->close();

    if ($hasRecentRequest && strtotime($createdAt) > time() - 60) {
        showEmailRequest($csrfToken, $genericMessage);
        $conn->close();
        exit;
    }

    $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $saveStmt = $conn->prepare(
        'INSERT INTO patient_password_resets (patient_id, otp_hash, expires_at, attempts, created_at)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0, NOW())
         ON DUPLICATE KEY UPDATE otp_hash = VALUES(otp_hash),
                                 expires_at = VALUES(expires_at),
                                 attempts = 0,
                                 created_at = NOW()'
    );
    if (!$saveStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $saveStmt->bind_param('is', $patientId, $otpHash);
    if (!$saveStmt->execute()) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $saveStmt->close();

    $subject = 'CareConnect password reset code';
    $body = "Your CareConnect password reset code is: {$otp}\n\n"
        . "This code expires in 10 minutes. If you did not request a password reset, ignore this email.";
    if (!$demoOtpMode && !careConnectSendEmail($email, $subject, $body)) {
        $invalidateStmt = $conn->prepare(
            'DELETE FROM patient_password_resets WHERE patient_id = ?'
        );
        if (!$invalidateStmt) {
            http_response_code(500);
            exit('Password recovery is temporarily unavailable.');
        }
        $invalidateStmt->bind_param('i', $patientId);
        if (!$invalidateStmt->execute()) {
            http_response_code(500);
            exit('Password recovery is temporarily unavailable.');
        }
        $invalidateStmt->close();
        showEmailRequest(
            $csrfToken,
            'We could not send the reset email. Please check the SMTP settings or try again later.'
        );
        $conn->close();
        exit;
    }

    $_SESSION['password_reset_pending_patient_id'] = (int) $patientId;
    $verificationMessage = $demoOtpMode
        ? 'Local demo mode: use the OTP shown below. It expires in 10 minutes. Do not enable this mode on a public server.'
        : 'If that email is registered, a reset code will be sent. Check your inbox.';
    $verificationFields = '';
    if ($demoOtpMode) {
        $verificationFields .= '<p><strong>Demo OTP:</strong> <code>'
            . escapeHtml($otp)
            . '</code></p>';
    }
    $verificationFields .= '<label for="otp">6-digit OTP</label>'
        . '<input type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" '
        . 'maxlength="6" autocomplete="one-time-code" required>'
        . '<button type="submit">Verify OTP</button>';

    recoveryForm(
        $csrfToken,
        'verify',
        $verificationMessage,
        $verificationFields
    );
    $conn->close();
    exit;
}

if ($mode === 'verify') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        showEmailRequest($csrfToken);
        $conn->close();
        exit;
    }

    $otp = $_POST['otp'] ?? '';
    $otp = is_string($otp) ? $otp : '';
    $pendingPatientId = $_SESSION['password_reset_pending_patient_id'] ?? null;

    if (!is_int($pendingPatientId)) {
        showEmailRequest($csrfToken, 'Your reset session expired. Request a new code.');
        $conn->close();
        exit;
    }

    $requestStmt = $conn->prepare(
        'SELECT otp_hash
         FROM patient_password_resets
         WHERE patient_id = ? AND expires_at > NOW() AND attempts < 5'
    );
    if (!$requestStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $requestStmt->bind_param('i', $pendingPatientId);
    if (!$requestStmt->execute()) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $requestStmt->bind_result($otpHash);
    $hasActiveRequest = $requestStmt->fetch();
    $requestStmt->close();

    if (
        !$hasActiveRequest ||
        !preg_match('/^[0-9]{6}$/', $otp) ||
        !password_verify($otp, $otpHash)
    ) {
        if ($hasActiveRequest) {
            $attemptStmt = $conn->prepare(
                'UPDATE patient_password_resets SET attempts = attempts + 1 WHERE patient_id = ?'
            );
            if (!$attemptStmt) {
                http_response_code(500);
                exit('Password recovery is temporarily unavailable.');
            }
            $attemptStmt->bind_param('i', $pendingPatientId);
            if (!$attemptStmt->execute()) {
                http_response_code(500);
                exit('Password recovery is temporarily unavailable.');
            }
            $attemptStmt->close();
        }

        recoveryForm(
            $csrfToken,
            'verify',
            'The OTP is invalid, expired, or has reached its attempt limit. Request a new code.',
            '<label for="otp">6-digit OTP</label>'
            . '<input type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" '
            . 'maxlength="6" autocomplete="one-time-code" required>'
            . '<button type="submit">Verify OTP</button>'
            . '<p><a href="forgot_password.php">Request a new code</a></p>'
        );
        $conn->close();
        exit;
    }

    $_SESSION['password_reset_patient_id'] = $pendingPatientId;
    $_SESSION['password_reset_expires_at'] = time() + 600;
    unset($_SESSION['password_reset_pending_patient_id']);

    $deleteStmt = $conn->prepare(
        'DELETE FROM patient_password_resets WHERE patient_id = ?'
    );
    if (!$deleteStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $deleteStmt->bind_param('i', $pendingPatientId);
    if (!$deleteStmt->execute()) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $deleteStmt->close();

    session_regenerate_id(true);
    recoveryForm(
        $csrfToken,
        'reset',
        'OTP verified. Choose a new password.',
        '<label for="password">New password</label>'
        . '<input type="password" id="password" name="password" autocomplete="new-password" required>'
        . '<label for="confirm_password">Confirm new password</label>'
        . '<input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>'
        . '<button type="submit">Reset password</button>'
    );
    $conn->close();
    exit;
}

if ($mode === 'reset') {
    $patientId = $_SESSION['password_reset_patient_id'] ?? null;
    $resetExpiresAt = $_SESSION['password_reset_expires_at'] ?? 0;

    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !is_int($patientId) ||
        !is_int($resetExpiresAt) ||
        $resetExpiresAt < time()
    ) {
        unset($_SESSION['password_reset_patient_id'], $_SESSION['password_reset_expires_at']);
        showEmailRequest($csrfToken, 'Your reset session expired. Request a new code.');
        $conn->close();
        exit;
    }

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $password = is_string($password) ? $password : '';
    $confirmPassword = is_string($confirmPassword) ? $confirmPassword : '';

    if (
        strlen($password) < 8 ||
        strlen($password) > 72 ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^a-zA-Z0-9]/', $password)
    ) {
        recoveryForm(
            $csrfToken,
            'reset',
            'Password must be 8-72 characters and include lowercase, uppercase, a number, and a symbol.',
            '<label for="password">New password</label>'
            . '<input type="password" id="password" name="password" autocomplete="new-password" required>'
            . '<label for="confirm_password">Confirm new password</label>'
            . '<input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>'
            . '<button type="submit">Reset password</button>'
        );
        $conn->close();
        exit;
    }

    if ($password !== $confirmPassword) {
        recoveryForm(
            $csrfToken,
            'reset',
            'The passwords do not match. Please try again.',
            '<label for="password">New password</label>'
            . '<input type="password" id="password" name="password" autocomplete="new-password" required>'
            . '<label for="confirm_password">Confirm new password</label>'
            . '<input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>'
            . '<button type="submit">Reset password</button>'
        );
        $conn->close();
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare('UPDATE patients SET password = ? WHERE patient_id = ?');
    if (!$updateStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $updateStmt->bind_param('si', $passwordHash, $patientId);
    if (!$updateStmt->execute() || $updateStmt->affected_rows !== 1) {
        http_response_code(500);
        exit('Unable to reset the password. Please request a new code.');
    }
    $updateStmt->close();

    unset($_SESSION['password_reset_patient_id'], $_SESSION['password_reset_expires_at']);
    renderRecoveryPage(
        '<p class="message" role="status">Your password has been reset. <a href="login.html">Log in</a> with your new password.</p>'
    );
    $conn->close();
    exit;
}

http_response_code(400);
showEmailRequest($csrfToken, 'Invalid recovery request. Please start again.');
$conn->close();

?>
