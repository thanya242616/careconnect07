<?php
session_start();

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$patient_id = $_SESSION['patient_id'];

// Use the selected website language; English is the default.
$lang = $_SESSION['language'] ?? 'en';
$translations = [
    'en' => [
        'page_title' => 'My Digital Prescription - CareConnect',
        'back' => '← Back to Dashboard',
        'heading' => 'My Digital Prescriptions',
        'prescription_title' => 'CareConnect - Digital Prescription',
        'prescription_id' => 'Prescription ID:',
        'patient_name' => 'Patient Name:',
        'patient_id' => 'Patient ID:',
        'doctor' => 'Doctor:',
        'hospital' => 'Hospital:',
        'date' => 'Date:',
        'diagnosis' => 'Diagnosis',
        'medicines' => 'Medicines',
        'medicine' => 'Medicine:',
        'dosage' => 'Dosage:',
        'frequency' => 'Frequency:',
        'duration' => 'Duration:',
        'instructions' => 'Instructions:',
        'medicine_unavailable' => 'Medicine details unavailable.',
        'doctor_instructions' => 'Doctor Instructions',
        'print' => 'Print Prescription',
        'none_title' => 'No Prescription Available',
        'none_message' => 'Your doctor has not issued a prescription yet.'
    ],
    'ta' => [
        'page_title' => 'எனது டிஜிட்டல் மருந்துச் சீட்டு - CareConnect',
        'back' => '← டாஷ்போர்டுக்குத் திரும்பு',
        'heading' => 'எனது டிஜிட்டல் மருந்துச் சீட்டுகள்',
        'prescription_title' => 'CareConnect - டிஜிட்டல் மருந்துச் சீட்டு',
        'prescription_id' => 'மருந்துச் சீட்டு எண்:',
        'patient_name' => 'நோயாளியின் பெயர்:',
        'patient_id' => 'நோயாளர் எண்:',
        'doctor' => 'மருத்துவர்:',
        'hospital' => 'மருத்துவமனை:',
        'date' => 'தேதி:',
        'diagnosis' => 'நோய் கண்டறிதல்',
        'medicines' => 'மருந்துகள்',
        'medicine' => 'மருந்து:',
        'dosage' => 'மருந்தின் அளவு:',
        'frequency' => 'எத்தனை முறை:',
        'duration' => 'கால அளவு:',
        'instructions' => 'வழிமுறைகள்:',
        'medicine_unavailable' => 'மருந்து விவரங்கள் கிடைக்கவில்லை.',
        'doctor_instructions' => 'மருத்துவரின் வழிமுறைகள்',
        'print' => 'மருந்துச் சீட்டை அச்சிடு',
        'none_title' => 'மருந்துச் சீட்டு எதுவும் இல்லை',
        'none_message' => 'உங்கள் மருத்துவர் இன்னும் மருந்துச் சீட்டை வழங்கவில்லை.'
    ],
    'hi' => [
        'page_title' => 'मेरी डिजिटल प्रिस्क्रिप्शन - CareConnect',
        'back' => '← डैशबोर्ड पर वापस जाएँ',
        'heading' => 'मेरी डिजिटल प्रिस्क्रिप्शन',
        'prescription_title' => 'CareConnect - डिजिटल प्रिस्क्रिप्शन',
        'prescription_id' => 'प्रिस्क्रिप्शन आईडी:',
        'patient_name' => 'मरीज़ का नाम:',
        'patient_id' => 'मरीज़ आईडी:',
        'doctor' => 'डॉक्टर:',
        'hospital' => 'अस्पताल:',
        'date' => 'तारीख:',
        'diagnosis' => 'निदान',
        'medicines' => 'दवाइयाँ',
        'medicine' => 'दवा:',
        'dosage' => 'खुराक:',
        'frequency' => 'कितनी बार:',
        'duration' => 'अवधि:',
        'instructions' => 'निर्देश:',
        'medicine_unavailable' => 'दवा का विवरण उपलब्ध नहीं है।',
        'doctor_instructions' => 'डॉक्टर के निर्देश',
        'print' => 'प्रिस्क्रिप्शन प्रिंट करें',
        'none_title' => 'कोई प्रिस्क्रिप्शन उपलब्ध नहीं है',
        'none_message' => 'आपके डॉक्टर ने अभी तक प्रिस्क्रिप्शन जारी नहीं किया है।'
    ]
];

