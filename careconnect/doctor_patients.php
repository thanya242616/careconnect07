<?php

session_start();

if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctorlogin.php");
    exit();
}

include "db.php";

$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

$patient = null;

/* Search patient only when Patient ID is entered */

if ($patient_id > 0) {

    $sql = "SELECT
                p.patient_id,
                p.full_name,
                p.date_of_birth,
                p.gender,
                p.mobile,
                p.email,
                p.address,

                i.issue_id,
                i.symptoms,
                i.duration,
                i.severity,
                i.issue_description,
                i.hospital_name,
                i.created_at

            FROM patients p

            LEFT JOIN patient_issues i
            ON i.issue_id = (
                SELECT MAX(i2.issue_id)
                FROM patient_issues i2
                WHERE i2.patient_id = p.patient_id
            )

            WHERE p.patient_id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database Error: " . $conn->error);
    }

    $stmt->bind_param("i", $patient_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $patient = $result->fetch_assoc();

        /* Calculate age */

        $patient['age'] = '';

        if (!empty($patient['date_of_birth'])) {

            $dob = new DateTime($patient['date_of_birth']);
            $today = new DateTime();

            $patient['age'] = $today->diff($dob)->y;
        }
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CareConnect - Patient Details</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f2f7fb;
    color: #263746;
}

.sidebar {
    width: 240px;
    height: 100vh;
    background: #123c55;
    color: white;
    position: fixed;
    padding: 25px 15px;
    overflow-y: auto;
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

.main {
    margin-left: 240px;
    padding: 30px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.header h1 {
    color: #123c55;
    font-size: 27px;
}

.back {
    color: #087bb5;
    text-decoration: none;
    font-weight: bold;
}

.description {
    color: #71818d;
    margin-bottom: 25px;
}

.search-box {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 3px 12px #0000000a;
}

.search-box label {
    display: block;
    font-weight: bold;
    margin-bottom: 10px;
    color: #34566b;
}

.search-form {
    display: flex;
    gap: 10px;
}

.search-form input {
    flex: 1;
    padding: 13px;
    border: 1px solid #ccd9e2;
    border-radius: 7px;
    outline: none;
}

.search-form input:focus {
    border-color: #1684b5;
}

.search-btn {
    background: #087eae;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: bold;
}

.search-btn:hover {
    background: #05658e;
}

.patient-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 12px #0000000a;
}

.patient-card h2 {
    color: #123c55;
    margin-bottom: 20px;
    font-size: 20px;
}

.details {
    margin-top: 10px;
    padding: 28px;
    background: #f7fafc;
    border: 1px solid #dce8ef;
    border-radius: 10px;
}

.details h2 {
    color: #123c55;
    margin-bottom: 20px;
}

.section-title {
    grid-column: 1 / -1;
    color: #087eae;
    font-size: 17px;
    margin: 20px 0 5px;
    padding-bottom: 10px;
    border-bottom: 1px solid #dce8ef;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-item label {
    color: #526c7e;
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 7px;
}

.detail-item input,
.detail-item textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd9e2;
    border-radius: 6px;
    font-size: 14px;
    color: #263746;
    background: white;
}

.detail-item input[readonly],
.detail-item textarea[readonly] {
    background: #edf4f8;
    color: #40596b;
}

.detail-item textarea {
    min-height: 90px;
    resize: vertical;
}

.full {
    grid-column: 1 / -1;
}

.empty {
    text-align: center;
    padding: 45px 10px;
    color: #71818d;
}

.empty-icon {
    font-size: 38px;
    margin-bottom: 12px;
}

.note {
    margin-top: 20px;
    color: #71818d;
    font-size: 13px;
}

