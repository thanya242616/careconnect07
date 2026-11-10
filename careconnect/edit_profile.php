<?php
session_start();
include "db.php";

/* Selected language from language.php */
$lang = $_SESSION['language'] ?? 'en';
if (!in_array($lang, ['en', 'ta', 'hi'], true)) {
    $lang = 'en';
}

$translations = [
    'en' => [
        'page_title' => 'CareConnect - Patient Profile',
        'dashboard' => 'Dashboard',
        'my_profile' => 'My Profile',
        'book_appointment' => 'Book Appointment',
        'previous_reports' => 'Previous Reports',
        'appointment_status' => 'Appointment Status',
        'online_consultation' => 'Online Consultation',
        'health_issue' => 'Current Health Issue',
        'logout' => 'Logout',
        'heading' => 'Patient Profile',
        'back' => '← Back to Dashboard',
        'patient' => 'Patient',
        'choose_photo' => 'Choose Profile Photo',
        'personal_info' => 'Personal Information',
        'patient_id' => 'Patient ID',
        'full_name' => 'Full Name',
        'age' => 'Age',
        'gender' => 'Gender',
        'select_gender' => 'Select Gender',
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
        'mobile' => 'Mobile Number',
        'email' => 'Email Address',
        'blood_group' => 'Blood Group',
        'select_blood' => 'Select Blood Group',
        'address' => 'Address',
        'edit' => 'Edit Profile',
        'cancel' => 'Cancel',
        'save' => 'Save Changes',
        'required' => 'Please fill all required fields.',
        'invalid_age' => 'Please enter a valid age between 1 and 120.',
        'photo_size' => 'Photo size must be less than 5 MB.',
        'photo_type' => 'Only JPG, JPEG, PNG, GIF and WEBP images are allowed.',
        'photo_failed' => 'Photo upload failed.',
        'photo_error' => 'Error while uploading photo.',
        'saved' => 'Profile changes saved successfully!',
        'db_error' => 'Database error: ',
        'update_error' => 'Update error: ',
        'not_found' => 'Patient profile not found.',
        'profile_photo_alt' => 'Patient Profile Photo',
    ],
    'ta' => [
        'page_title' => 'CareConnect - நோயாளி சுயவிவரம்',
        'dashboard' => 'முகப்பு',
        'my_profile' => 'எனது சுயவிவரம்',
        'book_appointment' => 'மருத்துவர் சந்திப்பு பதிவு',
        'previous_reports' => 'முந்தைய மருத்துவ அறிக்கைகள்',
        'appointment_status' => 'சந்திப்பு நிலை',
        'online_consultation' => 'ஆன்லைன் ஆலோசனை',
        'health_issue' => 'தற்போதைய உடல்நலப் பிரச்சினை',
        'logout' => 'வெளியேறு',
        'heading' => 'நோயாளி சுயவிவரம்',
        'back' => '← முகப்புக்குத் திரும்பு',
        'patient' => 'நோயாளி',
        'choose_photo' => 'சுயவிவரப் புகைப்படத்தைத் தேர்வு செய்',
        'personal_info' => 'தனிப்பட்ட விவரங்கள்',
        'patient_id' => 'நோயாளி ID',
        'full_name' => 'முழுப் பெயர்',
        'age' => 'வயது',
        'gender' => 'பாலினம்',
        'select_gender' => 'பாலினத்தைத் தேர்ந்தெடுக்கவும்',
        'male' => 'ஆண்',
        'female' => 'பெண்',
        'other' => 'மற்றவை',
        'mobile' => 'கைபேசி எண்',
        'email' => 'மின்னஞ்சல் முகவரி',
        'blood_group' => 'இரத்த வகை',
        'select_blood' => 'இரத்த வகையைத் தேர்ந்தெடுக்கவும்',
        'address' => 'முகவரி',
        'edit' => 'சுயவிவரத்தைத் திருத்து',
        'cancel' => 'ரத்து செய்',
        'save' => 'மாற்றங்களைச் சேமி',
        'required' => 'தேவையான அனைத்து விவரங்களையும் நிரப்பவும்.',
        'invalid_age' => '1 முதல் 120 வரை சரியான வயதை உள்ளிடவும்.',
        'photo_size' => 'புகைப்படத்தின் அளவு 5 MB-க்கு குறைவாக இருக்க வேண்டும்.',
        'photo_type' => 'JPG, JPEG, PNG, GIF அல்லது WEBP படங்களை மட்டும் பதிவேற்றவும்.',
        'photo_failed' => 'புகைப்படத்தைப் பதிவேற்ற முடியவில்லை.',
        'photo_error' => 'புகைப்படத்தைப் பதிவேற்றும்போது பிழை ஏற்பட்டது.',
        'saved' => 'சுயவிவர மாற்றங்கள் வெற்றிகரமாகச் சேமிக்கப்பட்டன!',
        'db_error' => 'தரவுத்தளப் பிழை: ',
        'update_error' => 'புதுப்பிப்பு பிழை: ',
        'not_found' => 'நோயாளியின் சுயவிவரம் கிடைக்கவில்லை.',
        'profile_photo_alt' => 'நோயாளியின் சுயவிவரப் புகைப்படம்',
    ],
    'hi' => [
        'page_title' => 'CareConnect - मरीज की प्रोफ़ाइल',
        'dashboard' => 'डैशबोर्ड',
        'my_profile' => 'मेरी प्रोफ़ाइल',
        'book_appointment' => 'अपॉइंटमेंट बुक करें',
        'previous_reports' => 'पिछली मेडिकल रिपोर्ट',
        'appointment_status' => 'अपॉइंटमेंट स्थिति',
        'online_consultation' => 'ऑनलाइन परामर्श',
        'health_issue' => 'वर्तमान स्वास्थ्य समस्या',
        'logout' => 'लॉग आउट',
        'heading' => 'मरीज की प्रोफ़ाइल',
        'back' => '← डैशबोर्ड पर वापस जाएँ',
        'patient' => 'मरीज',
        'choose_photo' => 'प्रोफ़ाइल फ़ोटो चुनें',
        'personal_info' => 'व्यक्तिगत जानकारी',
        'patient_id' => 'मरीज ID',
        'full_name' => 'पूरा नाम',
        'age' => 'उम्र',
        'gender' => 'लिंग',
        'select_gender' => 'लिंग चुनें',
        'male' => 'पुरुष',
        'female' => 'महिला',
        'other' => 'अन्य',
        'mobile' => 'मोबाइल नंबर',
        'email' => 'ईमेल पता',
        'blood_group' => 'ब्लड ग्रुप',
        'select_blood' => 'ब्लड ग्रुप चुनें',
        'address' => 'पता',
        'edit' => 'प्रोफ़ाइल संपादित करें',
        'cancel' => 'रद्द करें',
        'save' => 'बदलाव सेव करें',
        'required' => 'कृपया सभी आवश्यक फ़ील्ड भरें।',
        'invalid_age' => 'कृपया 1 से 120 के बीच सही उम्र दर्ज करें।',
        'photo_size' => 'फ़ोटो का आकार 5 MB से कम होना चाहिए।',
        'photo_type' => 'केवल JPG, JPEG, PNG, GIF और WEBP छवियाँ स्वीकार हैं।',
        'photo_failed' => 'फ़ोटो अपलोड नहीं हो सकी।',
        'photo_error' => 'फ़ोटो अपलोड करते समय त्रुटि हुई।',
        'saved' => 'प्रोफ़ाइल बदलाव सफलतापूर्वक सेव हुए!',
        'db_error' => 'डेटाबेस त्रुटि: ',
        'update_error' => 'अपडेट त्रुटि: ',
        'not_found' => 'मरीज की प्रोफ़ाइल नहीं मिली।',
        'profile_photo_alt' => 'मरीज की प्रोफ़ाइल फ़ोटो',
    ],
];
$t = $translations[$lang];

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.html");
    exit();
}

