<?php
session_start();
require_once "db.php";

function healthIssuePostValue(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

function healthIssueCharacterCount(string $value): ?int
{
    $count = preg_match_all('/./us', $value, $matches);

    return $count === false ? null : $count;
}

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}

/*
  Language selected in language.php:
  en = English, ta = Tamil, hi = Hindi
*/
$lang = $_SESSION['language'] ?? 'en';
$supported_languages = ['en', 'ta', 'hi'];
if (!in_array($lang, $supported_languages, true)) {
    $lang = 'en';
}

$translations = [
    'en' => [
        'page_title' => 'CareConnect - Health Issue',
        'heading' => 'Patient Health Issue Form',
        'hospital' => 'Hospital Name',
        'doctor_id' => 'Doctor Name',
        'symptoms' => 'Symptoms',
        'duration' => 'Duration',
        'select_duration' => 'Select duration',
        'severity' => 'Severity',
        'select_severity' => 'Select severity',
        'mild' => 'Mild',
        'moderate' => 'Moderate',
        'severe' => 'Severe',
        'details' => 'Additional Medical Details',
        'hospital_placeholder' => 'Select a registered hospital',
        'doctor_placeholder' => 'Select a doctor',
        'symptoms_placeholder' => 'Enter your symptoms',
        'notes_placeholder' => 'Enter previous medical history or additional details',
        'submit' => 'Submit Health Issue',
        'back' => 'Back to Dashboard',
        'required_error' => 'Please fill all required fields.',
        'invalid_hospital' => 'Please select a registered hospital.',
        'invalid_doctor' => 'Please select a doctor registered at the selected hospital.',
        'invalid_symptoms' => 'Enter valid symptoms (3-1000 characters).',
        'invalid_duration' => 'Please select a valid symptom duration.',
        'invalid_severity' => 'Please select a valid severity.',
        'invalid_notes' => 'Additional details must be 5000 characters or fewer and include both letters and numbers.',
        'success' => 'Health issue submitted successfully! Assigned Doctor: ',
        'verify_error' => 'Issue was inserted, but doctor assignment verification failed.',
        'db_error' => 'Database Error: ',
        'days_1_3' => '1-3 days',
        'days_4_7' => '4-7 days',
        'weeks_1_2' => '1-2 weeks',
        'more_2_weeks' => 'More than 2 weeks',
        'more_1_month' => 'More than 1 month',
    ],
    'ta' => [
        'page_title' => 'CareConnect - உடல்நலப் பிரச்சினை',
        'heading' => 'நோயாளியின் உடல்நலப் பிரச்சினைப் படிவம்',
        'hospital' => 'மருத்துவமனை பெயர்',
        'doctor_id' => 'மருத்துவர் பெயர்',
        'symptoms' => 'அறிகுறிகள்',
        'duration' => 'எவ்வளவு காலமாக உள்ளது?',
        'select_duration' => 'கால அளவைத் தேர்ந்தெடுக்கவும்',
        'severity' => 'பிரச்சினையின் தீவிரம்',
        'select_severity' => 'தீவிரத்தைத் தேர்ந்தெடுக்கவும்',
        'mild' => 'லேசானது',
        'moderate' => 'மிதமானது',
        'severe' => 'தீவிரமானது',
        'details' => 'கூடுதல் மருத்துவ விவரங்கள்',
        'hospital_placeholder' => 'பதிவுசெய்யப்பட்ட மருத்துவமனையைத் தேர்ந்தெடுக்கவும்',
        'doctor_placeholder' => 'மருத்துவரைத் தேர்ந்தெடுக்கவும்',
        'symptoms_placeholder' => 'உங்கள் அறிகுறிகளை உள்ளிடவும்',
        'notes_placeholder' => 'முந்தைய மருத்துவ வரலாறு அல்லது கூடுதல் விவரங்களை உள்ளிடவும்',
        'submit' => 'உடல்நலப் பிரச்சினையைச் சமர்ப்பிக்கவும்',
        'back' => 'முகப்புப் பக்கத்திற்குத் திரும்பவும்',
        'required_error' => 'தேவையான அனைத்து விவரங்களையும் நிரப்பவும்.',
        'invalid_hospital' => 'பதிவுசெய்யப்பட்ட மருத்துவமனையைத் தேர்ந்தெடுக்கவும்.',
        'invalid_doctor' => 'தேர்ந்தெடுத்த மருத்துவமனையில் பதிவுசெய்யப்பட்ட மருத்துவரைத் தேர்ந்தெடுக்கவும்.',
        'invalid_symptoms' => 'சரியான அறிகுறிகளை உள்ளிடவும் (3-1000 எழுத்துகள்).',
        'invalid_duration' => 'சரியான அறிகுறி கால அளவைத் தேர்ந்தெடுக்கவும்.',
        'invalid_severity' => 'சரியான தீவிரத்தைத் தேர்ந்தெடுக்கவும்.',
        'invalid_notes' => 'கூடுதல் விவரங்கள் 5000 எழுத்துகளுக்குள் இருந்து எழுத்துகள் மற்றும் எண்கள் இரண்டையும் கொண்டிருக்க வேண்டும்.',
        'success' => 'உடல்நலப் பிரச்சினை வெற்றிகரமாகச் சமர்ப்பிக்கப்பட்டது! ஒதுக்கப்பட்ட மருத்துவர்: ',
        'verify_error' => 'பிரச்சினை சேமிக்கப்பட்டது; ஆனால் மருத்துவர் ஒதுக்கீட்டைச் சரிபார்க்க முடியவில்லை.',
        'db_error' => 'தரவுத்தளப் பிழை: ',
        'days_1_3' => '1-3 நாட்கள்',
        'days_4_7' => '4-7 நாட்கள்',
        'weeks_1_2' => '1-2 வாரங்கள்',
        'more_2_weeks' => '2 வாரங்களுக்கு மேல்',
        'more_1_month' => '1 மாதத்திற்கு மேல்',
    ],
    'hi' => [
        'page_title' => 'CareConnect - स्वास्थ्य समस्या',
        'heading' => 'मरीज की स्वास्थ्य समस्या का फॉर्म',
        'hospital' => 'अस्पताल का नाम',
        'doctor_id' => 'डॉक्टर का नाम',
        'symptoms' => 'लक्षण',
        'duration' => 'अवधि',
        'select_duration' => 'अवधि चुनें',
        'severity' => 'गंभीरता',
        'select_severity' => 'गंभीरता चुनें',
        'mild' => 'हल्की',
        'moderate' => 'मध्यम',
        'severe' => 'गंभीर',
        'details' => 'अतिरिक्त चिकित्सा जानकारी',
        'hospital_placeholder' => 'पंजीकृत अस्पताल चुनें',
        'doctor_placeholder' => 'डॉक्टर चुनें',
        'symptoms_placeholder' => 'अपने लक्षण दर्ज करें',
        'notes_placeholder' => 'पिछला चिकित्सा इतिहास या अतिरिक्त जानकारी दर्ज करें',
        'submit' => 'स्वास्थ्य समस्या जमा करें',
        'back' => 'डैशबोर्ड पर वापस जाएँ',
        'required_error' => 'कृपया सभी आवश्यक फ़ील्ड भरें।',
        'invalid_hospital' => 'कृपया पंजीकृत अस्पताल चुनें।',
        'invalid_doctor' => 'चुने गए अस्पताल में पंजीकृत डॉक्टर चुनें।',
        'invalid_symptoms' => 'सही लक्षण दर्ज करें (3-1000 अक्षर)।',
        'invalid_duration' => 'कृपया लक्षणों की सही अवधि चुनें।',
        'invalid_severity' => 'कृपया सही गंभीरता चुनें।',
        'invalid_notes' => 'अतिरिक्त जानकारी 5000 अक्षरों से अधिक नहीं होनी चाहिए और उसमें अक्षर और संख्या दोनों होने चाहिए।',
        'success' => 'स्वास्थ्य समस्या सफलतापूर्वक जमा हुई! नियुक्त डॉक्टर: ',
        'verify_error' => 'समस्या सेव हुई, लेकिन डॉक्टर असाइनमेंट की पुष्टि नहीं हो सकी।',
        'db_error' => 'डेटाबेस त्रुटि: ',
        'days_1_3' => '1-3 दिन',
        'days_4_7' => '4-7 दिन',
        'weeks_1_2' => '1-2 सप्ताह',
        'more_2_weeks' => '2 सप्ताह से अधिक',
        'more_1_month' => '1 महीने से अधिक',
    ],
];

