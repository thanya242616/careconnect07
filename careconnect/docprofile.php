<?php
session_start();
include "db.php";

if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctorlogin.html");
    exit();
}

$doctor_id = $_SESSION['doctor_id'];
$message = "";
$message_type = "";


/* =========================
   UPDATE PROFILE
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $doctor_name = trim($_POST['doctor_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $hospital = trim($_POST['hospital'] ?? '');
    $registration_no = trim($_POST['registration_no'] ?? '');

    if (
        empty($doctor_name) ||
        empty($email) ||
        empty($mobile) ||
        empty($specialization) ||
        empty($hospital) ||
        empty($registration_no)
    ) {

        $message = "Please fill all fields.";
        $message_type = "error";

    } else {

        /* Get old photo */

        $old_photo = "";

        $photo_sql = "SELECT profile_photo
                      FROM doctors
                      WHERE doctor_id = ?";

        $photo_stmt = $conn->prepare($photo_sql);

        if ($photo_stmt) {

            $photo_stmt->bind_param("s", $doctor_id);
            $photo_stmt->execute();

            $photo_result = $photo_stmt->get_result();

            if ($photo_result->num_rows === 1) {

                $photo_data = $photo_result->fetch_assoc();
                $old_photo = $photo_data['profile_photo'] ?? '';
            }

            $photo_stmt->close();
        }


        /* Keep old photo */

        $new_photo = $old_photo;


        /* =========================
           PHOTO UPLOAD
           ========================= */

        if (
            isset($_FILES['profile_photo']) &&
            $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {

                $file_tmp = $_FILES['profile_photo']['tmp_name'];
                $file_name = $_FILES['profile_photo']['name'];
                $file_size = $_FILES['profile_photo']['size'];

                if ($file_size > 5 * 1024 * 1024) {

                    $message = "Photo size must be less than 5 MB.";
                    $message_type = "error";

                } else {

                    $extension = strtolower(
                        pathinfo($file_name, PATHINFO_EXTENSION)
                    );

                    $allowed = [
                        "jpg",
                        "jpeg",
                        "png",
                        "gif",
                        "webp"
                    ];

                    if (!in_array($extension, $allowed)) {

                        $message =
                            "Only JPG, JPEG, PNG, GIF and WEBP images are allowed.";

                        $message_type = "error";

                    } else {

                        $new_file_name =
                            "doctor_" .
                            $doctor_id .
                            "_" .
                            time() .
                            "." .
                            $extension;

                        $upload_folder = "doctor_uploads/";

                        if (!is_dir($upload_folder)) {
                            mkdir($upload_folder, 0777, true);
                        }

                        $upload_path =
                            $upload_folder . $new_file_name;

                        if (
                            move_uploaded_file(
                                $file_tmp,
                                $upload_path
                            )
                        ) {

                            $new_photo = $new_file_name;

                            /* Delete old photo */

                            if (
                                !empty($old_photo) &&
                                file_exists(
                                    $upload_folder . $old_photo
                                )
                            ) {

                                unlink(
                                    $upload_folder . $old_photo
                                );
                            }

                        } else {

                            $message = "Photo upload failed.";
                            $message_type = "error";
                        }
                    }
                }

            } else {

                $message = "Error while uploading photo.";
                $message_type = "error";
            }
        }


        /* =========================
           UPDATE DATABASE
           ========================= */

        if (empty($message)) {

            $update_sql = "UPDATE doctors
                           SET doctor_name = ?,
                               email = ?,
                               mobile = ?,
                               specialization = ?,
                               hospital = ?,
                               registration_no = ?,
                               profile_photo = ?
                           WHERE doctor_id = ?";

            $update_stmt = $conn->prepare($update_sql);

            if (!$update_stmt) {
                die("Update error: " . $conn->error);
            }

            $update_stmt->bind_param(
                "ssssssss",
                $doctor_name,
                $email,
                $mobile,
                $specialization,
                $hospital,
                $registration_no,
                $new_photo,
                $doctor_id
            );

            if ($update_stmt->execute()) {

                $_SESSION['doctor_name'] = $doctor_name;

                $message =
                    "Profile changes saved successfully!";

                $message_type = "success";

            } else {

                $message =
                    "Error updating profile: " .
                    $update_stmt->error;

                $message_type = "error";
            }

            $update_stmt->close();
        }
    }
}


