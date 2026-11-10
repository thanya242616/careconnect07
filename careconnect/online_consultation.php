<?php
session_start();

function consultationPostValue(string $key): ?string
{
    $value = $_POST[$key] ?? null;

    return is_string($value) ? trim($value) : null;
}

function consultationCharacterCount(string $value): ?int
{
    $count = preg_match_all('/./us', $value, $matches);

    return $count === false ? null : $count;
}

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.html");
    exit();
}

require_once "db.php";
date_default_timezone_set("Asia/Kolkata");

$patient_id = (int)$_SESSION['patient_id'];
$patient_name = $_SESSION['patient_name'] ?? 'Patient';
if (
    !is_string($patient_name) ||
    ($patientNameLength = consultationCharacterCount($patient_name)) === null ||
    $patientNameLength < 2 ||
    $patientNameLength > 100 ||
    !preg_match('/\p{L}/u', $patient_name)
) {
    $patient_name = 'Patient';
}
$language = $_SESSION['language'] ?? 'en';
if (!in_array($language, ['en', 'ta', 'hi'], true)) $language = 'en';

$translations = [
'en' => [
 'page_title'=>'CareConnect - Online Consultation','patient'=>'Patient','dashboard'=>'Dashboard','reports'=>'Medical Reports','appointments'=>'Appointments','online'=>'Online Consultation','prescriptions'=>'My Prescription','logout'=>'Logout','tagline'=>'Your Health. Your Records. Your Control.','health_matters'=>'Your Health Matters','brand'=>'CARECONNECT','hero_title'=>'Online Consultation','hero_desc'=>'Connect with your doctor from anywhere. Book your consultation, receive your doctor\'s response, and join your online appointment using the meeting link shared by your doctor.','book_title'=>'Book Consultation','book_sub'=>'Request an online appointment','patient_name'=>'Patient Name','patient_id'=>'Patient ID','hospital'=>'Hospital Name','doctor_id'=>'Doctor ID','doctor_name'=>'Doctor Name','date'=>'Consultation Date','time'=>'Consultation Time','symptoms'=>'Symptoms / Reason','hospital_placeholder'=>'Enter hospital name','doctor_id_placeholder'=>'Enter doctor ID','doctor_name_placeholder'=>'Enter doctor name','symptoms_placeholder'=>'Describe your health problem...','request'=>'📹 Request Online Consultation','total'=>'Total Requests','accepted_progress'=>'Accepted / In Progress','pending'=>'Pending','notification_accepted'=>'Consultation Accepted','notification_rejected'=>'Consultation Rejected','has'=>'has','your_request'=>'your consultation request.','my_consultations'=>'My Consultations','track'=>'Track your online appointments','date_label'=>'Date','time_label'=>'Time','symptoms_label'=>'Symptoms:','doctor_response'=>'Doctor Response:','join'=>'🎥 Join Google Meet','accepted_wait'=>'⏳ Consultation accepted. Waiting for doctor to share the Google Meet link.','pending_wait'=>'⏳ Waiting for doctor approval','rejected_wait'=>'❌ Consultation request rejected','completed_wait'=>'✅ Consultation completed','status_unknown'=>'Consultation status:','empty_title'=>'No Consultations Yet','empty_desc'=>'Book your first online consultation with a doctor.','footer'=>'CareConnect | Online Consultation','success'=>'Your consultation request has been sent successfully.','fill_required'=>'Please fill all required fields.','future_date'=>'Please select today or a future date.','book_error'=>'Unable to book consultation. Please try again.','db_error'=>'Database error: ','status_pending'=>'Pending','status_accepted'=>'Accepted','status_rejected'=>'Rejected','status_progress'=>'In Progress','status_completed'=>'Completed','doctor'=>'Doctor','patient_role'=>'Patient'
],
'ta' => [
 'page_title'=>'CareConnect - ஆன்லைன் ஆலோசனை','patient'=>'நோயாளர்','dashboard'=>'முகப்பு','reports'=>'மருத்துவ அறிக்கைகள்','appointments'=>'சந்திப்புகள்','online'=>'ஆன்லைன் ஆலோசனை','prescriptions'=>'எனது மருந்துச் சீட்டு','logout'=>'வெளியேறு','tagline'=>'உங்கள் ஆரோக்கியம். உங்கள் பதிவுகள். உங்கள் கட்டுப்பாடு.','health_matters'=>'உங்கள் ஆரோக்கியம் முக்கியம்','brand'=>'CARECONNECT','hero_title'=>'ஆன்லைன் ஆலோசனை','hero_desc'=>'எங்கிருந்தும் உங்கள் மருத்துவருடன் தொடர்புகொள்ளுங்கள். ஆலோசனையை முன்பதிவு செய்து, மருத்துவரின் பதிலைப் பெற்று, அவர் பகிரும் சந்திப்பு இணைப்பைப் பயன்படுத்தி ஆன்லைன் சந்திப்பில் இணையுங்கள்.','book_title'=>'ஆலோசனையை முன்பதிவு செய்க','book_sub'=>'ஆன்லைன் சந்திப்பைக் கோருங்கள்','patient_name'=>'நோயாளர் பெயர்','patient_id'=>'நோயாளர் ID','hospital'=>'மருத்துவமனை பெயர்','doctor_id'=>'மருத்துவர் ID','doctor_name'=>'மருத்துவர் பெயர்','date'=>'ஆலோசனை தேதி','time'=>'ஆலோசனை நேரம்','symptoms'=>'அறிகுறிகள் / காரணம்','hospital_placeholder'=>'மருத்துவமனை பெயரை உள்ளிடவும்','doctor_id_placeholder'=>'மருத்துவர் ID-ஐ உள்ளிடவும்','doctor_name_placeholder'=>'மருத்துவர் பெயரை உள்ளிடவும்','symptoms_placeholder'=>'உங்கள் உடல்நலப் பிரச்சினையை விவரிக்கவும்...','request'=>'📹 ஆன்லைன் ஆலோசனையைக் கோரு','total'=>'மொத்த கோரிக்கைகள்','accepted_progress'=>'ஏற்கப்பட்டது / நடைபெறுகிறது','pending'=>'நிலுவையில்','notification_accepted'=>'ஆலோசனை ஏற்கப்பட்டது','notification_rejected'=>'ஆலோசனை நிராகரிக்கப்பட்டது','has'=>'உங்கள் ஆலோசனைக் கோரிக்கையை','your_request'=>'என்ற நிலைக்கு மாற்றியுள்ளார்.','my_consultations'=>'எனது ஆலோசனைகள்','track'=>'உங்கள் ஆன்லைன் சந்திப்புகளைக் கண்காணிக்கவும்','date_label'=>'தேதி','time_label'=>'நேரம்','symptoms_label'=>'அறிகுறிகள்:','doctor_response'=>'மருத்துவரின் பதில்:','join'=>'🎥 Google Meet-இல் இணையுங்கள்','accepted_wait'=>'⏳ ஆலோசனை ஏற்கப்பட்டது. மருத்துவர் Google Meet இணைப்பைப் பகிர காத்திருக்கவும்.','pending_wait'=>'⏳ மருத்துவரின் ஒப்புதலுக்காகக் காத்திருக்கிறது','rejected_wait'=>'❌ ஆலோசனைக் கோரிக்கை நிராகரிக்கப்பட்டது','completed_wait'=>'✅ ஆலோசனை முடிந்தது','status_unknown'=>'ஆலோசனை நிலை:','empty_title'=>'ஆலோசனைகள் எதுவும் இல்லை','empty_desc'=>'மருத்துவருடன் உங்கள் முதல் ஆன்லைன் ஆலோசனையை முன்பதிவு செய்யுங்கள்.','footer'=>'CareConnect | ஆன்லைன் ஆலோசனை','success'=>'உங்கள் ஆலோசனைக் கோரிக்கை வெற்றிகரமாக அனுப்பப்பட்டது.','fill_required'=>'தேவையான அனைத்து புலங்களையும் நிரப்பவும்.','future_date'=>'இன்றைய தேதியையோ எதிர்கால தேதியையோ தேர்ந்தெடுக்கவும்.','book_error'=>'ஆலோசனையை முன்பதிவு செய்ய முடியவில்லை. மீண்டும் முயற்சிக்கவும்.','db_error'=>'தரவுத்தளப் பிழை: ','status_pending'=>'நிலுவையில்','status_accepted'=>'ஏற்கப்பட்டது','status_rejected'=>'நிராகரிக்கப்பட்டது','status_progress'=>'நடைபெறுகிறது','status_completed'=>'முடிந்தது','doctor'=>'மருத்துவர்','patient_role'=>'நோயாளர்'
],
'hi' => [
 'page_title'=>'CareConnect - ऑनलाइन परामर्श','patient'=>'मरीज़','dashboard'=>'डैशबोर्ड','reports'=>'मेडिकल रिपोर्ट','appointments'=>'अपॉइंटमेंट','online'=>'ऑनलाइन परामर्श','prescriptions'=>'मेरी दवा पर्ची','logout'=>'लॉग आउट','tagline'=>'आपका स्वास्थ्य। आपके रिकॉर्ड। आपका नियंत्रण।','health_matters'=>'आपका स्वास्थ्य महत्वपूर्ण है','brand'=>'CARECONNECT','hero_title'=>'ऑनलाइन परामर्श','hero_desc'=>'कहीं से भी अपने डॉक्टर से जुड़ें। परामर्श बुक करें, डॉक्टर का जवाब प्राप्त करें और डॉक्टर द्वारा साझा किए गए मीटिंग लिंक से ऑनलाइन अपॉइंटमेंट में शामिल हों।','book_title'=>'परामर्श बुक करें','book_sub'=>'ऑनलाइन अपॉइंटमेंट का अनुरोध करें','patient_name'=>'मरीज़ का नाम','patient_id'=>'मरीज़ ID','hospital'=>'अस्पताल का नाम','doctor_id'=>'डॉक्टर ID','doctor_name'=>'डॉक्टर का नाम','date'=>'परामर्श की तारीख','time'=>'परामर्श का समय','symptoms'=>'लक्षण / कारण','hospital_placeholder'=>'अस्पताल का नाम दर्ज करें','doctor_id_placeholder'=>'डॉक्टर ID दर्ज करें','doctor_name_placeholder'=>'डॉक्टर का नाम दर्ज करें','symptoms_placeholder'=>'अपनी स्वास्थ्य समस्या बताएं...','request'=>'📹 ऑनलाइन परामर्श का अनुरोध करें','total'=>'कुल अनुरोध','accepted_progress'=>'स्वीकृत / जारी','pending'=>'लंबित','notification_accepted'=>'परामर्श स्वीकृत','notification_rejected'=>'परामर्श अस्वीकृत','has'=>'ने आपका परामर्श अनुरोध','your_request'=>'किया है।','my_consultations'=>'मेरे परामर्श','track'=>'अपने ऑनलाइन अपॉइंटमेंट देखें','date_label'=>'तारीख','time_label'=>'समय','symptoms_label'=>'लक्षण:','doctor_response'=>'डॉक्टर का जवाब:','join'=>'🎥 Google Meet में शामिल हों','accepted_wait'=>'⏳ परामर्श स्वीकृत है। डॉक्टर के Google Meet लिंक साझा करने की प्रतीक्षा करें।','pending_wait'=>'⏳ डॉक्टर की मंज़ूरी की प्रतीक्षा है','rejected_wait'=>'❌ परामर्श अनुरोध अस्वीकृत','completed_wait'=>'✅ परामर्श पूरा हुआ','status_unknown'=>'परामर्श स्थिति:','empty_title'=>'अभी कोई परामर्श नहीं','empty_desc'=>'डॉक्टर के साथ अपना पहला ऑनलाइन परामर्श बुक करें।','footer'=>'CareConnect | ऑनलाइन परामर्श','success'=>'आपका परामर्श अनुरोध सफलतापूर्वक भेज दिया गया है।','fill_required'=>'कृपया सभी आवश्यक फ़ील्ड भरें।','future_date'=>'आज या भविष्य की तारीख चुनें।','book_error'=>'परामर्श बुक नहीं हो सका। कृपया फिर से प्रयास करें।','db_error'=>'डेटाबेस त्रुटि: ','status_pending'=>'लंबित','status_accepted'=>'स्वीकृत','status_rejected'=>'अस्वीकृत','status_progress'=>'जारी है','status_completed'=>'पूरा हुआ','doctor'=>'डॉक्टर','patient_role'=>'मरीज़'
]
];
$t = $translations[$language];

