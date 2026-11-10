<?php

function doctorSignupPostValue(string $key, bool $trim = true): string
{
    $value = $_POST[$key] ?? '';

    if (!is_string($value)) {
        return '';
    }

    return $trim ? trim($value) : $value;
}

function doctorSignupCharacterCount(string $value): ?int
{
    $count = preg_match_all('/./us', $value, $matches);

    return $count === false ? null : $count;
}

function renderDoctorSignupForm(array $errors, array $values): void
{
    $formPath = __DIR__ . DIRECTORY_SEPARATOR . 'doctorsignup.html';
    $formHtml = file_get_contents($formPath);

    if ($formHtml === false) {
        http_response_code(500);
        exit('Unable to reload the doctor signup form.');
    }

    $validationData = json_encode(
        ['errors' => $errors, 'values' => $values],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT |
        JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
    );

    $validationScript = <<<HTML
<style>
    .signup-validation-error {
        border: 2px solid #c62828 !important;
        background-color: #fff3f3 !important;
    }
    .signup-validation-message {
        margin: 0 0 18px;
        padding: 10px;
        border: 1px solid #c62828;
        border-radius: 5px;
        background-color: #fff3f3;
        color: #9b1c1c;
    }
</style>
<script>
    const validation = {$validationData};
    const form = document.querySelector('form');
    const heading = document.querySelector('.container h2');
    const message = document.createElement('p');
    message.className = 'signup-validation-message';
    message.setAttribute('role', 'alert');
    message.textContent = Object.values(validation.errors).join(' ');
    heading.insertAdjacentElement('afterend', message);

    for (const [name, value] of Object.entries(validation.values)) {
        const field = form.elements.namedItem(name);
        if (field) {
            field.value = value;
        }
    }

    const firstInvalidField = Object.keys(validation.errors)
        .map(name => form.elements.namedItem(name))
        .find(field => field);

    for (const name of Object.keys(validation.errors)) {
        const field = form.elements.namedItem(name);
        if (field) {
            field.classList.add('signup-validation-error');
            field.setAttribute('aria-invalid', 'true');
        }
    }

    if (firstInvalidField) {
        firstInvalidField.focus();
    }
</script>
HTML;

    $updatedHtml = str_replace('</body>', $validationScript . '</body>', $formHtml, $count);

    if ($count !== 1) {
        http_response_code(500);
        exit('Unable to display doctor signup validation errors.');
    }

    echo $updatedHtml;
}

function doctorSignupErrorPage(string $message): void
{
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="en"><meta charset="UTF-8"><title>Doctor Signup</title>'
        . '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<a href="doctorsignup.html">Back to signup</a></html>';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request.');
}

$doctor_id = strtoupper(doctorSignupPostValue('doctor_id'));
$doctor_name = doctorSignupPostValue('doctor_name');
$email = doctorSignupPostValue('email');
$mobile = doctorSignupPostValue('mobile');
$specialization = doctorSignupPostValue('specialization');
$hospital = doctorSignupPostValue('hospital');
$registration_no = strtoupper(doctorSignupPostValue('registration_no'));
$password = doctorSignupPostValue('password', false);
$confirm_password = doctorSignupPostValue('confirm_password', false);

$values = [
    'doctor_id' => $doctor_id,
    'doctor_name' => $doctor_name,
    'email' => $email,
    'mobile' => $mobile,
    'specialization' => $specialization,
    'hospital' => $hospital,
    'registration_no' => $registration_no,
];
$errors = [];

if ($doctor_id === '') {
    $errors['doctor_id'] = 'Please enter a doctor ID.';
} elseif (!preg_match('/^[A-Z0-9][A-Z0-9_-]{2,19}$/', $doctor_id)) {
    $errors['doctor_id'] = 'Doctor ID must be 3-20 characters using letters, numbers, hyphens, or underscores.';
}

if ($doctor_name === '') {
    $errors['doctor_name'] = 'Please enter your name.';
} elseif (
    ($nameLength = doctorSignupCharacterCount($doctor_name)) === null ||
    $nameLength < 2 ||
    $nameLength > 100 ||
    !preg_match("/^[\p{L}\p{M}]+(?:[ '\x{2019}-][\p{L}\p{M}]+)*$/u", $doctor_name)
) {
    $errors['doctor_name'] = 'Enter a valid name using letters, spaces, apostrophes, or hyphens (2-100 characters).';
}