$patient_id = (int)$_SESSION['patient_id'];
$message = "";
$message_type = "";

/* UPDATE PROFILE */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST['full_name'] ?? '');
    $posted_age = trim($_POST['age'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');

    if ($full_name === '' || $posted_age === '' || $gender === '' || $mobile === '') {
        $message = $t['required'];
        $message_type = "error";
    } elseif (!ctype_digit($posted_age) || (int)$posted_age < 1 || (int)$posted_age > 120) {
        $message = $t['invalid_age'];
        $message_type = "error";
    } else {
        $age_to_save = (int)$posted_age;

        /* Fetch old photo */
        $old_photo = "";
        $photo_stmt = $conn->prepare("SELECT profile_photo FROM patients WHERE patient_id = ?");
        if ($photo_stmt) {
            $photo_stmt->bind_param("i", $patient_id);
            $photo_stmt->execute();
            $photo_result = $photo_stmt->get_result();
            if ($photo_result->num_rows === 1) {
                $photo_data = $photo_result->fetch_assoc();
                $old_photo = $photo_data['profile_photo'] ?? '';
            }
            $photo_stmt->close();
        }

        $new_photo = $old_photo;
        $upload_folder = "patient_uploads/";
        $photo_uploaded = false;

        /* Upload a new profile photo if selected */
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
                $message = $t['photo_error'];
                $message_type = "error";
            } elseif ($_FILES['profile_photo']['size'] > 5 * 1024 * 1024) {
                $message = $t['photo_size'];
                $message_type = "error";
            } else {
                $tmp_name = $_FILES['profile_photo']['tmp_name'];
                $image_info = @getimagesize($tmp_name);
                $mime = $image_info['mime'] ?? '';
                $allowed_mimes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ];

                if (!$image_info || !isset($allowed_mimes[$mime])) {
                    $message = $t['photo_type'];
                    $message_type = "error";
                } else {
                    $extension = $allowed_mimes[$mime];
                    if (!is_dir($upload_folder) && !mkdir($upload_folder, 0755, true) && !is_dir($upload_folder)) {
                        $message = $t['photo_failed'];
                        $message_type = "error";
                    } else {
                        $new_file_name = "patient_" . $patient_id . "_" . bin2hex(random_bytes(8)) . "." . $extension;
                        $upload_path = $upload_folder . $new_file_name;

                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            $new_photo = $new_file_name;
                            $photo_uploaded = true;
                        } else {
                            $message = $t['photo_failed'];
                            $message_type = "error";
                        }
                    }
                }
            }
        }

        /* Save profile only when photo processing succeeded */
        if ($message === "") {
            $update_sql = "UPDATE patients
                           SET full_name = ?, age = ?, gender = ?, mobile = ?,
                               email = ?, address = ?, blood_group = ?, profile_photo = ?
                           WHERE patient_id = ?";
            $update_stmt = $conn->prepare($update_sql);

            if (!$update_stmt) {
                if ($photo_uploaded && is_file($upload_folder . $new_photo)) {
                    unlink($upload_folder . $new_photo);
                }
                $message = $t['update_error'] . $conn->error;
                $message_type = "error";
            } else {
                $update_stmt->bind_param(
                    "sissssssi",
                    $full_name,
                    $age_to_save,
                    $gender,
                    $mobile,
                    $email,
                    $address,
                    $blood_group,
                    $new_photo,
                    $patient_id
                );

                if ($update_stmt->execute()) {
                    $_SESSION['patient_name'] = $full_name;
                    $message = $t['saved'];
                    $message_type = "success";

                    /* Remove old photo only after the database update succeeded */
                    if (
                        $photo_uploaded &&
                        $old_photo !== '' &&
                        basename($old_photo) === $old_photo &&
                        is_file($upload_folder . $old_photo)
                    ) {
                        unlink($upload_folder . $old_photo);
                    }
                } else {
                    if ($photo_uploaded && is_file($upload_folder . $new_photo)) {
                        unlink($upload_folder . $new_photo);
                    }
                    $message = $t['update_error'] . $update_stmt->error;
                    $message_type = "error";
                }
                $update_stmt->close();
            }
        }
    }
}