/* =========================
   GET DOCTOR DETAILS
   ========================= */

$sql = "SELECT
            doctor_id,
            doctor_name,
            email,
            mobile,
            specialization,
            hospital,
            registration_no,
            profile_photo
        FROM doctors
        WHERE doctor_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("s", $doctor_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    die(
        "Doctor profile not found for Doctor ID: " .
        htmlspecialchars($doctor_id)
    );
}

$doctor = $result->fetch_assoc();

$stmt->close();


/* Profile photo */

if (!empty($doctor['profile_photo'])) {

    $profile_photo =
        "doctor_uploads/" .
        $doctor['profile_photo'];

} else {

    $profile_photo =
        "https://via.placeholder.com/125";
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>CareConnect - Doctor Profile</title>


<style>

/* =========================
   RESET
   ========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}


/* =========================
   BODY
   ========================= */

body {
    background: #f2f7fb;
    color: #263746;
}


/* =========================
   SIDEBAR
   ========================= */

.sidebar {
    width: 240px;
    height: 100vh;
    background: #123c55;
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    padding: 25px 15px;
}

.logo {
    font-size: 25px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 35px;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 13px;
    margin: 7px 0;
    border-radius: 7px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #1e617d;
}


/* =========================
   MAIN AREA
   ========================= */

.main {
    margin-left: 240px;
    min-height: 100vh;
    padding: 28px 40px 50px;
}


/* =========================
   HEADER
   ========================= */

.header {
    width: 100%;
    max-width: 1050px;
    margin: 0 auto 22px;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    color: #123c55;
    font-size: 28px;
}

.back {
    color: #087bb5;
    text-decoration: none;
    font-weight: bold;
}

.back:hover {
    text-decoration: underline;
}


/* =========================
   PROFILE CARD
   ========================= */

.profile-card {
    width: 100%;
    max-width: 920px;
    margin: 0 auto;

    background: white;

    padding: 28px 34px 30px;

    border-radius: 14px;

    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
}


/* =========================
   PROFILE TOP
   ========================= */

.profile-top {
    text-align: center;

    padding-bottom: 22px;

    border-bottom: 1px solid #e7edf1;

    margin-bottom: 24px;
}

.photo {
    width: 115px;
    height: 115px;

    border-radius: 50%;

    object-fit: cover;

    border: 4px solid #dcecf4;

    background: #eaf3f8;
}

.profile-top h2 {
    margin-top: 12px;
    color: #123c55;
    font-size: 24px;
}

.profile-top p {
    color: #71818d;
    margin-top: 5px;
}


/* =========================
   PHOTO BUTTON
   ========================= */

.photo-upload {
    margin-top: 14px;
}

.photo-upload input {
    display: none;
}

.photo-label {
    display: inline-block;

    padding: 9px 15px;

    background: #087eae;

    color: white;

    border-radius: 6px;

    cursor: pointer;

    font-size: 14px;

    font-weight: bold;
}

.photo-label:hover {
    background: #056b94;
}


/* =========================
   SECTION TITLE
   ========================= */

.section-title {
    color: #123c55;

    margin-bottom: 18px;

    font-size: 19px;
}


/* =========================
   FORM GRID
   ========================= */

.form-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    column-gap: 22px;

    row-gap: 18px;
}


/* =========================
   FIELD
   ========================= */

.field {
    display: flex;

    flex-direction: column;

    gap: 7px;
}

.field label {
    font-size: 14px;

    font-weight: bold;

    color: #526777;
}

.field input {
    width: 100%;

    height: 45px;

    padding: 11px 12px;

    border: 1px solid #ccd9e2;

    border-radius: 7px;

    background: #f8fafc;

    color: #263746;

    outline: none;

    font-size: 14px;
}

