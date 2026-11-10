
<?php
session_start();

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.html");
    exit();
}

include "db.php";

$patient_id = (int) $_SESSION['patient_id'];

$language = $_SESSION['language'] ?? 'en';

$translations = [
    'en' => [
        'title' => 'Appointment Status',
        'tagline' => 'CareConnect - Your Health. Your Records. Your Control.',
        'my_appointments' => 'My Appointments',
        'hospital_appointment' => 'Hospital Appointment',
        'appointment_id' => 'Appointment ID',
        'doctor' => 'Doctor Name',
        'hospital' => 'Hospital',
        'specialization' => 'Specialization',
        'date' => 'Date',
        'time' => 'Time',
        'reason' => 'Reason',
        'no_appointments' => 'No Appointments',
        'not_booked' => 'You have not booked any appointment yet.',
        'book' => 'Book Appointment',
        'dashboard' => 'Back to Dashboard'
    ],
    'ta' => [
        'title' => 'சந்திப்பு நிலை',
        'tagline' => 'CareConnect - உங்கள் உடல்நலம். உங்கள் பதிவுகள். உங்கள் கட்டுப்பாடு.',
        'my_appointments' => 'எனது சந்திப்புகள்',
        'hospital_appointment' => 'மருத்துவமனை சந்திப்பு',
        'appointment_id' => 'சந்திப்பு எண்',
        'doctor' => 'மருத்துவர் பெயர்',
        'hospital' => 'மருத்துவமனை',
        'specialization' => 'மருத்துவப் பிரிவு',
        'date' => 'தேதி',
        'time' => 'நேரம்',
        'reason' => 'காரணம்',
        'no_appointments' => 'சந்திப்புகள் எதுவும் இல்லை',
        'not_booked' => 'நீங்கள் இன்னும் எந்தச் சந்திப்பையும் பதிவு செய்யவில்லை.',
        'book' => 'சந்திப்பைப் பதிவு செய்',
        'dashboard' => 'முகப்புப் பக்கத்திற்குத் திரும்பு'
    ],
    'hi' => [
        'title' => 'अपॉइंटमेंट की स्थिति',
        'tagline' => 'CareConnect - आपका स्वास्थ्य। आपके रिकॉर्ड। आपका नियंत्रण।',
        'my_appointments' => 'मेरी अपॉइंटमेंट',
        'hospital_appointment' => 'अस्पताल की अपॉइंटमेंट',
        'appointment_id' => 'अपॉइंटमेंट आईडी',
        'doctor' => 'डॉक्टर का नाम',
        'hospital' => 'अस्पताल',
        'specialization' => 'विशेषज्ञता',
        'date' => 'तारीख',
        'time' => 'समय',
        'reason' => 'कारण',
        'no_appointments' => 'कोई अपॉइंटमेंट नहीं',
        'not_booked' => 'आपने अभी तक कोई अपॉइंटमेंट बुक नहीं की है।',
        'book' => 'अपॉइंटमेंट बुक करें',
        'dashboard' => 'डैशबोर्ड पर वापस जाएँ'
    ]
];

if (!isset($translations[$language])) {
    $language = 'en';
}

$t = $translations[$language];

$sql = "SELECT
            appointment_id,
            hospital_name,
            specialization,
            doctor_name,
            appointment_date,
            appointment_time,
            reason,
            status
        FROM appointments
        WHERE patient_id = ?
        ORDER BY appointment_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareConnect - <?php echo $t['title']; ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f7fb;
            margin: 0;
            padding: 20px;
            color: #263746;
        }

        .container {
            max-width: 850px;
            margin: auto;
        }

        .header {
            background: #087f8c;
            color: white;
            text-align: center;
            padding: 25px;
            border-radius: 12px;
        }

        .header h2 {
            margin: 0;
        }

        .section-title {
            margin-top: 30px;
            color: #087f8c;
        }

        .appointment-card {
            background: white;
            padding: 25px;
            margin-top: 15px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #00000010;
            border-left: 5px solid #087f8c;
        }

        .status {
            display: inline-block;
            background: #e1f7e8;
            color: #16803c;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .detail {
            background: #f5f9fc;
            padding: 13px;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }

        .label {
            color: #718096;
            font-size: 13px;
        }

        .value {
            font-weight: bold;
            margin-top: 6px;
        }

        .empty {
            background: white;
            padding: 40px 20px;
            margin-top: 25px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 3px 12px #00000010;
        }

        .empty-icon {
            font-size: 45px;
        }

        .empty h3 {
            color: #087f8c;
        }

        .empty p {
            color: #718096;
        }

        button {
            padding: 12px 20px;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 20px;
        }

        .book-btn {
            background: #087f8c;
            color: white;
        }

        .dashboard-btn {
            background: #1769aa;
            color: white;
        }

        @media(max-width: 600px) {
            .details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>🩺 <?php echo $t['title']; ?></h2>
        <p><?php echo $t['tagline']; ?></p>
    </div>

    <h3 class="section-title">
        🏥 <?php echo $t['my_appointments']; ?>
    </h3>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($appointment = $result->fetch_assoc()): ?>

            <div class="appointment-card">

                <h3>🏥 <?php echo $t['hospital_appointment']; ?></h3>

                <span class="status">
                    <?php echo htmlspecialchars($appointment['status'] ?? ''); ?>
                </span>

                <div class="details">

                    <div class="detail">
                        <div class="label"><?php echo $t['appointment_id']; ?></div>
                        <div class="value">
                            <?php echo htmlspecialchars((string)$appointment['appointment_id']); ?>
                        </div>
                    </div>

                    <div class="detail">
                        <div class="label"><?php echo $t['doctor']; ?></div>
                        <div class="value">
                            <?php echo htmlspecialchars($appointment['doctor_name'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="detail">
                        <div class="label"><?php echo $t['hospital']; ?></div>
                        <div class="value">
                            <?php echo htmlspecialchars($appointment['hospital_name'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="detail">
                        <div class="label"><?php echo $t['specialization']; ?></div>
                        <div class="value">
                            <?php echo htmlspecialchars($appointment['specialization'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="detail">
                        <div class="label"><?php echo $t['date']; ?></div>
                        <div class="value">
                            <?php echo htmlspecialchars($appointment['appointment_date'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="detail">
                        <div class="label"><?php echo $t['time']; ?></div>
                        <div class="value">
                            <?php echo htmlspecialchars($appointment['appointment_time'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="detail">
                        <div class="label"><?php echo $t['reason']; ?></div>
                        <div class="value">
                            <?php echo htmlspecialchars($appointment['reason'] ?? ''); ?>
                        </div>
                    </div>

                </div>
            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">
            <div class="empty-icon">📅</div>

            <h3><?php echo $t['no_appointments']; ?></h3>

            <p><?php echo $t['not_booked']; ?></p>

            <button class="book-btn"
                onclick="location.href='appointment.php'">
                ➕ <?php echo $t['book']; ?>
            </button>
        </div>

    <?php endif; ?>

    <button class="dashboard-btn"
        onclick="location.href='patient.php'">
        🏠 <?php echo $t['dashboard']; ?>
    </button>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
