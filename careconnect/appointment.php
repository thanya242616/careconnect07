
<?php
session_start();

function appointmentPostString(string $key): ?string
{
    $value = $_POST[$key] ?? null;

    return is_string($value) ? trim($value) : null;
}

function appointmentCharacterCount(string $value): ?int
{
    $count = preg_match_all('/./us', $value, $matches);

    return $count === false ? null : $count;
}

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.html");
    exit();
}

include "db.php";

$language = $_SESSION['language'] ?? 'en';

$translations = [
    'en' => [
        'logout'=>'Logout',
        'title'=>'Book an Appointment',
        'subtitle'=>'Schedule an appointment with a doctor for your healthcare needs.',
        'patient_name'=>'Patient Name',
        'patient_id'=>'Patient ID',
        'hospital'=>'Select Hospital',
        'hospital_placeholder'=>'Choose Hospital',
        'specialization'=>'Select Specialization',
        'choose_specialization'=>'Choose Specialization',
        'cardiology'=>'Cardiology',
        'dermatology'=>'Dermatology',
        'general_medicine'=>'General Medicine',
        'orthopedics'=>'Orthopedics',
        'pediatrics'=>'Pediatrics',
        'neurology'=>'Neurology',
        'other'=>'Other',
        'doctor'=>'Doctor Name',
        'doctor_placeholder'=>'Choose Doctor',
        'date'=>'Appointment Date',
        'time'=>'Appointment Time',
        'choose_time'=>'Choose Time',
        'reason'=>'Reason for Visit',
        'reason_placeholder'=>'Briefly describe your health concern',
        'book'=>'Book Appointment',
        'back'=>'← Back to Patient Dashboard',
        'footer'=>'Your Health. Your Records. Your Control.',
        'fill'=>'Please fill all the fields.',
        'future'=>'Please select a valid date today or in the future.',
        'invalid_hospital'=>'Please choose a registered hospital.',
        'invalid_specialization'=>'Please select a valid specialization.',
        'invalid_doctor'=>'Choose a doctor registered for the selected hospital and specialization.',
        'invalid_time'=>'Please select a valid appointment time.',
        'past_time'=>'The selected appointment time has already passed.',
        'invalid_reason'=>'Enter a reason of 10-2000 characters.',
        'success'=>'Appointment request submitted successfully!',
        'failed'=>'Unable to book appointment. Please try again.',
        'db_error'=>'Database query error.'
    ],
    'ta' => [
        'logout'=>'வெளியேறு',
        'title'=>'மருத்துவர் சந்திப்பைப் பதிவு செய்க',
        'subtitle'=>'உங்கள் மருத்துவத் தேவைகளுக்காக மருத்துவரைச் சந்திக்க நேரம் பதிவு செய்யுங்கள்.',
        'patient_name'=>'நோயாளியின் பெயர்',
        'patient_id'=>'நோயாளி அடையாள எண்',
        'hospital'=>'மருத்துவமனையைத் தேர்ந்தெடுக்கவும்',
        'hospital_placeholder'=>'மருத்துவமனையைத் தேர்ந்தெடுக்கவும்',
        'specialization'=>'மருத்துவப் பிரிவைத் தேர்ந்தெடுக்கவும்',
        'choose_specialization'=>'மருத்துவப் பிரிவைத் தேர்ந்தெடுக்கவும்',
        'cardiology'=>'இதய மருத்துவம்',
        'dermatology'=>'தோல் மருத்துவம்',
        'general_medicine'=>'பொது மருத்துவம்',
        'orthopedics'=>'எலும்பியல் மருத்துவம்',
        'pediatrics'=>'குழந்தைகள் மருத்துவம்',
        'neurology'=>'நரம்பியல் மருத்துவம்',
        'other'=>'மற்றவை',
        'doctor'=>'மருத்துவரின் பெயர்',
        'doctor_placeholder'=>'மருத்துவரைத் தேர்ந்தெடுக்கவும்',
        'date'=>'சந்திப்பு தேதி',
        'time'=>'சந்திப்பு நேரம்',
        'choose_time'=>'நேரத்தைத் தேர்ந்தெடுக்கவும்',
        'reason'=>'சந்திப்புக்கான காரணம்',
        'reason_placeholder'=>'உங்கள் உடல்நலப் பிரச்சினையைச் சுருக்கமாக எழுதவும்',
        'book'=>'சந்திப்பைப் பதிவு செய்க',
        'back'=>'← நோயாளி முகப்புப் பக்கத்திற்குத் திரும்புக',
        'footer'=>'உங்கள் ஆரோக்கியம். உங்கள் பதிவுகள். உங்கள் கட்டுப்பாடு.',
        'fill'=>'அனைத்து விவரங்களையும் நிரப்பவும்.',
        'future'=>'இன்றைய அல்லது எதிர்காலச் சரியான தேதியைத் தேர்ந்தெடுக்கவும்.',
        'invalid_hospital'=>'பதிவுசெய்யப்பட்ட மருத்துவமனையைத் தேர்ந்தெடுக்கவும்.',
        'invalid_specialization'=>'சரியான மருத்துவப் பிரிவைத் தேர்ந்தெடுக்கவும்.',
        'invalid_doctor'=>'தேர்ந்தெடுத்த மருத்துவமனை மற்றும் பிரிவில் உள்ள மருத்துவரைத் தேர்ந்தெடுக்கவும்.',
        'invalid_time'=>'சரியான சந்திப்பு நேரத்தைத் தேர்ந்தெடுக்கவும்.',
        'past_time'=>'தேர்ந்தெடுத்த சந்திப்பு நேரம் கடந்துவிட்டது.',
        'invalid_reason'=>'10-2000 எழுத்துகளுக்குள் சந்திப்புக்கான காரணத்தை உள்ளிடவும்.',
        'success'=>'மருத்துவர் சந்திப்பு கோரிக்கை வெற்றிகரமாகப் பதிவு செய்யப்பட்டது!',
        'failed'=>'சந்திப்பைப் பதிவு செய்ய முடியவில்லை. மீண்டும் முயற்சிக்கவும்.',
        'db_error'=>'தரவுத்தள வினவலில் பிழை ஏற்பட்டுள்ளது.'
    ],
    'hi' => [
        'logout'=>'लॉग आउट',
        'title'=>'अपॉइंटमेंट बुक करें',
        'subtitle'=>'अपनी स्वास्थ्य आवश्यकताओं के लिए डॉक्टर से मिलने का समय तय करें।',
        'patient_name'=>'मरीज़ का नाम',
        'patient_id'=>'मरीज़ पहचान संख्या',
        'hospital'=>'अस्पताल चुनें',
        'hospital_placeholder'=>'अस्पताल चुनें',
        'specialization'=>'विशेषज्ञता चुनें',
        'choose_specialization'=>'विशेषज्ञता चुनें',
        'cardiology'=>'हृदय रोग',
        'dermatology'=>'त्वचा रोग',
        'general_medicine'=>'सामान्य चिकित्सा',
        'orthopedics'=>'हड्डी रोग',
        'pediatrics'=>'बाल चिकित्सा',
        'neurology'=>'तंत्रिका रोग',
        'other'=>'अन्य',
        'doctor'=>'डॉक्टर का नाम',
        'doctor_placeholder'=>'डॉक्टर चुनें',
        'date'=>'अपॉइंटमेंट की तारीख',
        'time'=>'अपॉइंटमेंट का समय',
        'choose_time'=>'समय चुनें',
        'reason'=>'मिलने का कारण',
        'reason_placeholder'=>'अपनी स्वास्थ्य समस्या संक्षेप में लिखें',
        'book'=>'अपॉइंटमेंट बुक करें',
        'back'=>'← मरीज़ डैशबोर्ड पर वापस जाएँ',
        'footer'=>'आपका स्वास्थ्य। आपके रिकॉर्ड। आपका नियंत्रण।',
        'fill'=>'कृपया सभी फ़ील्ड भरें।',
        'future'=>'कृपया आज या भविष्य की सही तारीख चुनें।',
        'invalid_hospital'=>'पंजीकृत अस्पताल चुनें।',
        'invalid_specialization'=>'कृपया सही विशेषज्ञता चुनें।',
        'invalid_doctor'=>'चुने गए अस्पताल और विशेषज्ञता के डॉक्टर को चुनें।',
        'invalid_time'=>'कृपया सही अपॉइंटमेंट समय चुनें।',
        'past_time'=>'चुना गया अपॉइंटमेंट समय बीत चुका है।',
        'invalid_reason'=>'10-2000 अक्षरों में मिलने का कारण दर्ज करें।',
        'success'=>'अपॉइंटमेंट अनुरोध सफलतापूर्वक दर्ज किया गया!',
        'failed'=>'अपॉइंटमेंट बुक नहीं हो पाया। कृपया फिर से कोशिश करें।',
        'db_error'=>'डेटाबेस क्वेरी में त्रुटि हुई।'
    ]
];