.field input:disabled {
    background: #f1f4f6;

    color: #647583;

    cursor: default;
}

.field input:focus {
    border-color: #1684b5;

    background: white;
}


/* =========================
   BUTTONS
   ========================= */

.buttons {
    display: flex;

    justify-content: flex-end;

    gap: 12px;

    margin-top: 26px;

    flex-wrap: wrap;
}

button {
    padding: 11px 19px;

    border: none;

    border-radius: 7px;

    cursor: pointer;

    font-weight: bold;

    font-size: 14px;
}

.edit-btn,
.save-btn {
    background: #087eae;

    color: white;
}

.edit-btn:hover,
.save-btn:hover {
    background: #056b94;
}

.cancel-btn {
    background: #e8edf1;

    color: #344b5a;
}

.cancel-btn:hover {
    background: #dbe2e7;
}

#saveBtn,
#cancelBtn {
    display: none;
}


/* =========================
   MESSAGE
   ========================= */

.message {
    text-align: right;

    margin-top: 14px;

    font-size: 14px;

    font-weight: bold;
}

.success {
    color: #237744;
}

.error {
    color: #c0392b;
}


/* =========================
   MOBILE
   ========================= */

@media (max-width: 800px) {

    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .profile-card {
        padding: 25px 20px;
    }
}

</style>

</head>


<body>


<!-- =========================
     SIDEBAR
     ========================= -->

<div class="sidebar">

    <div class="logo">
        CareConnect
    </div>

    <a href="doctor_profile.php">
        Dashboard
    </a>

    <a href="docprofile.php" class="active">
        My Profile
    </a>

    <a href="doctor_appointments.php">
        Appointments
    </a>

    <a href="doctor_patients.php">
        Patients
    </a>

    <a href="doctor_reports.php">
        Medical Reports
    </a>

    <a href="doctor_online_consultation.php">
        Online Consultation
    </a>

    <a href="doctor_prescription.php">
        Prescriptions
    </a>

    <a href="index.html">
        Logout
    </a>

</div>


<!-- =========================
     MAIN
     ========================= -->