if ($language === 'en') {
    $t['hospital_placeholder'] = 'Select a registered hospital';
    $t['doctor_id_placeholder'] = 'Select a doctor';
    $t['doctor_name_placeholder'] = 'Select a doctor';
} elseif ($language === 'ta') {
    $t['hospital_placeholder'] = 'பதிவுசெய்யப்பட்ட மருத்துவமனையைத் தேர்ந்தெடுக்கவும்';
    $t['doctor_id_placeholder'] = 'மருத்துவரைத் தேர்ந்தெடுக்கவும்';
    $t['doctor_name_placeholder'] = 'மருத்துவரைத் தேர்ந்தெடுக்கவும்';
} else {
    $t['hospital_placeholder'] = 'पंजीकृत अस्पताल चुनें';
    $t['doctor_id_placeholder'] = 'डॉक्टर चुनें';
    $t['doctor_name_placeholder'] = 'डॉक्टर चुनें';
}

function h($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
function status_text($status, $t) {
    $s = strtolower(trim((string)$status));
    if ($s === 'pending') return $t['status_pending'];
    if ($s === 'accepted') return $t['status_accepted'];
    if ($s === 'rejected') return $t['status_rejected'];
    if ($s === 'in progress') return $t['status_progress'];
    if ($s === 'completed') return $t['status_completed'];
    return (string)$status;
}

$doctorDirectory = [];
$hospitalNames = [];
$doctorResult = $conn->query(
    "SELECT doctor_id, doctor_name, hospital
     FROM doctors
     WHERE TRIM(COALESCE(doctor_id, '')) <> ''
       AND TRIM(COALESCE(doctor_name, '')) <> ''
       AND TRIM(COALESCE(hospital, '')) <> ''
     ORDER BY hospital, doctor_name"
);

if (!$doctorResult) {
    http_response_code(500);
    exit(h($t['db_error'] . $conn->error));
}

while ($doctor = $doctorResult->fetch_assoc()) {
    $hospital = trim($doctor['hospital']);
    $doctorId = trim($doctor['doctor_id']);
    $doctorName = trim($doctor['doctor_name']);
    $doctorDirectory[$hospital][$doctorId] = $doctorName;
    $hospitalNames[$hospital] = $hospital;
}
$doctorResult->free();
$hospitalNames = array_values($hospitalNames);

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_consultation'])) {
    $hospital_name = consultationPostValue('hospital_name');
    $doctor_id = consultationPostValue('doctor_id');
    $consultation_date = consultationPostValue('consultation_date');
    $consultation_time = consultationPostValue('consultation_time');
    $symptoms = consultationPostValue('symptoms');

    if (
        $hospital_name === null ||
        $doctor_id === null ||
        $consultation_date === null ||
        $consultation_time === null ||
        $symptoms === null ||
        $hospital_name === '' ||
        $doctor_id === '' ||
        $consultation_date === '' ||
        $consultation_time === '' ||
        $symptoms === ''
    ) {
        $error = $t['fill_required'];
    } elseif (!isset($doctorDirectory[$hospital_name])) {
        $error = $language === 'ta'
            ? 'பதிவுசெய்யப்பட்ட மருத்துவமனையைத் தேர்ந்தெடுக்கவும்.'
            : ($language === 'hi'
                ? 'कृपया पंजीकृत अस्पताल चुनें।'
                : 'Please select a registered hospital.');
    } elseif (!isset($doctorDirectory[$hospital_name][$doctor_id])) {
        $error = $language === 'ta'
            ? 'தேர்ந்தெடுத்த மருத்துவமனையில் பதிவுசெய்யப்பட்ட மருத்துவரைத் தேர்ந்தெடுக்கவும்.'
            : ($language === 'hi'
                ? 'चुने गए अस्पताल में पंजीकृत डॉक्टर चुनें।'
                : 'Choose a doctor registered at the selected hospital.');
    } elseif (
        ($symptomsLength = consultationCharacterCount($symptoms)) === null ||
        $symptomsLength < 10 ||
        $symptomsLength > 2000 ||
        preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $symptoms) ||
        !preg_match('/\p{L}/u', $symptoms)
    ) {
        $error = $language === 'ta'
            ? 'அறிகுறிகளை 10-2000 எழுத்துகளுக்குள் சரியாக விவரிக்கவும்.'
            : ($language === 'hi'
                ? 'लक्षणों का 10-2000 अक्षरों में सही विवरण दें।'
                : 'Describe your symptoms in 10-2000 characters and include letters.');
    } else {
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $consultation_date);
        $dateErrors = DateTimeImmutable::getLastErrors();
        $parsedTime = DateTimeImmutable::createFromFormat('!H:i', $consultation_time);
        $timeErrors = DateTimeImmutable::getLastErrors();

        if (
            !$parsedDate ||
            $parsedDate->format('Y-m-d') !== $consultation_date ||
            ($dateErrors !== false &&
                ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) ||
            $consultation_date < date('Y-m-d')
        ) {
            $error = $t['future_date'];
        } elseif (
            !$parsedTime ||
            $parsedTime->format('H:i') !== $consultation_time ||
            ($timeErrors !== false &&
                ($timeErrors['warning_count'] > 0 || $timeErrors['error_count'] > 0))
        ) {
            $error = $language === 'ta'
                ? 'சரியான ஆலோசனை நேரத்தைத் தேர்ந்தெடுக்கவும்.'
                : ($language === 'hi'
                    ? 'कृपया सही परामर्श समय चुनें।'
                    : 'Please select a valid consultation time.');
        } elseif ($consultation_date === date('Y-m-d')) {
            $selectedDateTime = $parsedDate->setTime(
                (int)$parsedTime->format('H'),
                (int)$parsedTime->format('i')
            );
            if ($selectedDateTime <= new DateTimeImmutable('now')) {
                $error = $language === 'ta'
                    ? 'கடந்த ஆலோசனை நேரத்தைத் தேர்ந்தெடுக்க முடியாது.'
                    : ($language === 'hi'
                        ? 'बीता हुआ परामर्श समय नहीं चुन सकते।'
                        : 'The selected consultation time has already passed.');
            }
        }

        if ($error === '') {
            $doctor_name = $doctorDirectory[$hospital_name][$doctor_id];
            $sql = "INSERT INTO consultations (patient_id, patient_name, hospital_name, doctor_id, doctor_name, consultation_date, consultation_time, symptoms, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('isssssss', $patient_id, $patient_name, $hospital_name, $doctor_id, $doctor_name, $consultation_date, $consultation_time, $symptoms);
                if ($stmt->execute()) {
                    $stmt->close();
                    header('Location: online_consultation.php?booked=1');
                    exit();
                } else {
                    $error = $t['book_error'];
                    $stmt->close();
                }
            } else {
                $error = $t['db_error'] . $conn->error;
            }
        }
    }
}
if (isset($_GET['booked'])) $message = $t['success'];