$t = $translations[$lang];

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$patient_id = (int)$_SESSION['patient_id'];

$doctor_directory = [];
$hospital_names = [];
$doctor_result = $conn->query(
    "SELECT doctor_id, doctor_name, hospital
     FROM doctors
     WHERE TRIM(COALESCE(doctor_id, '')) <> ''
       AND TRIM(COALESCE(doctor_name, '')) <> ''
       AND TRIM(COALESCE(hospital, '')) <> ''
     ORDER BY hospital, doctor_name"
);

if (!$doctor_result) {
    http_response_code(500);
    exit($t['db_error'] . h($conn->error));
}

while ($doctor = $doctor_result->fetch_assoc()) {
    $hospital = trim($doctor['hospital']);
    $doctor_id_value = trim($doctor['doctor_id']);

    if (!isset($doctor_directory[$hospital])) {
        $doctor_directory[$hospital] = [];
    }
    $doctor_directory[$hospital][$doctor_id_value] = trim($doctor['doctor_name']);
    $hospital_names[$hospital] = $hospital;
}
$doctor_result->free();
$hospital_names = array_values($hospital_names);

$message = "";
$message_type = "";

$hospital_name = "";
$doctor_id = "";
$symptoms = "";
$duration = "";
$severity = "";
$notes = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $hospital_name = healthIssuePostValue('hospital_name');
    $doctor_id = healthIssuePostValue('doctor_id');
    $symptoms = healthIssuePostValue('symptoms');
    $duration = healthIssuePostValue('duration');
    $severity = healthIssuePostValue('severity');
    $notes = healthIssuePostValue('notes');

    if (
        $hospital_name === '' ||
        $doctor_id === '' ||
        $symptoms === '' ||
        $duration === '' ||
        $severity === ''
    ) {
        $message = $t['required_error'];
        $message_type = "error";
    } elseif (!isset($doctor_directory[$hospital_name])) {
        $message = $t['invalid_hospital'];
        $message_type = "error";
    } elseif (!isset($doctor_directory[$hospital_name][$doctor_id])) {
        $message = $t['invalid_doctor'];
        $message_type = "error";
    } elseif (
        ($symptoms_length = healthIssueCharacterCount($symptoms)) === null ||
        $symptoms_length < 3 ||
        $symptoms_length > 1000 ||
        preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $symptoms) ||
        !preg_match('/[\p{L}\p{N}]/u', $symptoms)
    ) {
        $message = $t['invalid_symptoms'];
        $message_type = "error";
    } elseif (!in_array($duration, [
        '1-3 days',
        '4-7 days',
        '1-2 weeks',
        'More than 2 weeks',
        'More than 1 month'
    ], true)) {
        $message = $t['invalid_duration'];
        $message_type = "error";
    } elseif (!in_array($severity, ['Mild', 'Moderate', 'Severe'], true)) {
        $message = $t['invalid_severity'];
        $message_type = "error";
    } elseif (
        ($notes_length = healthIssueCharacterCount($notes)) === null ||
        $notes_length > 5000 ||
        preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $notes) ||
        ($notes !== '' && (
            !preg_match('/\p{L}/u', $notes) ||
            !preg_match('/\p{N}/u', $notes)
        ))
    ) {
        $message = $t['invalid_notes'];
        $message_type = "error";
    } else {
        $doctor_sql = "SELECT doctor_id, doctor_name
                       FROM doctors
                       WHERE doctor_id = ? AND hospital = ?";
        $doctor_stmt = $conn->prepare($doctor_sql);

        if (!$doctor_stmt) {
            $message = $t['db_error'] . $conn->error;
            $message_type = "error";
        } else {
            $doctor_stmt->bind_param("ss", $doctor_id, $hospital_name);
            if (!$doctor_stmt->execute()) {
                $message = $t['db_error'] . $doctor_stmt->error;
                $message_type = "error";
            } else {
                $doctor_result = $doctor_stmt->get_result();

                if ($doctor_result->num_rows === 0) {
                    $message = $t['invalid_doctor'];
                    $message_type = "error";
                } else {
                    $doctor = $doctor_result->fetch_assoc();
                    $selected_doctor_id = (string)$doctor['doctor_id'];
                    $selected_doctor_name = (string)$doctor['doctor_name'];

                    $insert_sql = "INSERT INTO patient_issues
                        (patient_id, doctor_id, symptoms, duration, severity, issue_description, hospital_name)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

                    $insert_stmt = $conn->prepare($insert_sql);

                    if (!$insert_stmt) {
                        $message = $t['db_error'] . $conn->error;
                        $message_type = "error";
                    } else {
                        $insert_stmt->bind_param(
                            "issssss",
                            $patient_id,
                            $selected_doctor_id,
                            $symptoms,
                            $duration,
                            $severity,
                            $notes,
                            $hospital_name
                        );

                        if ($insert_stmt->execute()) {
                            $new_issue_id = $conn->insert_id;

                            $verify_sql = "SELECT doctor_id FROM patient_issues
                                           WHERE issue_id = ? AND patient_id = ?";
                            $verify_stmt = $conn->prepare($verify_sql);

                            if ($verify_stmt) {
                                $verify_stmt->bind_param("ii", $new_issue_id, $patient_id);

                                if (!$verify_stmt->execute()) {
                                    $message = $t['db_error'] . $verify_stmt->error;
                                    $message_type = "error";
                                } else {
                                    $verify_result = $verify_stmt->get_result();
                                    $saved_record = $verify_result->fetch_assoc();

                                    if (
                                        $saved_record &&
                                        (string)$saved_record['doctor_id'] === $selected_doctor_id
                                    ) {
                                        $message = $t['success'] . $selected_doctor_name;
                                        $message_type = "success";

                                        $hospital_name = "";
                                        $doctor_id = "";
                                        $symptoms = "";
                                        $duration = "";
                                        $severity = "";
                                        $notes = "";
                                    } else {
                                        $message = $t['verify_error'];
                                        $message_type = "error";
                                    }
                                }

                                $verify_stmt->close();
                            } else {
                                $message = $t['db_error'] . $conn->error;
                                $message_type = "error";
                            }
                        } else {
                            $message = $t['db_error'] . $insert_stmt->error;
                            $message_type = "error";
                        }

                        $insert_stmt->close();
                    }
                }
            }

            $doctor_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo h($lang); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h($t['page_title']); ?></title>
<style>
* { box-sizing: border-box; }
body {
    font-family: Arial, sans-serif;
    background: #f2f7fb;
    margin: 0;
    padding: 30px 15px;
}
.container {
    max-width: 650px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
h2 { text-align: center; color: #176b87; margin-bottom: 25px; }
label {
    display: block;
    margin-top: 15px;
    margin-bottom: 7px;
    font-weight: bold;
    color: #333;
}
input, select, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 15px;
}
textarea { resize: vertical; min-height: 90px; }
button {
    width: 100%;
    margin-top: 22px;
    padding: 13px;
    background: #087f8c;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
}
button:hover { background: #066875; }
.message {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
    overflow-wrap: anywhere;
}
.success { background: #e5f7eb; color: #176534; }
.error { background: #fde8e8; color: #a32121; }
.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #087fbd;
    text-decoration: none;
}
.back-link:hover { text-decoration: underline; }
@media (max-width: 480px) {
    body { padding: 15px 10px; }
    .container { padding: 20px 16px; }
}
</style>
</head>
<body>
<div class="container">
    <h2><?php echo h($t['heading']); ?></h2>

    <?php if ($message !== ""): ?>
        <div class="message <?php echo h($message_type); ?>">
            <?php echo h($message); ?>
        </div>
    <?php endif; ?>

    <form action="healthissue.php" method="POST">
        <label><?php echo h($t['hospital']); ?> *</label>
        <select name="hospital_name" id="hospitalSelect" required>
            <option value=""><?php echo h($t['hospital_placeholder']); ?></option>
            <?php foreach ($hospital_names as $hospital_option): ?>
                <option value="<?php echo h($hospital_option); ?>"
                    <?php echo $hospital_name === $hospital_option ? 'selected' : ''; ?>>
                    <?php echo h($hospital_option); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label><?php echo h($t['doctor_id']); ?> *</label>
        <select name="doctor_id" id="doctorSelect" required>
            <option value=""><?php echo h($t['doctor_placeholder']); ?></option>
        </select>

        <label><?php echo h($t['symptoms']); ?> *</label>
        <input type="text" name="symptoms"
               value="<?php echo h($symptoms); ?>"
               list="symptomSuggestions"
               maxlength="1000"
               placeholder="<?php echo h($t['symptoms_placeholder']); ?>" required>
        <datalist id="symptomSuggestions">
            <option value="Fever">
            <option value="Headache">
            <option value="Cough">
            <option value="Cold">
            <option value="Sore throat">
            <option value="Stomach pain">
            <option value="Chest pain">
            <option value="Breathing difficulty">
            <option value="Nausea">
            <option value="Dizziness">
            <option value="Joint pain">
            <option value="Skin rash">
        </datalist>

        <label><?php echo h($t['duration']); ?> *</label>
        <select name="duration" required>
            <option value=""><?php echo h($t['select_duration']); ?></option>
            <?php
            $durations = [
                "1-3 days" => $t['days_1_3'],
                "4-7 days" => $t['days_4_7'],
                "1-2 weeks" => $t['weeks_1_2'],
                "More than 2 weeks" => $t['more_2_weeks'],
                "More than 1 month" => $t['more_1_month']
            ];
            foreach ($durations as $value => $label) {
                $selected = ($duration === $value) ? "selected" : "";
                echo '<option value="' . h($value) . '" ' . $selected . '>' . h($label) . '</option>';
            }
            ?>
        </select>

        <label><?php echo h($t['severity']); ?> *</label>
        <select name="severity" required>
            <option value=""><?php echo h($t['select_severity']); ?></option>
            <?php
            // Store the same English values in the database for compatibility.
            $severities = [
                "Mild" => $t['mild'],
                "Moderate" => $t['moderate'],
                "Severe" => $t['severe']
            ];
            foreach ($severities as $value => $label) {
                $selected = ($severity === $value) ? "selected" : "";
                echo '<option value="' . h($value) . '" ' . $selected . '>' . h($label) . '</option>';
            }
            ?>
        </select>

        <label><?php echo h($t['details']); ?></label>
        <textarea name="notes" maxlength="5000" placeholder="<?php echo h($t['notes_placeholder']); ?>"><?php echo h($notes); ?></textarea>

        <button type="submit"><?php echo h($t['submit']); ?></button>
    </form>

    <a class="back-link" href="patient.php"><?php echo h($t['back']); ?></a>
</div>
<script>
const doctorDirectory = <?php
    echo json_encode(
        $doctor_directory,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
    );
?>;
const hospitalSelect = document.getElementById("hospitalSelect");
const doctorSelect = document.getElementById("doctorSelect");
const selectedDoctorId = <?php
    echo json_encode(
        $doctor_id,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
    );
?>;

function updateDoctors() {
    const doctors = doctorDirectory[hospitalSelect.value] || {};
    const previousDoctor = doctorSelect.value;

    doctorSelect.replaceChildren(new Option(
        <?php echo json_encode($t['doctor_placeholder'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
        ''
    ));

    Object.entries(doctors).forEach(function([doctorId, doctorName]) {
        doctorSelect.add(new Option(doctorName, doctorId));
    });

    doctorSelect.value = Object.prototype.hasOwnProperty.call(doctors, previousDoctor)
        ? previousDoctor
        : '';
}

hospitalSelect.addEventListener("change", updateDoctors);
updateDoctors();
if (selectedDoctorId !== '') {
    doctorSelect.value = selectedDoctorId;
}
</script>
</body>
</html>