if (!isset($translations[$language])) {
    $language = 'en';
}

$t = $translations[$language];

$patient_id = (int) $_SESSION['patient_id'];
$patient_name = $_SESSION['patient_name'] ?? 'Patient';

$doctorDirectory = [];
$hospitalNames = [];
$specializationNames = [];
$doctorResult = $conn->query(
    "SELECT doctor_name, specialization, hospital
     FROM doctors
     WHERE TRIM(COALESCE(doctor_name, '')) <> ''
       AND TRIM(COALESCE(specialization, '')) <> ''
       AND TRIM(COALESCE(hospital, '')) <> ''
     ORDER BY hospital, specialization, doctor_name"
);

if (!$doctorResult) {
    http_response_code(500);
    exit('Unable to load hospital and doctor list: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}

while ($doctor = $doctorResult->fetch_assoc()) {
    $hospital = trim($doctor['hospital']);
    $specialization = trim($doctor['specialization']);
    $doctorName = trim($doctor['doctor_name']);

    if (!isset($doctorDirectory[$hospital][$specialization])) {
        $doctorDirectory[$hospital][$specialization] = [];
    }
    if (!in_array($doctorName, $doctorDirectory[$hospital][$specialization], true)) {
        $doctorDirectory[$hospital][$specialization][] = $doctorName;
    }
    $hospitalNames[$hospital] = $hospital;
    $specializationNames[$specialization] = $specialization;
}
$doctorResult->free();

$hospitalNames = array_values($hospitalNames);
$specializationNames = array_values($specializationNames);

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $hospital_name = appointmentPostString('hospital_name');
    $specialization = appointmentPostString('specialization');
    $doctor_name = appointmentPostString('doctor_name');
    $appointment_date = appointmentPostString('appointment_date');
    $appointment_time = appointmentPostString('appointment_time');
    $reason = appointmentPostString('reason');
    $validationError = null;

    if (
        $hospital_name === null ||
        $specialization === null ||
        $doctor_name === null ||
        $appointment_date === null ||
        $appointment_time === null ||
        $reason === null ||
        $hospital_name === '' ||
        $specialization === '' ||
        $doctor_name === '' ||
        $appointment_date === '' ||
        $appointment_time === '' ||
        $reason === ''
    ) {
        $validationError = 'fill';
    } elseif (!isset($doctorDirectory[$hospital_name])) {
        $validationError = 'invalid_hospital';
    } elseif (!isset($doctorDirectory[$hospital_name][$specialization])) {
        $validationError = 'invalid_specialization';
    } elseif (!in_array($doctor_name, $doctorDirectory[$hospital_name][$specialization], true)) {
        $validationError = 'invalid_doctor';
    } elseif (
        ($reasonLength = appointmentCharacterCount($reason)) === null ||
        $reasonLength < 10 ||
        $reasonLength > 2000 ||
        preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $reason) ||
        !preg_match('/[\p{L}\p{N}]/u', $reason)
    ) {
        $validationError = 'invalid_reason';
    } elseif (!in_array($appointment_time, [
        '09:00 AM',
        '10:00 AM',
        '11:00 AM',
        '12:00 PM',
        '02:00 PM',
        '03:00 PM',
        '04:00 PM'
    ], true)) {
        $validationError = 'invalid_time';
    } else {
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $appointment_date);
        $dateErrors = DateTimeImmutable::getLastErrors();

        if (
            !$parsedDate ||
            $parsedDate->format('Y-m-d') !== $appointment_date ||
            ($dateErrors !== false &&
                ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) ||
            $appointment_date < date('Y-m-d')
        ) {
            $validationError = 'future';
        } elseif ($appointment_date === date('Y-m-d')) {
            $parsedTime = DateTimeImmutable::createFromFormat('!h:i A', $appointment_time);
            $timeErrors = DateTimeImmutable::getLastErrors();

            if (
                !$parsedTime ||
                ($timeErrors !== false &&
                    ($timeErrors['warning_count'] > 0 || $timeErrors['error_count'] > 0)) ||
                $parsedDate->setTime(
                    (int) $parsedTime->format('H'),
                    (int) $parsedTime->format('i')
                ) <= new DateTimeImmutable('now')
            ) {
                $validationError = 'past_time';
            }
        }
    }

    if ($validationError !== null) {
        $message = $t[$validationError];
        $message_type = "error";

    } else {

        $sql = "INSERT INTO appointments
        (patient_id, patient_name, hospital_name, specialization,
         doctor_name, appointment_date, appointment_time, reason)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param(
                "isssssss",
                $patient_id,
                $patient_name,
                $hospital_name,
                $specialization,
                $doctor_name,
                $appointment_date,
                $appointment_time,
                $reason
            );

            if ($stmt->execute()) {
                $message = $t['success'];
                $message_type = "success";
            } else {
                $message = $t['failed'];
                $message_type = "error";
            }

            $stmt->close();

        } else {
            $message = $t['db_error'];
            $message_type = "error";
        }
    }
}