@media(max-width: 700px) {

    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main {
        margin-left: 0;
        padding: 18px;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .section-title,
    .full {
        grid-column: 1;
    }

    .search-form {
        flex-direction: column;
    }
}

</style>

</head>

<body>

<div class="sidebar">

    <div class="logo">CareConnect</div>

    <a href="doctor_profile.php">Dashboard</a>

    <a href="docprofile.html">
        My Profile
    </a>

    <a href="doctor_appointments.php">
        Appointments
    </a>

    <a href="doctor_patients.php" class="active">
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


<div class="main">

    <div class="header">

        <h1>Patient Details</h1>

        <a href="doctor_profile.php" class="back">
            ← Back to Dashboard
        </a>

    </div>


    <p class="description">
        Enter the Patient ID to view the patient's current health issue.
    </p>


    <!-- SEARCH PATIENT -->

    <div class="search-box">

        <label for="patientId">
            Enter Patient ID
        </label>

        <form method="GET" class="search-form">

            <input
                type="number"
                name="patient_id"
                id="patientId"
                placeholder="Enter Patient ID"
                value="<?php echo $patient_id > 0 ? $patient_id : ''; ?>"
                required
            >

            <button type="submit" class="search-btn">
                Search Patient
            </button>

        </form>

    </div>


    <div class="patient-card">

        <?php if ($patient): ?>


            <!-- PATIENT INFORMATION -->

            <div class="details">

                <h2>Patient Personal Details</h2>

                <div class="details-grid">

                    <h3 class="section-title">
                        Patient Information
                    </h3>


                    <div class="detail-item">

                        <label>Patient ID</label>

                        <input
                            value="<?php echo htmlspecialchars($patient['patient_id']); ?>"
                            readonly
                        >

                    </div>


                    <div class="detail-item">

                        <label>Patient Name</label>

                        <input
                            value="<?php echo htmlspecialchars($patient['full_name']); ?>"
                            readonly
                        >

                    </div>


                    <div class="detail-item">

                        <label>Age</label>

                        <input
                            value="<?php echo htmlspecialchars($patient['age']); ?>"
                            readonly
                        >

                    </div>


                    <div class="detail-item">

                        <label>Gender</label>

                        <input
                            value="<?php echo htmlspecialchars($patient['gender']); ?>"
                            readonly
                        >

                    </div>


                    <div class="detail-item">

                        <label>Mobile Number</label>

                        <input
                            value="<?php echo htmlspecialchars($patient['mobile']); ?>"
                            readonly
                        >

                    </div>


                    <div class="detail-item">

                        <label>Email</label>

                        <input
                            value="<?php echo htmlspecialchars($patient['email']); ?>"
                            readonly
                        >

                    </div>


                    <div class="detail-item full">

                        <label>Address</label>

                        <textarea readonly><?php
                            echo htmlspecialchars($patient['address']);
                        ?></textarea>

                    </div>

                </div>

            </div>


            <!-- CURRENT HEALTH ISSUE -->

            <div class="details">

                <h2>Patient Current Health Issue</h2>

                <?php if (!empty($patient['issue_id'])): ?>

                    <div class="details-grid">


                        <h3 class="section-title">
                            Submitted Health Issue
                        </h3>


                        <div class="detail-item full">

                            <label>Hospital Name</label>

                            <input
                                value="<?php
                                    echo htmlspecialchars(
                                        $patient['hospital_name']
                                    );
                                ?>"
                                readonly
                            >

                        </div>


                        <div class="detail-item full">

                            <label>Current Symptoms</label>

                            <textarea readonly><?php
                                echo htmlspecialchars(
                                    $patient['symptoms']
                                );
                            ?></textarea>

                        </div>


                        <div class="detail-item">

                            <label>Duration of Symptoms</label>

                            <input
                                value="<?php
                                    echo htmlspecialchars(
                                        $patient['duration']
                                    );
                                ?>"
                                readonly
                            >

                        </div>


                        <div class="detail-item">

                            <label>Severity of Problem</label>

                            <input
                                value="<?php
                                    echo htmlspecialchars(
                                        $patient['severity']
                                    );
                                ?>"
                                readonly
                            >

                        </div>


                        <div class="detail-item full">

                            <label>Additional Notes</label>

                            <textarea readonly><?php
                                echo htmlspecialchars(
                                    $patient['issue_description']
                                );
                            ?></textarea>

                        </div>


                        <div class="detail-item full">

                            <label>Submitted Date</label>

                            <input
                                value="<?php
                                    echo htmlspecialchars(
                                        $patient['created_at']
                                    );
                                ?>"
                                readonly
                            >

                        </div>

                    </div>

                <?php else: ?>

                    <div class="empty">

                        <div class="empty-icon">
                            📋
                        </div>

                        <h3>
                            No Health Issue Submitted
                        </h3>

                        <p>
                            This patient has not submitted a current
                            health issue yet.
                        </p>

                    </div>

                <?php endif; ?>

            </div>


        <?php elseif ($patient_id > 0): ?>


            <div class="empty">

                <div class="empty-icon">
                    🔍
                </div>

                <h3>
                    Patient Not Found
                </h3>

                <p>
                    No patient exists with Patient ID
                    <?php echo htmlspecialchars($patient_id); ?>.
                </p>

            </div>


        <?php else: ?>


            <div class="empty">

                <div class="empty-icon">
                    👤
                </div>

                <h3>
                    Search for a Patient
                </h3>

                <p>
                    Enter a Patient ID above to view the patient's
                    current health issue.
                </p>

            </div>


        <?php endif; ?>

    </div>


    <p class="note">

        Patient information must be accessed only by authorized doctors
        with appropriate patient permission.

    </p>

</div>

</body>

</html>