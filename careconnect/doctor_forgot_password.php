<?php

session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';

function doctorRecoveryEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function renderDoctorRecoveryPage(string $content): void
{
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CareConnect - Doctor Password Recovery</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f2f7f8; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .recovery-box { background: white; width: 100%; max-width: 420px; padding: 35px; border-radius: 12px; box-shadow: 0 4px 15px #00000012; }
        h2 { text-align: center; color: #087f8c; margin-bottom: 18px; }
        p { line-height: 1.5; }
        label { display: block; margin: 14px 0 7px; font-weight: 600; font-size: 14px; }
        input { width: 100%; padding: 13px; border: 1px solid #ccdadd; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        button { width: 100%; margin-top: 18px; background: #087f8c; color: white; padding: 13px; border: 0; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #05636e; }
        .message { padding: 10px; border-radius: 5px; background: #eef6fc; color: #24516f; }
        .back-link { text-align: center; margin-top: 18px; }
        a { color: #087f8c; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <main class="recovery-box">
        <h2>CareConnect Doctor Password Recovery</h2>
        ' . $content . '
        <p class="back-link"><a href="doctorlogin.html">Back to doctor login</a></p>
    </main>
</body>
</html>';
}

function doctorRecoveryForm(string $csrfToken, string $mode, string $message, string $fields): void
{
    $content = '';
    if ($message !== '') {
        $content .= '<p class="message" role="status">'
            . doctorRecoveryEscape($message)
            . '</p>';
    }

    $content .= '<form method="POST" action="doctor_forgot_password.php">'
        . '<input type="hidden" name="csrf_token" value="' . doctorRecoveryEscape($csrfToken) . '">'
        . '<input type="hidden" name="mode" value="' . doctorRecoveryEscape($mode) . '">'
        . $fields
        . '</form>';

    renderDoctorRecoveryPage($content);
}

function showDoctorEmailRequest(string $csrfToken, string $message = ''): void
{
    doctorRecoveryForm(
        $csrfToken,
        'request',
        $message,
        '<p>Enter the email address registered to your doctor account. If it matches an account, we will send a one-time reset code.</p>'
        . '<label for="email">Registered email</label>'
        . '<input type="email" id="email" name="email" maxlength="100" autocomplete="email" required>'
        . '<button type="submit">Send OTP</button>'
    );
}

function showDoctorOtpForm(string $csrfToken, string $message = ''): void
{
    doctorRecoveryForm(
        $csrfToken,
        'verify',
        $message,
        '<label for="otp">6-digit OTP</label>'
        . '<input type="text" id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" '
        . 'maxlength="6" autocomplete="one-time-code" required>'
        . '<button type="submit">Verify OTP</button>'
        . '<p><a href="doctor_forgot_password.php">Request a new code</a></p>'
    );
}

function showDoctorNewPasswordForm(string $csrfToken, string $message = ''): void
{
    doctorRecoveryForm(
        $csrfToken,
        'reset',
        $message,
        '<label for="password">New password</label>'
        . '<input type="password" id="password" name="password" autocomplete="new-password" required>'
        . '<label for="confirm_password">Confirm new password</label>'
        . '<input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>'
        . '<button type="submit">Reset password</button>'
    );
}

function ensureDoctorRecoveryTable(mysqli $conn): void
{
    $sql = "CREATE TABLE IF NOT EXISTS doctor_password_resets (
                doctor_id VARCHAR(20) NOT NULL PRIMARY KEY,
                otp_hash VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_doctor_password_resets_expiry (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conn->query($sql)) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
}

if (empty($_SESSION['doctor_recovery_csrf_token'])) {
    $_SESSION['doctor_recovery_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['doctor_recovery_csrf_token'];

ensureDoctorRecoveryTable($conn);

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
        showDoctorEmailRequest($csrfToken, 'Your session expired. Please try again.');
        $conn->close();
        exit;
    }
}

if ($mode === 'request') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        showDoctorEmailRequest($csrfToken);
        $conn->close();
        exit;
    }

    $email = $_POST['email'] ?? '';
    $email = is_string($email) ? trim($email) : '';
    $genericMessage = 'If that email is registered, a reset code will be sent. Check your inbox.';

    if ($email === '' || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        showDoctorEmailRequest($csrfToken, $genericMessage);
        $conn->close();
        exit;
    }

    $mailFrom = getenv('CARECONNECT_MAIL_FROM');
    if ($mailFrom === false || !filter_var($mailFrom, FILTER_VALIDATE_EMAIL)) {
        $mailFrom = ini_get('sendmail_from');
    }
    if (!is_string($mailFrom) || !filter_var($mailFrom, FILTER_VALIDATE_EMAIL)) {
        showDoctorEmailRequest($csrfToken, 'Email delivery is not configured. Please contact support.');
        $conn->close();
        exit;
    }

    $doctorStmt = $conn->prepare('SELECT doctor_id FROM doctors WHERE email = ? LIMIT 2');
    if (!$doctorStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $doctorStmt->bind_param('s', $email);
    if (!$doctorStmt->execute()) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $doctorStmt->store_result();

    if ($doctorStmt->num_rows !== 1) {
        $doctorStmt->close();
        showDoctorEmailRequest($csrfToken, $genericMessage);
        $conn->close();
        exit;
    }

    $doctorStmt->bind_result($doctorId);
    $doctorStmt->fetch();
    $doctorStmt->close();

    $recentStmt = $conn->prepare(
        'SELECT created_at FROM doctor_password_resets WHERE doctor_id = ?'
    );
    if (!$recentStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $recentStmt->bind_param('s', $doctorId);
    if (!$recentStmt->execute()) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $recentStmt->bind_result($createdAt);
    $hasRecentRequest = $recentStmt->fetch();
    $recentStmt->close();

    if ($hasRecentRequest && strtotime($createdAt) > time() - 60) {
        showDoctorEmailRequest($csrfToken, $genericMessage);
        $conn->close();
        exit;
    }

    $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $saveStmt = $conn->prepare(
        'INSERT INTO doctor_password_resets (doctor_id, otp_hash, expires_at, attempts, created_at)
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
    $saveStmt->bind_param('ss', $doctorId, $otpHash);
    if (!$saveStmt->execute()) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $saveStmt->close();

    $subject = 'CareConnect doctor password reset code';
    $body = "Your CareConnect doctor password reset code is: {$otp}\n\n"
        . "This code expires in 10 minutes. If you did not request a password reset, ignore this email.";
    $headers = "From: CareConnect <{$mailFrom}>\r\n"
        . "Content-Type: text/plain; charset=UTF-8";

    if (!mail($email, $subject, $body, $headers)) {
        $invalidateStmt = $conn->prepare(
            'UPDATE doctor_password_resets SET attempts = 5 WHERE doctor_id = ?'
        );
        if (!$invalidateStmt) {
            http_response_code(500);
            exit('Password recovery is temporarily unavailable.');
        }
        $invalidateStmt->bind_param('s', $doctorId);
        if (!$invalidateStmt->execute()) {
            http_response_code(500);
            exit('Password recovery is temporarily unavailable.');
        }
        $invalidateStmt->close();
        showDoctorEmailRequest(
            $csrfToken,
            'We could not send the reset email. Please try again later or contact support.'
        );
        $conn->close();
        exit;
    }

    $_SESSION['doctor_password_reset_pending_id'] = (string) $doctorId;
    showDoctorOtpForm($csrfToken, $genericMessage);
    $conn->close();
    exit;
}

if ($mode === 'verify') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        showDoctorEmailRequest($csrfToken);
        $conn->close();
        exit;
    }

    $pendingDoctorId = $_SESSION['doctor_password_reset_pending_id'] ?? null;
    $otp = $_POST['otp'] ?? '';
    $otp = is_string($otp) ? $otp : '';

    if (!is_string($pendingDoctorId)) {
        showDoctorEmailRequest($csrfToken, 'Your reset session expired. Request a new code.');
        $conn->close();
        exit;
    }

    $requestStmt = $conn->prepare(
        'SELECT otp_hash FROM doctor_password_resets
         WHERE doctor_id = ? AND expires_at > NOW() AND attempts < 5'
    );
    if (!$requestStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $requestStmt->bind_param('s', $pendingDoctorId);
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
                'UPDATE doctor_password_resets SET attempts = attempts + 1 WHERE doctor_id = ?'
            );
            if (!$attemptStmt) {
                http_response_code(500);
                exit('Password recovery is temporarily unavailable.');
            }
            $attemptStmt->bind_param('s', $pendingDoctorId);
            if (!$attemptStmt->execute()) {
                http_response_code(500);
                exit('Password recovery is temporarily unavailable.');
            }
            $attemptStmt->close();
        }

        showDoctorOtpForm(
            $csrfToken,
            'The OTP is invalid, expired, or has reached its attempt limit. Request a new code.'
        );
        $conn->close();
        exit;
    }

    $deleteStmt = $conn->prepare('DELETE FROM doctor_password_resets WHERE doctor_id = ?');
    if (!$deleteStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $deleteStmt->bind_param('s', $pendingDoctorId);
    if (!$deleteStmt->execute()) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $deleteStmt->close();

    session_regenerate_id(true);
    $_SESSION['doctor_password_reset_id'] = $pendingDoctorId;
    $_SESSION['doctor_password_reset_expires_at'] = time() + 600;
    unset($_SESSION['doctor_password_reset_pending_id']);
    $_SESSION['doctor_recovery_csrf_token'] = bin2hex(random_bytes(32));
    $csrfToken = $_SESSION['doctor_recovery_csrf_token'];

    showDoctorNewPasswordForm($csrfToken, 'OTP verified. Choose a new password.');
    $conn->close();
    exit;
}

if ($mode === 'reset') {
    $doctorId = $_SESSION['doctor_password_reset_id'] ?? null;
    $resetExpiresAt = $_SESSION['doctor_password_reset_expires_at'] ?? 0;

    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !is_string($doctorId) ||
        !is_int($resetExpiresAt) ||
        $resetExpiresAt < time()
    ) {
        unset($_SESSION['doctor_password_reset_id'], $_SESSION['doctor_password_reset_expires_at']);
        showDoctorEmailRequest($csrfToken, 'Your reset session expired. Request a new code.');
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
        showDoctorNewPasswordForm(
            $csrfToken,
            'Password must be 8-72 characters and include lowercase, uppercase, a number, and a symbol.'
        );
        $conn->close();
        exit;
    }

    if ($password !== $confirmPassword) {
        showDoctorNewPasswordForm($csrfToken, 'The passwords do not match. Please try again.');
        $conn->close();
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare('UPDATE doctors SET password = ? WHERE doctor_id = ?');
    if (!$updateStmt) {
        http_response_code(500);
        exit('Password recovery is temporarily unavailable.');
    }
    $updateStmt->bind_param('ss', $passwordHash, $doctorId);
    if (!$updateStmt->execute() || $updateStmt->affected_rows !== 1) {
        http_response_code(500);
        exit('Unable to reset the password. Please request a new code.');
    }
    $updateStmt->close();

    unset(
        $_SESSION['doctor_password_reset_id'],
        $_SESSION['doctor_password_reset_expires_at'],
        $_SESSION['doctor_password_reset_pending_id']
    );
    renderDoctorRecoveryPage(
        '<p class="message" role="status">Your password has been reset. '
        . '<a href="doctorlogin.html">Log in</a> with your new password.</p>'
    );
    $conn->close();
    exit;
}

http_response_code(400);
showDoctorEmailRequest($csrfToken, 'Invalid recovery request. Please start again.');
$conn->close();

?>
