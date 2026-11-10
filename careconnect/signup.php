<?php

function signupPostValue(string $key, bool $trim = true): string
{
    $value = $_POST[$key] ?? '';

    if (!is_string($value)) {
        return '';
    }

    return $trim ? trim($value) : $value;
}

function renderSignupForm(array $errors, array $values): void
{
    $formPath = __DIR__ . DIRECTORY_SEPARATOR . 'signup.html';
    $formHtml = file_get_contents($formPath);

    if ($formHtml === false) {
        http_response_code(500);
        exit('Unable to reload the signup form.');
    }

    $validationData = json_encode(
        ['errors' => $errors, 'values' => $values],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
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
    const heading = document.querySelector('.signup-box h2');
    const message = document.createElement('p');
    message.className = 'signup-validation-message';
    message.setAttribute('role', 'alert');
    message.textContent = Object.values(validation.errors).join(' ');
    heading.insertAdjacentElement('afterend', message);

    for (const [name, value] of Object.entries(validation.values)) {
        const field = form.elements.namedItem(name);
        if (field && name !== 'password' && name !== 'confirm_password') {
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
        exit('Unable to display signup validation errors.');
    }

    echo $updatedHtml;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request.');
}

$full_name = signupPostValue('full_name');
$date_of_birth = signupPostValue('date_of_birth');
$gender = signupPostValue('gender');
$mobile = signupPostValue('mobile');
$email = signupPostValue('email');
$address = signupPostValue('address');
$new_password = signupPostValue('password', false);
$confirm_password = signupPostValue('confirm_password', false);

$values = [
    'full_name' => $full_name,
    'date_of_birth' => $date_of_birth,
    'gender' => $gender,
    'mobile' => $mobile,
    'email' => $email,
    'address' => $address,
];
$errors = [];

if ($full_name === '') {
    $errors['full_name'] = 'Please enter your full name.';
} elseif (!preg_match("/^[\p{L}]+(?:[ '\x{2019}-][\p{L}]+)*$/u", $full_name)) {
    $errors['full_name'] = 'Enter a valid name using letters, spaces, apostrophes, or hyphens.';
}

if ($date_of_birth === '') {
    $errors['date_of_birth'] = 'Please enter your date of birth.';
} else {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $date_of_birth);
    $dateErrors = DateTimeImmutable::getLastErrors();

    if (
        !$date ||
        $date->format('Y-m-d') !== $date_of_birth ||
        ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) ||
        $date_of_birth > date('Y-m-d')
    ) {
        $errors['date_of_birth'] = 'Enter a valid date of birth that is not in the future.';
    }
}

if ($gender === '') {
    $errors['gender'] = 'Please select your gender.';
} elseif (!in_array($gender, ['Male', 'Female', 'Other'], true)) {
    $errors['gender'] = 'Please select a valid gender option.';
}

if ($mobile === '') {
    $errors['mobile'] = 'Please enter your mobile number.';
} elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
    $errors['mobile'] = 'Mobile number must contain exactly 10 digits.';
}

if ($email === '') {
    $errors['email'] = 'Please enter an email address for password recovery.';
} elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($address === '') {
    $errors['address'] = 'Please enter your address.';
} elseif (
    preg_match('/^.{5,}$/us', $address) !== 1 ||
    preg_match('/[\p{L}0-9]/u', $address) !== 1
) {
    $errors['address'] = 'Please enter a proper address of at least 5 characters.';
}

if ($new_password === '') {
    $errors['password'] = 'Please enter a password.';
} elseif (
    strlen($new_password) < 8 ||
    strlen($new_password) > 72 ||
    !preg_match('/[a-z]/', $new_password) ||
    !preg_match('/[A-Z]/', $new_password) ||
    !preg_match('/[0-9]/', $new_password) ||
    !preg_match('/[^a-zA-Z0-9]/', $new_password)
) {
    $errors['password'] = 'Password must be 8-72 characters and include lowercase, uppercase, a number, and a symbol.';
}

if ($confirm_password === '') {
    $errors['confirm_password'] = 'Please confirm your password.';
} elseif ($new_password !== $confirm_password) {
    if (!isset($errors['password'])) {
        $errors['password'] = 'Password and confirm password do not match.';
    }
    $errors['confirm_password'] = 'Password and confirm password do not match.';
}

if ($errors !== []) {
    renderSignupForm($errors, $values);
    exit;
}

include 'db.php';

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "INSERT INTO patients
        (full_name, date_of_birth, gender, mobile, email, address, password)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    exit('Database error: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}

$stmt->bind_param(
    'sssssss',
    $full_name,
    $date_of_birth,
    $gender,
    $mobile,
    $email,
    $address,
    $hashed_password
);

if ($stmt->execute()) {
    $patient_id = $conn->insert_id;

    echo '<h2>Account created successfully!</h2>';
    echo '<p>Your Patient ID is: <strong>'
        . htmlspecialchars((string) $patient_id, ENT_QUOTES, 'UTF-8')
        . '</strong></p>';
    echo '<p>Please remember this Patient ID for login.</p>';
    echo "<a href='login.html'>Go to Login</a>";
} else {
    http_response_code(500);
    echo 'Error while creating account: '
        . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
}

$stmt->close();
$conn->close();

?>