// If the selected language is not translated here, show English.
$t = $translations[$lang] ?? $translations['en'];

$sql = "SELECT * FROM prescriptions
        WHERE patient_id = ?
        ORDER BY prescription_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(in_array($lang, ['en', 'ta', 'hi'], true) ? $lang : 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($t['page_title']); ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f7fb;
            margin: 0;
            padding: 30px;
        }
        .container { max-width: 850px; margin: auto; }
        h2 { color: #176b87; text-align: center; }
        .prescription {
            background: white;
            padding: 25px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 3px 12px #00000012;
        }
        h3 { color: #176b87; }
        .info { line-height: 1.8; }
        .medicine {
            background: #f2f7fb;
            padding: 12px;
            margin: 10px 0;
            border-radius: 7px;
        }
        button {
            background: #176b87;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
        }
        .back { display: inline-block; margin-bottom: 15px; color: #176b87; }
        @media print {
            button, .back { display: none; }
            body { background: white; }
            .prescription {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <a class="back" href="patient.php"><?php echo htmlspecialchars($t['back']); ?></a>
    <h2><?php echo htmlspecialchars($t['heading']); ?></h2>

    <?php if ($result && $result->num_rows > 0) { ?>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="prescription">
                <h3><?php echo htmlspecialchars($t['prescription_title']); ?></h3>
                <div class="info">
                    <b><?php echo htmlspecialchars($t['prescription_id']); ?></b>
                    <?php echo htmlspecialchars($row['prescription_id'] ?? ''); ?><br>

                    <b><?php echo htmlspecialchars($t['patient_name']); ?></b>
                    <?php echo htmlspecialchars($row['patient_name'] ?? ''); ?><br>

                    <b><?php echo htmlspecialchars($t['patient_id']); ?></b>
                    <?php echo htmlspecialchars($row['patient_id'] ?? ''); ?><br>

                    <b><?php echo htmlspecialchars($t['doctor']); ?></b>
                    <?php echo htmlspecialchars($row['doctor_name'] ?? ''); ?><br>

                    <b><?php echo htmlspecialchars($t['hospital']); ?></b>
                    <?php echo htmlspecialchars($row['hospital_name'] ?? ''); ?><br>

                    <b><?php echo htmlspecialchars($t['date']); ?></b>
                    <?php echo htmlspecialchars($row['prescription_date'] ?? ''); ?>
                </div>

                <hr>
                <h3><?php echo htmlspecialchars($t['diagnosis']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($row['diagnosis'] ?? '')); ?></p>

                <h3><?php echo htmlspecialchars($t['medicines']); ?></h3>
                <?php
                $medicines = json_decode($row['medicines'] ?? '', true);
                if (is_array($medicines)) {
                    foreach ($medicines as $medicine) {
                ?>
                    <div class="medicine">
                        <b><?php echo htmlspecialchars($t['medicine']); ?></b>
                        <?php echo htmlspecialchars($medicine['name'] ?? ''); ?><br>

                        <b><?php echo htmlspecialchars($t['dosage']); ?></b>
                        <?php echo htmlspecialchars($medicine['dosage'] ?? ''); ?><br>

                        <b><?php echo htmlspecialchars($t['frequency']); ?></b>
                        <?php echo htmlspecialchars($medicine['frequency'] ?? ''); ?><br>

                        <b><?php echo htmlspecialchars($t['duration']); ?></b>
                        <?php echo htmlspecialchars($medicine['duration'] ?? ''); ?><br>

                        <b><?php echo htmlspecialchars($t['instructions']); ?></b>
                        <?php echo htmlspecialchars($medicine['instructions'] ?? ''); ?>
                    </div>
                <?php
                    }
                } else {
                    echo '<p>' . htmlspecialchars($t['medicine_unavailable']) . '</p>';
                }
                ?>

                <h3><?php echo htmlspecialchars($t['doctor_instructions']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($row['instructions'] ?? '')); ?></p>
                <button type="button" onclick="window.print()"><?php echo htmlspecialchars($t['print']); ?></button>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="prescription">
            <h3><?php echo htmlspecialchars($t['none_title']); ?></h3>
            <p><?php echo htmlspecialchars($t['none_message']); ?></p>
        </div>
    <?php } ?>
</div>
</body>
</html>