/* GET CURRENT PATIENT */
$sql = "SELECT patient_id, full_name, age, date_of_birth, gender, mobile,
               email, address, blood_group, profile_photo
        FROM patients
        WHERE patient_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(h($t['db_error'] . $conn->error));
}

$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die(h($t['not_found']));
}

$patient = $result->fetch_assoc();
$stmt->close();

/* Calculate age from date of birth if the age column is empty */
$age = $patient['age'] ?? '';
if (($age === '' || $age === null) && !empty($patient['date_of_birth'])) {
    try {
        $dob = new DateTime($patient['date_of_birth']);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
    } catch (Exception $e) {
        $age = '';
    }
}

$profile_photo = !empty($patient['profile_photo'])
    ? "patient_uploads/" . rawurlencode(basename($patient['profile_photo']))
    : "https://via.placeholder.com/125";
?>
<!DOCTYPE html>
<html lang="<?php echo h($lang); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h($t['page_title']); ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif}
body{background:#f2f7fb;color:#263746}
.sidebar{width:240px;height:100vh;background:#123c55;color:white;position:fixed;left:0;top:0;padding:25px 15px;overflow-y:auto}
.logo{font-size:25px;font-weight:bold;text-align:center;margin-bottom:35px}
.sidebar a{display:block;color:white;text-decoration:none;padding:13px;margin:7px 0;border-radius:7px;font-size:14px}
.sidebar a:hover,.sidebar a.active{background:#1e617d}
.main{margin-left:240px;min-height:100vh;padding:28px 40px 50px}
.header{width:100%;max-width:920px;margin:0 auto 22px;display:flex;justify-content:space-between;align-items:center;gap:15px}
.header h1{color:#123c55;font-size:28px}
.back{color:#087bb5;text-decoration:none;font-weight:bold}
.back:hover{text-decoration:underline}
.profile-card{width:100%;max-width:920px;margin:0 auto;background:white;padding:28px 34px 30px;border-radius:14px;box-shadow:0 4px 15px rgba(0,0,0,.06)}
.profile-top{text-align:center;padding-bottom:22px;border-bottom:1px solid #e7edf1;margin-bottom:24px}
.photo{width:115px;height:115px;border-radius:50%;object-fit:cover;border:4px solid #dcecf4;background:#eaf3f8}
.profile-top h2{margin-top:12px;color:#123c55;font-size:24px}
.profile-top p{color:#71818d;margin-top:5px}
.photo-upload{margin-top:14px}
.photo-label{display:inline-block;padding:9px 15px;background:#087eae;color:white;border-radius:6px;cursor:pointer;font-size:14px;font-weight:bold}
.photo-label:hover{background:#056b94}
.section-title{color:#123c55;margin-bottom:18px;font-size:19px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;column-gap:22px;row-gap:18px}
.field{display:flex;flex-direction:column;gap:7px;min-width:0}
.field label{font-size:14px;font-weight:bold;color:#526777}
.field input,.field select,.field textarea{width:100%;padding:11px 12px;border:1px solid #ccd9e2;border-radius:7px;background:#f8fafc;color:#263746;outline:none;font-size:14px}
.field input{height:45px}
.field textarea{min-height:90px;resize:vertical}
.field input:disabled,.field select:disabled,.field textarea:disabled{background:#f1f4f6;color:#647583;cursor:default}
.field input:focus,.field select:focus,.field textarea:focus{border-color:#1684b5;background:white}
.patient-id{background:#eaf3f8!important;color:#526777!important;font-weight:bold}
.buttons{display:flex;justify-content:flex-end;gap:12px;margin-top:26px;flex-wrap:wrap}
button{padding:11px 19px;border:none;border-radius:7px;cursor:pointer;font-weight:bold;font-size:14px}
.edit-btn,.save-btn{background:#087eae;color:white}
.edit-btn:hover,.save-btn:hover{background:#056b94}
.cancel-btn{background:#e8edf1;color:#344b5a}
.cancel-btn:hover{background:#dbe2e7}
#saveBtn,#cancelBtn{display:none}
.message{text-align:right;margin-top:14px;font-size:14px;font-weight:bold;overflow-wrap:anywhere}
.success{color:#237744}.error{color:#c0392b}
@media(max-width:800px){.sidebar{width:100%;height:auto;position:relative}.main{margin-left:0;padding:20px}.header{flex-direction:column;align-items:flex-start}.form-grid{grid-template-columns:1fr}.profile-card{padding:25px 20px}}
</style>
</head>
<body>
<div class="sidebar">
    <div class="logo">CareConnect</div>
    <a href="patient.php"><?php echo h($t['dashboard']); ?></a>
    <a href="edit_profile.php" class="active"><?php echo h($t['my_profile']); ?></a>
    <a href="appointment.php"><?php echo h($t['book_appointment']); ?></a>
    <a href="upload_report.php"><?php echo h($t['previous_reports']); ?></a>
    <a href="confirmation.php"><?php echo h($t['appointment_status']); ?></a>
    <a href="online_consultation.php"><?php echo h($t['online_consultation']); ?></a>
    <a href="healthissue.php"><?php echo h($t['health_issue']); ?></a>
    <a href="logout.php"><?php echo h($t['logout']); ?></a>
</div>

<div class="main">
    <div class="header">
        <h1><?php echo h($t['heading']); ?></h1>
        <a href="patient.php" class="back"><?php echo h($t['back']); ?></a>
    </div>

    <div class="profile-card">
        <div class="profile-top">
            <img src="<?php echo h($profile_photo); ?>" id="profilePhoto" class="photo" alt="<?php echo h($t['profile_photo_alt']); ?>">
            <h2><?php echo h($patient['full_name']); ?></h2>
            <p><?php echo h($t['patient']); ?></p>
            <div class="photo-upload">
                <label for="profilePhotoInput" class="photo-label" id="photoLabel" style="display:none;">
                    <?php echo h($t['choose_photo']); ?>
                </label>
            </div>
        </div>

        <h3 class="section-title"><?php echo h($t['personal_info']); ?></h3>

        <form method="POST" action="edit_profile.php" enctype="multipart/form-data" id="profileForm">
            <div class="form-grid">
                <div class="field">
                    <label><?php echo h($t['patient_id']); ?></label>
                    <input type="text" value="<?php echo h($patient['patient_id']); ?>" class="patient-id" disabled>
                </div>

                <div class="field">
                    <label for="fullName"><?php echo h($t['full_name']); ?></label>
                    <input type="text" name="full_name" id="fullName" value="<?php echo h($patient['full_name']); ?>" disabled required>
                </div>

                <div class="field">
                    <label for="age"><?php echo h($t['age']); ?></label>
                    <input type="number" name="age" id="age" value="<?php echo h($age); ?>" disabled min="1" max="120" required>
                </div>

                <div class="field">
                    <label for="gender"><?php echo h($t['gender']); ?></label>
                    <select name="gender" id="gender" disabled required>
                        <option value=""><?php echo h($t['select_gender']); ?></option>
                        <option value="Male" <?php echo (($patient['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>><?php echo h($t['male']); ?></option>
                        <option value="Female" <?php echo (($patient['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>><?php echo h($t['female']); ?></option>
                        <option value="Other" <?php echo (($patient['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>><?php echo h($t['other']); ?></option>
                    </select>
                </div>

                <div class="field">
                    <label for="mobile"><?php echo h($t['mobile']); ?></label>
                    <input type="tel" name="mobile" id="mobile" value="<?php echo h($patient['mobile']); ?>" disabled required>
                </div>

                <div class="field">
                    <label for="email"><?php echo h($t['email']); ?></label>
                    <input type="email" name="email" id="email" value="<?php echo h($patient['email'] ?? ''); ?>" disabled>
                </div>

                <div class="field">
                    <label for="bloodGroup"><?php echo h($t['blood_group']); ?></label>
                    <select name="blood_group" id="bloodGroup" disabled>
                        <option value=""><?php echo h($t['select_blood']); ?></option>
                        <?php
                        $blood_groups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
                        foreach ($blood_groups as $blood) {
                            $selected = (($patient['blood_group'] ?? '') === $blood) ? "selected" : "";
                            echo '<option value="' . h($blood) . '" ' . $selected . '>' . h($blood) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="field">
                    <label for="address"><?php echo h($t['address']); ?></label>
                    <textarea name="address" id="address" disabled><?php echo h($patient['address'] ?? ''); ?></textarea>
                </div>
            </div>

            <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;">

            <div class="buttons">
                <button type="button" class="edit-btn" id="editBtn" onclick="enableEdit()"><?php echo h($t['edit']); ?></button>
                <button type="button" class="cancel-btn" id="cancelBtn" onclick="cancelEdit()"><?php echo h($t['cancel']); ?></button>
                <button type="submit" class="save-btn" id="saveBtn"><?php echo h($t['save']); ?></button>
            </div>
        </form>

        <?php if ($message !== ""): ?>
            <p class="message <?php echo h($message_type); ?>"><?php echo h($message); ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
const fieldIds = ["fullName", "age", "gender", "mobile", "email", "bloodGroup", "address"];
let originalValues = {};
let originalPhoto = document.getElementById("profilePhoto").src;

function enableEdit() {
    originalValues = {};
    fieldIds.forEach(function(id) {
        const field = document.getElementById(id);
        originalValues[id] = field.value;
        field.disabled = false;
    });
    document.getElementById("editBtn").style.display = "none";
    document.getElementById("saveBtn").style.display = "inline-block";
    document.getElementById("cancelBtn").style.display = "inline-block";
    document.getElementById("photoLabel").style.display = "inline-block";
}

function cancelEdit() {
    fieldIds.forEach(function(id) {
        const field = document.getElementById(id);
        if (Object.prototype.hasOwnProperty.call(originalValues, id)) {
            field.value = originalValues[id];
        }
        field.disabled = true;
    });
    document.getElementById("profilePhotoInput").value = "";
    document.getElementById("profilePhoto").src = originalPhoto;
    document.getElementById("editBtn").style.display = "inline-block";
    document.getElementById("saveBtn").style.display = "none";
    document.getElementById("cancelBtn").style.display = "none";
    document.getElementById("photoLabel").style.display = "none";
}

document.getElementById("profilePhotoInput").addEventListener("change", function(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert(<?php echo json_encode($t['photo_size']); ?>);
        event.target.value = "";
        return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    if (!allowedTypes.includes(file.type)) {
        alert(<?php echo json_encode($t['photo_type']); ?>);
        event.target.value = "";
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById("profilePhoto").src = e.target.result;
    };
    reader.readAsDataURL(file);
});

document.getElementById("profileForm").addEventListener("submit", function() {
    // Disabled form fields are not submitted by the browser.
    fieldIds.forEach(function(id) {
        document.getElementById(id).disabled = false;
    });
});
</script>
</body>
</html>