$display_name = htmlspecialchars($patient_name, ENT_QUOTES, 'UTF-8');
$display_id = htmlspecialchars((string)$patient_id, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CareConnect - <?php echo $t['title']; ?></title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f2f7f9;
    color: #263746;
}

header {
    background: #087f8c;
    color: white;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

header h2 { font-size: 24px; }

header a {
    color: white;
    text-decoration: none;
    background: #05636e;
    padding: 10px 16px;
    border-radius: 6px;
}

.container {
    max-width: 750px;
    margin: 35px auto;
    padding: 0 20px;
}

.form-box {
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 15px #00000010;
}

.form-box h1 {
    color: #087f8c;
    margin-bottom: 10px;
    line-height: 1.5;
}

.subtitle {
    color: #687985;
    margin-bottom: 25px;
    line-height: 1.8;
}

.form-group { margin-bottom: 18px; }

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    font-size: 14px;
    line-height: 1.6;
}

input, select, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccdadd;
    border-radius: 6px;
    font-size: 14px;
    outline: none;
    background: white;
}

input:focus, select:focus, textarea:focus {
    border-color: #087f8c;
}

input[readonly] { background: #f0f5f6; }

textarea {
    resize: vertical;
    min-height: 90px;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

button {
    width: 100%;
    background: #087f8c;
    color: white;
    border: none;
    padding: 14px;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
    margin-top: 10px;
}

button:hover { background: #05636e; }

.back {
    display: block;
    text-align: center;
    color: #087f8c;
    text-decoration: none;
    margin-top: 20px;
    line-height: 1.8;
}

.message {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 6px;
    line-height: 1.8;
}

.success { background: #e4f7eb; color: #176b38; }
.error { background: #fdeaea; color: #a52222; }

footer {
    text-align: center;
    padding: 25px;
    color: #7b8992;
    line-height: 1.8;
}

@media(max-width: 600px) {
    .row { grid-template-columns: 1fr; gap: 0; }
    header { padding: 18px; }
    .form-box { padding: 22px; }
}
</style>
</head>

<body>

<header>
    <h2>♥ CareConnect</h2>
    <a href="index.html"><?php echo $t['logout']; ?></a>
</header>

<div class="container">
<div class="form-box">

    <h1><?php echo $t['title']; ?></h1>
    <p class="subtitle"><?php echo $t['subtitle']; ?></p>

    <?php if ($message !== ''): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="appointment.php">

        <div class="form-group">
            <label><?php echo $t['patient_name']; ?></label>
            <input type="text" value="<?php echo $display_name; ?>" readonly>
        </div>

        <div class="form-group">
            <label><?php echo $t['patient_id']; ?></label>
            <input type="text" value="<?php echo $display_id; ?>" readonly>
        </div>

        <div class="form-group">
            <label><?php echo $t['hospital']; ?></label>
            <select name="hospital_name" id="hospitalSelect" required>
                <option value=""><?php echo $t['hospital_placeholder']; ?></option>
                <?php foreach ($hospitalNames as $hospitalOption): ?>
                    <option value="<?php echo htmlspecialchars($hospitalOption, ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo (($hospital_name ?? '') === $hospitalOption) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($hospitalOption, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label><?php echo $t['specialization']; ?></label>
            <select name="specialization" id="specializationSelect" required>
                <option value=""><?php echo $t['choose_specialization']; ?></option>
                <?php foreach ($specializationNames as $specializationOption): ?>
                    <?php
                    $specializationTranslationKeys = [
                        'Cardiology' => 'cardiology',
                        'Dermatology' => 'dermatology',
                        'General Medicine' => 'general_medicine',
                        'Orthopedics' => 'orthopedics',
                        'Pediatrics' => 'pediatrics',
                        'Neurology' => 'neurology',
                        'Other' => 'other'
                    ];
                    $specializationLabel = isset($specializationTranslationKeys[$specializationOption])
                        ? $t[$specializationTranslationKeys[$specializationOption]]
                        : $specializationOption;
                    ?>
                    <option value="<?php echo htmlspecialchars($specializationOption, ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo (($specialization ?? '') === $specializationOption) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($specializationLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label><?php echo $t['doctor']; ?></label>
            <select name="doctor_name" id="doctorSelect" required>
                <option value=""><?php echo $t['doctor_placeholder']; ?></option>
            </select>
        </div>

        <div class="row">
            <div class="form-group">
                <label><?php echo $t['date']; ?></label>
                <input type="date" name="appointment_date"
                       id="appointmentDate" required>
            </div>

            <div class="form-group">
                <label><?php echo $t['time']; ?></label>
                <select name="appointment_time" required>
                    <option value=""><?php echo $t['choose_time']; ?></option>
                    <option value="09:00 AM">09:00 AM</option>
                    <option value="10:00 AM">10:00 AM</option>
                    <option value="11:00 AM">11:00 AM</option>
                    <option value="12:00 PM">12:00 PM</option>
                    <option value="02:00 PM">02:00 PM</option>
                    <option value="03:00 PM">03:00 PM</option>
                    <option value="04:00 PM">04:00 PM</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label><?php echo $t['reason']; ?></label>
            <textarea name="reason"
                placeholder="<?php echo $t['reason_placeholder']; ?>"
                required></textarea>
        </div>

        <button type="submit"><?php echo $t['book']; ?></button>

    </form>

    <a href="patient.php" class="back"><?php echo $t['back']; ?></a>

</div>
</div>

<footer>
    © 2026 CareConnect | <?php echo $t['footer']; ?>
</footer>

<script>
const dateInput = document.getElementById("appointmentDate");
const hospitalSelect = document.getElementById("hospitalSelect");
const specializationSelect = document.getElementById("specializationSelect");
const doctorSelect = document.getElementById("doctorSelect");
const doctorDirectory = <?php
    echo json_encode(
        $doctorDirectory,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
    );
?>;
const selectedHospital = <?php
    echo json_encode(
        $hospital_name ?? '',
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
    );
?>;
const selectedSpecialization = <?php
    echo json_encode(
        $specialization ?? '',
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
    );
?>;
const selectedDoctor = <?php
    echo json_encode(
        $doctor_name ?? '',
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
    );
?>;

function updateSpecializations() {
    const hospital = hospitalSelect.value;
    const availableSpecializations = Object.keys(doctorDirectory[hospital] || {});
    const currentSpecialization = specializationSelect.value;

    specializationSelect.replaceChildren(new Option(
        <?php echo json_encode($t['choose_specialization'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
        ''
    ));

    availableSpecializations.forEach(function(specialization) {
        const option = new Option(specialization, specialization);
        specializationSelect.add(option);
    });

    specializationSelect.value = availableSpecializations.includes(currentSpecialization)
        ? currentSpecialization
        : '';

    updateDoctors();
}

function updateDoctors() {
    const doctors = (doctorDirectory[hospitalSelect.value] || {})[specializationSelect.value] || [];
    const currentDoctor = doctorSelect.value;

    doctorSelect.replaceChildren(new Option(
        <?php echo json_encode($t['doctor_placeholder'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
        ''
    ));

    doctors.forEach(function(doctor) {
        doctorSelect.add(new Option(doctor, doctor));
    });

    doctorSelect.value = doctors.includes(currentDoctor) ? currentDoctor : '';
}

hospitalSelect.addEventListener('change', updateSpecializations);
specializationSelect.addEventListener('change', updateDoctors);

if (selectedHospital !== '') {
    hospitalSelect.value = selectedHospital;
}
updateSpecializations();
if (selectedSpecialization !== '') {
    specializationSelect.value = selectedSpecialization;
    updateDoctors();
}
if (selectedDoctor !== '') {
    doctorSelect.value = selectedDoctor;
}

const today = new Date();
const localDate = new Date(
    today.getTime() - today.getTimezoneOffset() * 60000
).toISOString().split("T")[0];

dateInput.min = localDate;
</script>

</body>
</html>