$stmt = $conn->prepare('SELECT * FROM consultations WHERE patient_id = ? ORDER BY created_at DESC');
if (!$stmt) die(h($t['db_error'] . $conn->error));
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$consultations = [];
$pending_count = $accepted_count = $rejected_count = $completed_count = 0;
while ($row = $result->fetch_assoc()) {
    $consultations[] = $row;
    $s = strtolower(trim($row['status'] ?? ''));
    if ($s === 'pending') $pending_count++;
    elseif ($s === 'accepted' || $s === 'in progress') $accepted_count++;
    elseif ($s === 'rejected') $rejected_count++;
    elseif ($s === 'completed') $completed_count++;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="<?= h($language) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($t['page_title']) ?></title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f3f8fc;color:#355e79}.sidebar{position:fixed;left:0;top:0;width:255px;height:100vh;overflow-y:auto;background:linear-gradient(180deg,#0d3b59,#092d46);color:white;padding:25px 15px}.logo{display:flex;align-items:center;gap:12px;padding:5px 12px 30px}.logo-icon{width:48px;height:48px;border-radius:50%;background:#20aee5;display:flex;align-items:center;justify-content:center;font-size:25px}.logo h2{margin:0;font-size:21px}.logo p{margin:5px 0 0;font-size:10px;color:#b7d7e8}.nav-item{display:block;padding:14px 16px;margin:5px 0;border-radius:9px;color:#d5e7f0;text-decoration:none;font-size:14px}.nav-item:hover,.nav-item.active{background:#1484bd;color:white}.sidebar-bottom{margin-top:35px;text-align:center;color:#a9c9da;font-size:12px;line-height:1.8}.main{margin-left:255px;min-height:100vh}.topbar{min-height:74px;background:white;border-bottom:1px solid #e5edf2;display:flex;align-items:center;justify-content:space-between;padding:15px 32px}.menu{font-size:25px;color:#28718f}.patient-info{display:flex;align-items:center;gap:11px}.patient-avatar{width:43px;height:43px;border-radius:50%;background:#e4f3fa;display:flex;align-items:center;justify-content:center;font-size:22px}.patient-name{font-size:14px;font-weight:bold;color:#254961}.patient-role{font-size:11px;color:#8298a6;margin-top:3px}.content{padding:25px 32px 40px}.hero{background:linear-gradient(110deg,#e8f6ff,#d9effb);border-radius:15px;padding:28px 35px;margin-bottom:20px}.hero-small{font-size:12px;font-weight:bold;letter-spacing:1px;color:#42758e}.hero h1{margin:10px 0;font-size:30px;color:#153e59}.hero p{max-width:700px;color:#54758a;font-size:14px;line-height:1.6}.alert{padding:14px 18px;border-radius:9px;margin-bottom:20px;font-size:14px}.success{background:#ddf6e9;border:1px solid #bce9d2;color:#157247}.error{background:#ffe4e4;border:1px solid #f4c2c2;color:#a52c2c}.grid{display:grid;grid-template-columns:minmax(280px,1fr) minmax(300px,1.35fr);gap:20px;align-items:start}.card{background:white;border-radius:14px;padding:24px;box-shadow:0 4px 15px rgba(28,70,95,.07);margin-bottom:18px;min-width:0}.card-title{display:flex;align-items:center;gap:12px;margin-bottom:20px}.card-title-icon{width:45px;height:45px;border-radius:50%;background:#e4f4fb;display:flex;align-items:center;justify-content:center;font-size:22px}.card-title h2{margin:0;font-size:20px;color:#355e79}.card-title p{margin:4px 0 0;font-size:12px;color:#8196a4}label{display:block;margin-top:15px;margin-bottom:6px;font-size:13px;font-weight:bold;color:#385d72}input,select,textarea{width:100%;padding:11px 12px;border:1px solid #d6e2e8;border-radius:7px;font-size:13px;outline:none;background:#fbfdfe}input:focus,select:focus,textarea:focus{border-color:#2196d2;background:white}textarea{min-height:95px;resize:vertical}.book-button,.join-button{display:block;width:100%;margin-top:18px;padding:13px;border:none;border-radius:8px;background:linear-gradient(90deg,#168fd7,#087abd);color:white;font-size:14px;font-weight:bold;cursor:pointer;text-align:center;text-decoration:none}.book-button:hover{background:#05669e}.join-button{background:linear-gradient(90deg,#14a978,#0b9567)}.join-button:hover{background:#067c55}.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:18px}.stat{background:white;border-radius:9px;padding:15px 8px;text-align:center;box-shadow:0 4px 15px rgba(28,70,95,.05)}.stat-number{font-size:22px;font-weight:bold;color:#176f9d}.stat-label{font-size:11px;color:#78909e;margin-top:5px}.consultation{border:1px solid #e3ebef;border-radius:11px;padding:17px;margin-bottom:13px;background:white}.consultation-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}.doctor-details{display:flex;gap:11px;align-items:center;min-width:0}.doctor-avatar{width:44px;height:44px;flex-shrink:0;border-radius:50%;background:#e5f4fc;display:flex;align-items:center;justify-content:center;font-size:21px}.doctor-name{font-weight:bold;color:#20465f;font-size:14px;overflow-wrap:anywhere}.doctor-hospital{color:#8196a4;font-size:11px;margin-top:4px}.status{display:inline-block;padding:6px 10px;border-radius:20px;font-size:11px;font-weight:bold;white-space:nowrap}.status-pending{color:#bd7600;background:#fff0d2}.status-accepted{color:#147a50;background:#ddf6e9}.status-rejected{color:#c23636;background:#ffe1e1}.status-progress{color:#1d77c5;background:#deefff}.status-completed{color:#647783;background:#edf1f3}.details{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:15px;padding-top:14px;border-top:1px solid #edf2f5}.detail{font-size:12px;color:#617b8b;line-height:1.7;overflow-wrap:anywhere}.detail strong{color:#385b70}.symptoms-box,.doctor-response{background:#f5f9fc;border-radius:7px;padding:11px;margin-top:12px;font-size:12px;color:#607b8c;line-height:1.6;overflow-wrap:anywhere}.doctor-response{background:#eef8ff}.wait-button{width:100%;border:none;border-radius:8px;padding:12px;margin-top:14px;background:#eef3f6;color:#718895;font-size:12px;font-weight:bold;cursor:not-allowed;line-height:1.7}.notification-card{border-radius:10px;padding:15px;margin-bottom:15px;border-left:4px solid}.notification-card.accepted{background:#edfaf4;border-color:#16a875}.notification-card.rejected{background:#fff0f0;border-color:#e64d4d}.notification-title{font-size:14px;font-weight:bold;margin-bottom:5px}.notification-text{font-size:12px;color:#647f8e;line-height:1.7}.empty{text-align:center;padding:35px 15px;color:#8298a5}.empty-icon{font-size:42px;margin-bottom:10px}.footer{display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;color:#8096a4;font-size:11px;padding:20px 5px}@media(max-width:1050px){.grid{grid-template-columns:1fr}}@media(max-width:800px){.sidebar{position:relative;width:100%;height:auto}.main{margin-left:0}.sidebar-bottom{margin-top:20px}.content{padding:15px}.topbar{padding:15px}}@media(max-width:480px){.details,.stats{grid-template-columns:1fr}.hero{padding:22px}.hero h1{font-size:24px}.card{padding:17px}.consultation-top{flex-wrap:wrap}}
</style>
</head>
<body>
<div class="sidebar">
 <div class="logo"><div class="logo-icon">♥</div><div><h2>CareConnect</h2><p><?= h($t['tagline']) ?></p></div></div>
 <a href="patient.php" class="nav-item">⌂ &nbsp; <?= h($t['dashboard']) ?></a>
 <a href="upload_report.php" class="nav-item">📄 &nbsp; <?= h($t['reports']) ?></a>
 <a href="appointment.php" class="nav-item">📅 &nbsp; <?= h($t['appointments']) ?></a>
 <a href="online_consultation.php" class="nav-item active">📹 &nbsp; <?= h($t['online']) ?></a>
 <a href="prescriptions.php" class="nav-item">💊 &nbsp; <?= h($t['prescriptions']) ?></a>
 <a href="logout.php" class="nav-item">↪ &nbsp; <?= h($t['logout']) ?></a>
 <div class="sidebar-bottom"><div style="font-size:42px">🩺</div><div><?= h($t['health_matters']) ?></div><div>CareConnect</div></div>
</div>
<div class="main">
 <div class="topbar"><div class="menu">☰</div><div class="patient-info"><div class="patient-avatar">👤</div><div><div class="patient-name"><?= h($patient_name) ?></div><div class="patient-role"><?= h($t['patient_role']) ?></div></div></div></div>
 <div class="content">
  <div class="hero"><div class="hero-small"><?= h($t['brand']) ?></div><h1><?= h($t['hero_title']) ?></h1><p><?= h($t['hero_desc']) ?></p></div>
  <?php if ($message !== ''): ?><div class="alert success">✓ <?= h($message) ?></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="alert error">✕ <?= h($error) ?></div><?php endif; ?>
  <div class="grid">
   <div class="card">
    <div class="card-title"><div class="card-title-icon">📅</div><div><h2><?= h($t['book_title']) ?></h2><p><?= h($t['book_sub']) ?></p></div></div>
    <form method="POST" action="online_consultation.php">
     <label><?= h($t['patient_name']) ?></label><input type="text" value="<?= h($patient_name) ?>" readonly>
     <label><?= h($t['patient_id']) ?></label><input type="text" value="<?= h($patient_id) ?>" readonly>
     <label><?= h($t['hospital']) ?> *</label>
     <select name="hospital_name" id="consultation-hospital" required>
      <option value=""><?= h($t['hospital_placeholder']) ?></option>
      <?php foreach ($hospitalNames as $hospitalOption): ?>
       <option value="<?= h($hospitalOption) ?>" <?= (isset($_POST['hospital_name']) && is_string($_POST['hospital_name']) && $_POST['hospital_name'] === $hospitalOption) ? 'selected' : '' ?>><?= h($hospitalOption) ?></option>
      <?php endforeach; ?>
     </select>
     <label><?= h($t['doctor_name']) ?> *</label>
     <select name="doctor_id" id="consultation-doctor" required>
      <option value=""><?= h($t['doctor_name_placeholder']) ?></option>
      <?php foreach ($doctorDirectory as $hospitalOption => $doctorsAtHospital): ?>
       <?php foreach ($doctorsAtHospital as $doctorOptionId => $doctorOptionName): ?>
        <option value="<?= h($doctorOptionId) ?>" data-hospital="<?= h($hospitalOption) ?>" <?= (isset($_POST['doctor_id']) && is_string($_POST['doctor_id']) && $_POST['doctor_id'] === $doctorOptionId && isset($_POST['hospital_name']) && $_POST['hospital_name'] === $hospitalOption) ? 'selected' : '' ?>><?= h($doctorOptionName) ?></option>
       <?php endforeach; ?>
      <?php endforeach; ?>
     </select>
     <label><?= h($t['date']) ?> *</label><input type="date" name="consultation_date" min="<?= h(date('Y-m-d')) ?>" value="<?= (isset($_POST['consultation_date']) && is_string($_POST['consultation_date'])) ? h($_POST['consultation_date']) : '' ?>" required>
     <label><?= h($t['time']) ?> *</label><input type="time" name="consultation_time" value="<?= (isset($_POST['consultation_time']) && is_string($_POST['consultation_time'])) ? h($_POST['consultation_time']) : '' ?>" required>
     <label><?= h($t['symptoms']) ?> *</label><textarea name="symptoms" maxlength="2000" placeholder="<?= h($t['symptoms_placeholder']) ?>" required><?= (isset($_POST['symptoms']) && is_string($_POST['symptoms'])) ? h($_POST['symptoms']) : '' ?></textarea>
     <button type="submit" name="book_consultation" class="book-button"><?= h($t['request']) ?></button>
    </form>
    <script>
    (() => {
      const hospital = document.getElementById('consultation-hospital');
      const doctor = document.getElementById('consultation-doctor');
      const options = Array.from(doctor.options).slice(1);
      const updateDoctors = () => {
        let selectedDoctorAvailable = false;
        options.forEach((option) => {
          option.hidden = option.dataset.hospital !== hospital.value;
          option.disabled = option.hidden;
          if (!option.hidden && option.selected) selectedDoctorAvailable = true;
        });
        if (!selectedDoctorAvailable) doctor.value = '';
      };
      hospital.addEventListener('change', updateDoctors);
      updateDoctors();
    })();
    </script>
   </div>
   <div>
    <div class="stats">
     <div class="stat"><div class="stat-number"><?= count($consultations) ?></div><div class="stat-label"><?= h($t['total']) ?></div></div>
     <div class="stat"><div class="stat-number"><?= $accepted_count ?></div><div class="stat-label"><?= h($t['accepted_progress']) ?></div></div>
     <div class="stat"><div class="stat-number"><?= $pending_count ?></div><div class="stat-label"><?= h($t['pending']) ?></div></div>
    </div>
    <?php foreach ($consultations as $notification): $ns = strtolower(trim($notification['status'] ?? '')); ?>
      <?php if ($ns === 'accepted' || $ns === 'rejected'): ?>
       <div class="notification-card <?= h($ns) ?>"><div class="notification-title"><?php if ($ns === 'accepted'): ?>✓ <?= h($t['notification_accepted']) ?><?php else: ?>✕ <?= h($t['notification_rejected']) ?><?php endif; ?></div><div class="notification-text">Dr. <?= h($notification['doctor_name'] ?? $t['doctor']) ?> <?= h($t['has']) ?> <strong><?= h(status_text($ns, $t)) ?></strong> <?= h($t['your_request']) ?><br>📅 <?= h($notification['consultation_date'] ?? '') ?> &nbsp; 🕐 <?= h($notification['consultation_time'] ?? '') ?></div></div>
      <?php endif; ?>
    <?php endforeach; ?>
    <div class="card">
     <div class="card-title"><div class="card-title-icon">📋</div><div><h2><?= h($t['my_consultations']) ?></h2><p><?= h($t['track']) ?></p></div></div>
     <?php if (count($consultations) > 0): ?>
      <?php foreach ($consultations as $row):
       $status = strtolower(trim($row['status'] ?? ''));
       $status_class = 'status-pending';
       if ($status === 'accepted') $status_class = 'status-accepted';
       elseif ($status === 'rejected') $status_class = 'status-rejected';
       elseif ($status === 'in progress') $status_class = 'status-progress';
       elseif ($status === 'completed') $status_class = 'status-completed';
       $meet_link = trim($row['meet_link'] ?? '');
       $meet_url_scheme = '';
       $meet_url_host = '';
       if (filter_var($meet_link, FILTER_VALIDATE_URL)) {
          $meet_url_scheme = strtolower(parse_url($meet_link, PHP_URL_SCHEME) ?? '');
          $meet_url_host = strtolower(parse_url($meet_link, PHP_URL_HOST) ?? '');
       }
       $is_valid_meet_link = $meet_url_scheme === 'https' && ($meet_url_host === 'meet.google.com' || substr($meet_url_host, -16) === '.meet.google.com');
       $can_join = in_array($status, ['accepted','in progress'], true) && $is_valid_meet_link;
      ?>
       <div class="consultation">
        <div class="consultation-top"><div class="doctor-details"><div class="doctor-avatar">👨‍⚕️</div><div><div class="doctor-name">Dr. <?= h($row['doctor_name'] ?: $t['doctor']) ?></div><div class="doctor-hospital"><?= h($row['hospital_name'] ?? '') ?></div></div></div><span class="status <?= h($status_class) ?>"><?= h(status_text($row['status'] ?? 'Pending', $t)) ?></span></div>
        <div class="details"><div class="detail"><strong>📅 <?= h($t['date_label']) ?></strong><br><?= h($row['consultation_date'] ?? '') ?></div><div class="detail"><strong>🕐 <?= h($t['time_label']) ?></strong><br><?= h($row['consultation_time'] ?? '') ?></div></div>
        <div class="symptoms-box"><strong><?= h($t['symptoms_label']) ?></strong> <?= nl2br(h($row['symptoms'] ?? '')) ?></div>
        <?php if (!empty($row['doctor_response'])): ?><div class="doctor-response"><strong><?= h($t['doctor_response']) ?></strong><br><?= nl2br(h($row['doctor_response'])) ?></div><?php endif; ?>
        <?php if ($can_join): ?><a class="join-button" href="<?= h($meet_link) ?>" target="_blank" rel="noopener noreferrer"><?= h($t['join']) ?></a>
        <?php elseif ($status === 'accepted' || $status === 'in progress'): ?><button type="button" class="wait-button" disabled><?= h($t['accepted_wait']) ?></button>
        <?php elseif ($status === 'pending'): ?><button type="button" class="wait-button" disabled><?= h($t['pending_wait']) ?></button>
        <?php elseif ($status === 'rejected'): ?><button type="button" class="wait-button" disabled><?= h($t['rejected_wait']) ?></button>
        <?php elseif ($status === 'completed'): ?><button type="button" class="wait-button" disabled><?= h($t['completed_wait']) ?></button>
        <?php else: ?><button type="button" class="wait-button" disabled><?= h($t['status_unknown']) ?> <?= h($row['status'] ?? 'Unknown') ?></button><?php endif; ?>
       </div>
      <?php endforeach; ?>
     <?php else: ?><div class="empty"><div class="empty-icon">📹</div><h3><?= h($t['empty_title']) ?></h3><p><?= h($t['empty_desc']) ?></p></div><?php endif; ?>
    </div>
   </div>
  </div>
  <div class="footer"><div><?= h($t['footer']) ?></div><div><?= h($t['tagline']) ?></div></div>
 </div>
</div>
</body>
</html>