if ($email === '') {
    $errors['email'] = 'Please enter your email address.';
} elseif (
    strlen($email) > 100 ||
    filter_var($email, FILTER_VALIDATE_EMAIL) === false
) {
    $errors['email'] = 'Enter a valid email address of 100 characters or fewer.';
}

if ($mobile === '') {
    $errors['mobile'] = 'Please enter your mobile number.';
} elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
    $errors['mobile'] = 'Mobile number must contain exactly 10 digits.';
}

if ($specialization === '') {
    $errors['specialization'] = 'Please select a specialization.';
} elseif (!in_array($specialization, [
    'General Medicine',
    'Cardiology',
    'Dermatology',
    'Orthopedics',
    'Neurology',
    'Pediatrics',
    'Gynecology'
], true)) {
    $errors['specialization'] = 'Please select a valid specialization.';
}

if ($hospital === '') {
    $errors['hospital'] = 'Please enter your hospital name.';
} elseif (
    ($hospitalLength = doctorSignupCharacterCount($hospital)) === null ||
    $hospitalLength < 2 ||
    $hospitalLength > 150 ||
    preg_match('/[\x00-\x1F\x7F]/', $hospital) ||
    !preg_match('/[\p{L}\p{N}]/u', $hospital)
) {
    $errors['hospital'] = 'Enter a valid hospital name (2-150 characters).';
}

if ($registration_no === '') {
    $errors['registration_no'] = 'Please enter your medical registration number.';
} elseif (!preg_match('/^[A-Z0-9][A-Z0-9\/ .-]{1,49}$/', $registration_no)) {
    $errors['registration_no'] = 'Registration number must be 2-50 characters using letters, numbers, spaces, /, . or -.';
}

if ($password === '') {
    $errors['password'] = 'Please enter a password.';
} elseif (
    strlen($password) < 8 ||
    strlen($password) > 72 ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[^a-zA-Z0-9]/', $password)
) {
    $errors['password'] = 'Password must be 8-72 characters and include lowercase, uppercase, a number, and a symbol.';
}

if ($confirm_password === '') {
    $errors['confirm_password'] = 'Please confirm your password.';
} elseif ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Password and confirm password do not match.';
}

if ($errors !== []) {
    renderDoctorSignupForm($errors, $values);
    exit;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';

$duplicateChecks = [
    'doctor_id' => ['doctor_id', $doctor_id, 'Doctor ID is already registered.'],
    'email' => ['email', $email, 'Email address is already registered.'],
    'registration_no' => [
        'registration_no',
        $registration_no,
        'Medical registration number is already registered.'
    ],
];

foreach ($duplicateChecks as $field => [$column, $value, $errorMessage]) {
    $checkStmt = $conn->prepare("SELECT 1 FROM doctors WHERE {$column} = ? LIMIT 1");

    if (!$checkStmt) {
        doctorSignupErrorPage('Database error: ' . $conn->error);
        $conn->close();
        exit;
    }

    $checkStmt->bind_param('s', $value);

    if (!$checkStmt->execute()) {
        $databaseError = $checkStmt->error;
        $checkStmt->close();
        doctorSignupErrorPage('Database error: ' . $databaseError);
        $conn->close();
        exit;
    }

    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        $errors[$field] = $errorMessage;
    }
    $checkStmt->close();
}

if ($errors !== []) {
    renderDoctorSignupForm($errors, $values);
    $conn->close();
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO doctors
        (doctor_id, doctor_name, email, mobile, specialization, hospital, registration_no, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    doctorSignupErrorPage('Database error: ' . $conn->error);
    $conn->close();
    exit;
}

$stmt->bind_param(
    'ssssssss',
    $doctor_id,
    $doctor_name,
    $email,
    $mobile,
    $specialization,
    $hospital,
    $registration_no,
    $hashed_password
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo "<script>alert('Doctor Account Created Successfully!'); window.location.href='doctorlogin.html';</script>";
    exit;
}

if ($stmt->errno === 1062) {
    $stmt->close();
    $conn->close();
    renderDoctorSignupForm(
        ['doctor_id' => 'Doctor ID, email address, or registration number is already registered.'],
        $values
    );
    exit;
}

$databaseError = $stmt->error;
$stmt->close();
$conn->close();
doctorSignupErrorPage('Signup failed: ' . $databaseError);

?>