<div class="main">


    <div class="header">

        <h1>
            Doctor Profile
        </h1>

        <a
            href="doctor_profile.php"
            class="back"
        >
            ← Back to Dashboard
        </a>

    </div>


    <div class="profile-card">


        <!-- =========================
             PROFILE TOP
             ========================= -->

        <div class="profile-top">

            <img
                src="<?php echo htmlspecialchars($profile_photo); ?>"
                id="profilePhoto"
                class="photo"
                alt="Doctor Profile Photo"
            >

            <h2>
                <?php
                echo htmlspecialchars(
                    $doctor['doctor_name']
                );
                ?>
            </h2>

            <p>
                Medical Professional
            </p>


            <div class="photo-upload">

                <label
                    for="profilePhotoInput"
                    class="photo-label"
                    id="photoLabel"
                    style="display:none;"
                >
                    Choose Profile Photo
                </label>

            </div>

        </div>


        <!-- =========================
             PERSONAL INFORMATION
             ========================= -->

        <h3 class="section-title">
            Personal Information
        </h3>


        <form
            method="POST"
            action="docprofile.php"
            enctype="multipart/form-data"
            id="profileForm"
        >


            <div class="form-grid">


                <!-- DOCTOR NAME -->

                <div class="field">

                    <label>
                        Doctor Name
                    </label>

                    <input
                        type="text"
                        name="doctor_name"
                        id="doctorName"
                        value="<?php echo htmlspecialchars($doctor['doctor_name']); ?>"
                        disabled
                    >

                </div>


                <!-- EMAIL -->

                <div class="field">

                    <label>
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="<?php echo htmlspecialchars($doctor['email']); ?>"
                        disabled
                    >

                </div>


                <!-- MOBILE -->

                <div class="field">

                    <label>
                        Mobile Number
                    </label>

                    <input
                        type="tel"
                        name="mobile"
                        id="mobile"
                        value="<?php echo htmlspecialchars($doctor['mobile']); ?>"
                        disabled
                    >

                </div>


                <!-- SPECIALIZATION -->

                <div class="field">

                    <label>
                        Specialization
                    </label>

                    <input
                        type="text"
                        name="specialization"
                        id="specialization"
                        value="<?php echo htmlspecialchars($doctor['specialization']); ?>"
                        disabled
                    >

                </div>


                <!-- HOSPITAL -->

                <div class="field">

                    <label>
                        Hospital Name
                    </label>

                    <input
                        type="text"
                        name="hospital"
                        id="hospital"
                        value="<?php echo htmlspecialchars($doctor['hospital']); ?>"
                        disabled
                    >

                </div>


                <!-- REGISTRATION -->

                <div class="field">

                    <label>
                        Medical Registration Number
                    </label>

                    <input
                        type="text"
                        name="registration_no"
                        id="registration"
                        value="<?php echo htmlspecialchars($doctor['registration_no']); ?>"
                        disabled
                    >

                </div>

            </div>


            <!-- PHOTO INPUT -->

            <input
                type="file"
                name="profile_photo"
                id="profilePhotoInput"
                accept="image/*"
                style="display:none;"
            >


            <!-- BUTTONS -->

            <div class="buttons">

                <button
                    type="button"
                    class="edit-btn"
                    id="editBtn"
                    onclick="enableEdit()"
                >
                    Edit Profile
                </button>


                <button
                    type="button"
                    class="cancel-btn"
                    id="cancelBtn"
                    onclick="cancelEdit()"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="save-btn"
                    id="saveBtn"
                >
                    Save Changes
                </button>

            </div>

        </form>


        <!-- MESSAGE -->

        <?php if (!empty($message)): ?>

            <p class="message <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </p>

        <?php endif; ?>


    </div>

</div>


<script>

/* =========================
   EDITABLE FIELDS
   ========================= */

const fields = [
    "doctorName",
    "email",
    "mobile",
    "specialization",
    "hospital",
    "registration"
];

let originalValues = {};


/* =========================
   ENABLE EDIT
   ========================= */

function enableEdit() {

    fields.forEach(function(id) {

        const field =
            document.getElementById(id);

        originalValues[id] =
            field.value;

        field.disabled = false;

    });

    document.getElementById(
        "editBtn"
    ).style.display = "none";

    document.getElementById(
        "saveBtn"
    ).style.display = "inline-block";

    document.getElementById(
        "cancelBtn"
    ).style.display = "inline-block";

    document.getElementById(
        "photoLabel"
    ).style.display = "inline-block";
}


/* =========================
   CANCEL EDIT
   ========================= */

function cancelEdit() {

    fields.forEach(function(id) {

        const field =
            document.getElementById(id);

        if (
            originalValues[id] !== undefined
        ) {
            field.value =
                originalValues[id];
        }

        field.disabled = true;

    });

    document.getElementById(
        "profilePhotoInput"
    ).value = "";

    document.getElementById(
        "editBtn"
    ).style.display = "inline-block";

    document.getElementById(
        "saveBtn"
    ).style.display = "none";

    document.getElementById(
        "cancelBtn"
    ).style.display = "none";

    document.getElementById(
        "photoLabel"
    ).style.display = "none";
}


/* =========================
   PHOTO PREVIEW
   ========================= */

document
.getElementById("profilePhotoInput")
.addEventListener("change", function(event) {

    const file =
        event.target.files[0];

    if (!file) {
        return;
    }

    const reader =
        new FileReader();

    reader.onload = function(e) {

        document.getElementById(
            "profilePhoto"
        ).src = e.target.result;

    };

    reader.readAsDataURL(file);

});


/* =========================
   SUBMIT
   ========================= */

document
.getElementById("profileForm")
.addEventListener("submit", function() {

    fields.forEach(function(id) {

        document.getElementById(
            id
        ).disabled = false;

    });

});

</script>


</body>

</html>