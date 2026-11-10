
<?php
session_start();

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$patient_id = (int) $_SESSION['patient_id'];
$patient_name = $_SESSION['patient_name'] ?? '';
$message = "";
$message_type = "success";

$language = $_SESSION['language'] ?? 'en';

$translations = [
    'en' => [
        'title' => 'Medical Reports',
        'tagline' => 'Your Health. Your Records. Your Control.',
        'upload' => 'Upload New Medical Report',
        'patient_name' => 'Patient Name',
        'patient_id' => 'Patient ID',
        'hospital' => 'Hospital Name (Report From)',
        'enter_hospital' => 'Select hospital',
        'type' => 'Report Type',
        'select_type' => 'Select report type',
        'date' => 'Report Date',
        'choose_file' => 'Choose Medical Report',
        'formats' => 'Accepted formats: PDF, JPG, JPEG, PNG. Maximum size: 5 MB.',
        'description' => 'Description (Optional)',
        'enter_description' => 'Enter report details',
        'upload_button' => 'Upload Report',
        'my_reports' => 'My Medical Reports',
        'privacy' => 'All uploaded reports are private by default. Share only the reports you choose.',
        'report_id' => 'Report ID',
        'sharing_status' => 'Sharing Status',
        'shared' => 'Shared',
        'private' => 'Private',
        'view' => 'View Report',
        'shared_doctor' => 'Shared Doctor',
        'shared_hospital' => 'Shared Hospital',
        'share_this' => 'Share This Report',
        'doctor' => 'Doctor Name',
        'enter_doctor' => 'Enter doctor name',
        'share' => 'Share This Report',
        'delete' => 'Delete Report',
        'none' => 'No medical reports uploaded yet.',
        'back' => 'Back to Patient Dashboard',
        'success_upload' => 'Report uploaded successfully! Your report is Private.',
        'required' => 'Please fill all required fields.',
        'invalid_hospital' => 'Please select a hospital from the registered hospital list.',
        'invalid_description' => 'Description can contain letters and spaces only.',
        'invalid_type' => 'Please select a valid report type.',
        'invalid_date' => 'Please enter a valid report date that is not in the future.',
        'invalid_file' => 'Please select a valid report file.',
        'allowed' => 'Only PDF, JPG, JPEG and PNG files are allowed.',
        'size' => 'Maximum file size is 5 MB.',
        'upload_failed' => 'File upload failed.',
        'share_required' => 'Please enter doctor name and hospital name.',
        'share_success' => 'Selected report sharing details saved successfully.',
        'not_found' => 'Report not found or no changes made.',
        'deleted' => 'Selected report deleted successfully.',
        'delete_failed' => 'Unable to delete report.',
        'invalid_report' => 'Invalid report.',
        'confirm_delete' => 'Are you sure you want to permanently delete this report?',
        'blood' => 'Blood Test',
        'xray' => 'X-Ray',
        'ct' => 'CT Scan',
        'mri' => 'MRI Scan',
        'ultrasound' => 'Ultrasound',
        'prescription' => 'Prescription',
        'other' => 'Other'
    ],

    'ta' => [
        'title' => 'மருத்துவ அறிக்கைகள்',
        'tagline' => 'உங்கள் உடல்நலம். உங்கள் பதிவுகள். உங்கள் கட்டுப்பாடு.',
        'upload' => 'புதிய மருத்துவ அறிக்கையைப் பதிவேற்றவும்',
        'patient_name' => 'நோயாளியின் பெயர்',
        'patient_id' => 'நோயாளர் அடையாள எண்',
        'hospital' => 'மருத்துவமனை பெயர்',
        'enter_hospital' => 'மருத்துவமனையைத் தேர்ந்தெடுக்கவும்',
        'type' => 'அறிக்கை வகை',
        'select_type' => 'அறிக்கை வகையைத் தேர்ந்தெடுக்கவும்',
        'date' => 'அறிக்கை தேதி',
        'choose_file' => 'மருத்துவ அறிக்கையைத் தேர்ந்தெடுக்கவும்',
        'formats' => 'அனுமதிக்கப்படும் கோப்புகள்: PDF, JPG, JPEG, PNG. அதிகபட்ச அளவு: 5 MB.',
        'description' => 'விளக்கம் (விருப்பத்தேர்வு)',
        'enter_description' => 'அறிக்கை விவரங்களை உள்ளிடவும்',
        'upload_button' => 'அறிக்கையைப் பதிவேற்றவும்',
        'my_reports' => 'எனது மருத்துவ அறிக்கைகள்',
        'privacy' => 'பதிவேற்றப்பட்ட அறிக்கைகள் இயல்பாகத் தனிப்பட்டவையாக இருக்கும். நீங்கள் தேர்ந்தெடுத்த அறிக்கைகளை மட்டுமே பகிரவும்.',
        'report_id' => 'அறிக்கை எண்',
        'sharing_status' => 'பகிர்வு நிலை',
        'shared' => 'பகிரப்பட்டது',
        'private' => 'தனிப்பட்டது',
        'view' => 'அறிக்கையைப் பார்க்கவும்',
        'shared_doctor' => 'பகிரப்பட்ட மருத்துவர்',
        'shared_hospital' => 'பகிரப்பட்ட மருத்துவமனை',
        'share_this' => 'இந்த அறிக்கையைப் பகிரவும்',
        'doctor' => 'மருத்துவர் பெயர்',
        'enter_doctor' => 'மருத்துவர் பெயரை உள்ளிடவும்',
        'share' => 'அறிக்கையைப் பகிரவும்',
        'delete' => 'அறிக்கையை நீக்கவும்',
        'none' => 'இதுவரை மருத்துவ அறிக்கைகள் பதிவேற்றப்படவில்லை.',
        'back' => 'நோயாளர் முகப்புப் பக்கத்திற்குத் திரும்பவும்',
        'success_upload' => 'அறிக்கை வெற்றிகரமாகப் பதிவேற்றப்பட்டது! இது தனிப்பட்டதாக உள்ளது.',
        'required' => 'தேவையான அனைத்து விவரங்களையும் நிரப்பவும்.',
        'invalid_hospital' => 'பதிவுசெய்யப்பட்ட மருத்துவமனைகளின் பட்டியலில் இருந்து தேர்ந்தெடுக்கவும்.',
        'invalid_description' => 'விளக்கத்தில் எழுத்துகள் மற்றும் இடைவெளிகள் மட்டுமே இருக்க வேண்டும்.',
        'invalid_type' => 'சரியான அறிக்கை வகையைத் தேர்ந்தெடுக்கவும்.',
        'invalid_date' => 'எதிர்காலத்தில் இல்லாத சரியான அறிக்கை தேதியை உள்ளிடவும்.',
        'invalid_file' => 'சரியான அறிக்கைக் கோப்பைத் தேர்ந்தெடுக்கவும்.',
        'allowed' => 'PDF, JPG, JPEG மற்றும் PNG கோப்புகள் மட்டுமே அனுமதிக்கப்படும்.',
        'size' => 'அதிகபட்ச கோப்பு அளவு 5 MB.',
        'upload_failed' => 'கோப்பைப் பதிவேற்ற முடியவில்லை.',
        'share_required' => 'மருத்துவர் பெயர் மற்றும் மருத்துவமனை பெயரை உள்ளிடவும்.',
        'share_success' => 'அறிக்கை பகிர்வு விவரங்கள் சேமிக்கப்பட்டன.',
        'not_found' => 'அறிக்கை கிடைக்கவில்லை அல்லது மாற்றம் செய்யப்படவில்லை.',
        'deleted' => 'அறிக்கை வெற்றிகரமாக நீக்கப்பட்டது.',
        'delete_failed' => 'அறிக்கையை நீக்க முடியவில்லை.',
        'invalid_report' => 'தவறான அறிக்கை.',
        'confirm_delete' => 'இந்த அறிக்கையை நிரந்தரமாக நீக்க விரும்புகிறீர்களா?',
        'blood' => 'இரத்தப் பரிசோதனை',
        'xray' => 'எக்ஸ்-ரே',
        'ct' => 'CT ஸ்கேன்',
        'mri' => 'MRI ஸ்கேன்',
        'ultrasound' => 'அல்ட்ராசவுண்ட்',
        'prescription' => 'மருந்துச் சீட்டு',
        'other' => 'மற்றவை'
    ],

    'hi' => [
        'title' => 'मेडिकल रिपोर्ट',
        'tagline' => 'आपका स्वास्थ्य। आपके रिकॉर्ड। आपका नियंत्रण।',
        'upload' => 'नई मेडिकल रिपोर्ट अपलोड करें',
        'patient_name' => 'मरीज़ का नाम',
        'patient_id' => 'मरीज़ आईडी',
        'hospital' => 'अस्पताल का नाम',
        'enter_hospital' => 'अस्पताल चुनें',
        'type' => 'रिपोर्ट का प्रकार',
        'select_type' => 'रिपोर्ट का प्रकार चुनें',
        'date' => 'रिपोर्ट की तारीख',
        'choose_file' => 'मेडिकल रिपोर्ट चुनें',
        'formats' => 'स्वीकृत फ़ाइलें: PDF, JPG, JPEG, PNG. अधिकतम आकार: 5 MB.',
        'description' => 'विवरण (वैकल्पिक)',
        'enter_description' => 'रिपोर्ट का विवरण दर्ज करें',
        'upload_button' => 'रिपोर्ट अपलोड करें',
        'my_reports' => 'मेरी मेडिकल रिपोर्ट',
        'privacy' => 'अपलोड की गई रिपोर्ट डिफ़ॉल्ट रूप से निजी हैं। केवल अपनी चुनी हुई रिपोर्ट साझा करें।',
        'report_id' => 'रिपोर्ट आईडी',
        'sharing_status' => 'साझा करने की स्थिति',
        'shared' => 'साझा की गई',
        'private' => 'निजी',
        'view' => 'रिपोर्ट देखें',
        'shared_doctor' => 'साझा किए गए डॉक्टर',
        'shared_hospital' => 'साझा किया गया अस्पताल',
        'share_this' => 'यह रिपोर्ट साझा करें',
        'doctor' => 'डॉक्टर का नाम',
        'enter_doctor' => 'डॉक्टर का नाम दर्ज करें',
        'share' => 'रिपोर्ट साझा करें',
        'delete' => 'रिपोर्ट हटाएँ',
        'none' => 'अभी तक कोई मेडिकल रिपोर्ट अपलोड नहीं की गई है।',
        'back' => 'मरीज़ डैशबोर्ड पर वापस जाएँ',
        'success_upload' => 'रिपोर्ट सफलतापूर्वक अपलोड हुई! आपकी रिपोर्ट निजी है।',
        'required' => 'कृपया सभी आवश्यक फ़ील्ड भरें।',
        'invalid_hospital' => 'पंजीकृत अस्पतालों की सूची से अस्पताल चुनें।',
        'invalid_description' => 'विवरण में केवल अक्षर और स्पेस होने चाहिए।',
        'invalid_type' => 'कृपया सही रिपोर्ट प्रकार चुनें।',
        'invalid_date' => 'भविष्य में न होने वाली सही रिपोर्ट तारीख दर्ज करें।',
        'invalid_file' => 'कृपया सही रिपोर्ट फ़ाइल चुनें।',
        'allowed' => 'केवल PDF, JPG, JPEG और PNG फ़ाइलें स्वीकार हैं।',
        'size' => 'अधिकतम फ़ाइल आकार 5 MB है।',
        'upload_failed' => 'फ़ाइल अपलोड नहीं हुई।',
        'share_required' => 'डॉक्टर और अस्पताल का नाम दर्ज करें।',
        'share_success' => 'रिपोर्ट साझा करने की जानकारी सेव हो गई।',
        'not_found' => 'रिपोर्ट नहीं मिली या कोई बदलाव नहीं हुआ।',
        'deleted' => 'रिपोर्ट सफलतापूर्वक हटा दी गई।',
        'delete_failed' => 'रिपोर्ट हटाई नहीं जा सकी।',
        'invalid_report' => 'अमान्य रिपोर्ट।',
        'confirm_delete' => 'क्या आप इस रिपोर्ट को स्थायी रूप से हटाना चाहते हैं?',
        'blood' => 'ब्लड टेस्ट',
        'xray' => 'एक्स-रे',
        'ct' => 'सीटी स्कैन',
        'mri' => 'एमआरआई स्कैन',
        'ultrasound' => 'अल्ट्रासाउंड',
        'prescription' => 'प्रिस्क्रिप्शन',
        'other' => 'अन्य'
    ]
];

if (!isset($translations[$language])) {
    $language = 'en';
}

$t = $translations[$language];

$hospital_names = [];
$hospital_result = $conn->query(
    "SELECT DISTINCT TRIM(hospital) AS hospital_name
     FROM doctors
     WHERE TRIM(COALESCE(hospital, '')) <> ''
     ORDER BY hospital_name"
);

if (!$hospital_result) {
    http_response_code(500);
    exit('Unable to load hospital list: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}

while ($hospital = $hospital_result->fetch_assoc()) {
    $hospital_names[] = $hospital['hospital_name'];
}
$hospital_result->free();

/* =========================
   UPLOAD NEW REPORT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST['action'] ?? '') === 'upload'
) {
    $hospital_name = is_string($_POST['hospital_name'] ?? null)
        ? trim($_POST['hospital_name'])
        : '';
    $report_type = is_string($_POST['report_type'] ?? null)
        ? trim($_POST['report_type'])
        : '';
    $report_date = is_string($_POST['report_date'] ?? null)
        ? trim($_POST['report_date'])
        : '';
    $description = is_string($_POST['description'] ?? null)
        ? trim($_POST['description'])
        : '';

    if (
        $hospital_name === '' ||
        $report_type === '' ||
        $report_date === ''
    ) {
        $message = $t['required'];
        $message_type = 'error';

    } elseif (!in_array($hospital_name, $hospital_names, true)) {
        $message = $t['invalid_hospital'];
        $message_type = 'error';

    } elseif (
        $description !== '' &&
        !preg_match('/^\p{L}+(?:[ \t]+\p{L}+)*$/u', $description)
    ) {
        $message = $t['invalid_description'];
        $message_type = 'error';

    } elseif (
        !in_array($report_type, [
            'Blood Test',
            'X-Ray',
            'CT Scan',
            'MRI Scan',
            'Ultrasound',
            'Prescription',
            'Other'
        ], true)
    ) {
        $message = $t['invalid_type'];
        $message_type = 'error';

    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $report_date) ||
        ($parsed_report_date = DateTimeImmutable::createFromFormat('!Y-m-d', $report_date)) === false ||
        $parsed_report_date->format('Y-m-d') !== $report_date ||
        $report_date > date('Y-m-d')
    ) {
        $message = $t['invalid_date'];
        $message_type = 'error';

    } elseif (
        !isset($_FILES['report_file']) ||
        !is_array($_FILES['report_file']) ||
        !isset($_FILES['report_file']['error']) ||
        $_FILES['report_file']['error'] !== UPLOAD_ERR_OK
    ) {
        $message = $t['invalid_file'];
        $message_type = 'error';

    } else {
        $file = $_FILES['report_file'];
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowed, true)) {
            $message = $t['allowed'];
            $message_type = 'error';

        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $message = $t['size'];
            $message_type = 'error';

        } else {
            $upload_dir = __DIR__ . "/uploads/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_filename = bin2hex(random_bytes(16)) . "." . $extension;
            $destination = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $file_path = "uploads/" . $new_filename;

                $sql = "INSERT INTO patient_reports
                    (
                        patient_id,
                        patient_name,
                        hospital_name,
                        report_type,
                        report_date,
                        file_path,
                        description,
                        shared_doctor_name,
                        shared_hospital,
                        share_status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, NULL, NULL, 'Private')";

                $stmt = $conn->prepare($sql);

                $stmt->bind_param(
                    "issssss",
                    $patient_id,
                    $patient_name,
                    $hospital_name,
                    $report_type,
                    $report_date,
                    $file_path,
                    $description
                );

                if ($stmt->execute()) {
                    $message = $t['success_upload'];
                    $message_type = 'success';
                } else {
                    if (file_exists($destination)) {
                        unlink($destination);
                    }

                    $message = $t['upload_failed'];
                    $message_type = 'error';
                }

                $stmt->close();

            } else {
                $message = $t['upload_failed'];
                $message_type = 'error';
            }
        }
    }
}


/* =========================
   SHARE SELECTED REPORT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST['action'] ?? '') === 'share'
) {
    $report_id = (int) ($_POST['report_id'] ?? 0);
    $doctor_name = trim($_POST['doctor_name'] ?? '');
    $shared_hospital = trim($_POST['shared_hospital'] ?? '');

    if (
        $report_id <= 0 ||
        $doctor_name === '' ||
        $shared_hospital === ''
    ) {
        $message = $t['share_required'];
        $message_type = 'error';

    } else {
        $sql = "UPDATE patient_reports
                SET shared_doctor_name = ?,
                    shared_hospital = ?,
                    share_status = 'Shared'
                WHERE report_id = ?
                AND patient_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssii",
            $doctor_name,
            $shared_hospital,
            $report_id,
            $patient_id
        );

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = $t['share_success'];
            $message_type = 'success';
        } else {
            $message = $t['not_found'];
            $message_type = 'error';
        }

        $stmt->close();
    }
}


/* =========================
   DELETE SELECTED REPORT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST['action'] ?? '') === 'delete'
) {
    $report_id = (int) ($_POST['report_id'] ?? 0);

    if ($report_id > 0) {
        // Confirm the report belongs to the logged-in patient.
        $check = $conn->prepare(
            "SELECT file_path
             FROM patient_reports
             WHERE report_id = ?
             AND patient_id = ?"
        );

        $check->bind_param("ii", $report_id, $patient_id);
        $check->execute();

        $report = $check->get_result()->fetch_assoc();
        $check->close();

        if ($report) {
            $delete = $conn->prepare(
                "DELETE FROM patient_reports
                 WHERE report_id = ?
                 AND patient_id = ?"
            );

            $delete->bind_param("ii", $report_id, $patient_id);

            if (
                $delete->execute() &&
                $delete->affected_rows > 0
            ) {
                $filename = basename($report['file_path']);
                $file_to_delete = __DIR__ . "/uploads/" . $filename;

                if (is_file($file_to_delete)) {
                    unlink($file_to_delete);
                }

                $message = $t['deleted'];
                $message_type = 'success';

            } else {
                $message = $t['delete_failed'];
                $message_type = 'error';
            }

            $delete->close();

        } else {
            $message = $t['not_found'];
            $message_type = 'error';
        }

    } else {
        $message = $t['invalid_report'];
        $message_type = 'error';
    }
}


/* =========================
   FETCH PATIENT REPORTS
========================= */

$sql = "SELECT *
        FROM patient_reports
        WHERE patient_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();

$reports = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>CareConnect - <?php echo htmlspecialchars($t['title']); ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f7fb;
            margin: 0;
            padding: 0;
            color: #263746;
        }

        header {
            background-color: #176b87;
            color: white;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            margin-bottom: 8px;
        }

        header p {
            margin-bottom: 0;
        }

        .container {
            width: 85%;
            max-width: 850px;
            margin: 30px auto;
        }

        .card {
            background-color: white;
            padding: 25px;
            margin-bottom: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        h2, h3 {
            color: #176b87;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 7px;
            font-weight: bold;
            color: #333;
        }

        input, select, textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #176b87;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
            font-size: 14px;
        }

        button:hover {
            opacity: 0.9;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .delete-btn:hover {
            background-color: #b02a37;
        }

        .message {
            padding: 13px;
            border-radius: 6px;
            margin-bottom: 20px;
            overflow-wrap: anywhere;
        }

        .success {
            background-color: #e7f5ec;
            color: #176b45;
        }

        .error {
            background-color: #fde8e8;
            color: #a12626;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 13px;
            background-color: #fff3cd;
            color: #856404;
        }

        .shared {
            background-color: #d4edda;
            color: #155724;
        }

        .share-box {
            background-color: #f5f9fc;
            padding: 18px;
            border-radius: 8px;
            margin-top: 18px;
        }

        .report-card {
            border: 1px solid #e1e8ed;
            border-radius: 10px;
            padding: 20px;
            margin-top: 18px;
            overflow-wrap: anywhere;
        }

        .report-card p {
            line-height: 1.6;
        }

        .view-link {
            color: #176b87;
            font-weight: bold;
            text-decoration: none;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        .back {
            display: inline-block;
            margin-top: 15px;
            margin-bottom: 25px;
            color: #176b87;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

        .note {
            color: #777;
            font-size: 13px;
            line-height: 1.6;
        }

        hr {
            border: 0;
            border-top: 1px solid #e5e5e5;
            margin: 25px 0;
        }

        @media (max-width: 600px) {
            .container {
                width: 92%;
            }

            .card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>CareConnect</h1>
    <p><?php echo htmlspecialchars($t['tagline']); ?></p>
</header>

<div class="container">

    <?php if ($message !== ''): ?>
        <div class="message <?php echo $message_type === 'error' ? 'error' : 'success'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>


    <!-- UPLOAD SECTION -->

    <div class="card">

        <h2><?php echo htmlspecialchars($t['upload']); ?></h2>

        <form method="POST" enctype="multipart/form-data">

            <input type="hidden" name="action" value="upload">

            <label><?php echo htmlspecialchars($t['patient_name']); ?></label>

            <input
                type="text"
                value="<?php echo htmlspecialchars($patient_name); ?>"
                readonly
            >

            <label><?php echo htmlspecialchars($t['patient_id']); ?></label>

            <input
                type="text"
                value="<?php echo $patient_id; ?>"
                readonly
            >

            <label><?php echo htmlspecialchars($t['hospital']); ?></label>

            <select name="hospital_name" required>
                <option value=""><?php echo htmlspecialchars($t['enter_hospital']); ?></option>
                <?php foreach ($hospital_names as $hospital_option): ?>
                    <option value="<?php echo htmlspecialchars($hospital_option, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($hospital_option, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label><?php echo htmlspecialchars($t['type']); ?></label>

            <select name="report_type" required>
                <option value="">
                    <?php echo htmlspecialchars($t['select_type']); ?>
                </option>

                <option value="Blood Test"><?php echo htmlspecialchars($t['blood']); ?></option>
                <option value="X-Ray"><?php echo htmlspecialchars($t['xray']); ?></option>
                <option value="CT Scan"><?php echo htmlspecialchars($t['ct']); ?></option>
                <option value="MRI Scan"><?php echo htmlspecialchars($t['mri']); ?></option>
                <option value="Ultrasound"><?php echo htmlspecialchars($t['ultrasound']); ?></option>
                <option value="Prescription"><?php echo htmlspecialchars($t['prescription']); ?></option>
                <option value="Other"><?php echo htmlspecialchars($t['other']); ?></option>
            </select>

            <label><?php echo htmlspecialchars($t['date']); ?></label>

            <input
                type="date"
                name="report_date"
                max="<?php echo date('Y-m-d'); ?>"
                required
            >

            <label><?php echo htmlspecialchars($t['choose_file']); ?></label>

            <input
                type="file"
                name="report_file"
                accept=".pdf,.jpg,.jpeg,.png"
                required
            >

            <p class="note">
                <?php echo htmlspecialchars($t['formats']); ?>
            </p>

            <label><?php echo htmlspecialchars($t['description']); ?></label>

            <textarea
                name="description"
                rows="3"
                placeholder="<?php echo htmlspecialchars($t['enter_description']); ?>"
            ></textarea>

            <button type="submit">
                <?php echo htmlspecialchars($t['upload_button']); ?>
            </button>

        </form>
    </div>


    <!-- MY REPORTS SECTION -->

    <div class="card">

        <h2><?php echo htmlspecialchars($t['my_reports']); ?></h2>

        <p class="note">
            <?php echo htmlspecialchars($t['privacy']); ?>
        </p>

        <?php if ($reports->num_rows > 0): ?>

            <?php while ($report = $reports->fetch_assoc()): ?>

                <div class="report-card">

                    <h3>
                        <?php
                        echo htmlspecialchars(
                            $t[strtolower(str_replace(' ', '_', $report['report_type']))]
                            ?? $report['report_type']
                        );
                        ?>
                    </h3>

                    <p>
                        <b><?php echo htmlspecialchars($t['report_id']); ?>:</b>
                        <?php echo (int) $report['report_id']; ?>
                    </p>

                    <p>
                        <b><?php echo htmlspecialchars($t['hospital']); ?>:</b>
                        <?php echo htmlspecialchars($report['hospital_name']); ?>
                    </p>

                    <p>
                        <b><?php echo htmlspecialchars($t['date']); ?>:</b>
                        <?php echo htmlspecialchars($report['report_date']); ?>
                    </p>

                    <p>
                        <b><?php echo htmlspecialchars($t['description']); ?>:</b>
                        <?php echo htmlspecialchars($report['description'] ?? ''); ?>
                    </p>

                    <p>
                        <b><?php echo htmlspecialchars($t['sharing_status']); ?>:</b>

                        <?php if ($report['share_status'] === 'Shared'): ?>
                            <span class="status shared">
                                <?php echo htmlspecialchars($t['shared']); ?>
                            </span>
                        <?php else: ?>
                            <span class="status">
                                <?php echo htmlspecialchars($t['private']); ?>
                            </span>
                        <?php endif; ?>
                    </p>

                    <p>
                        <a
                            class="view-link"
                            href="<?php echo htmlspecialchars($report['file_path']); ?>"
                            target="_blank"
                            rel="noopener"
                        >
                            <?php echo htmlspecialchars($t['view']); ?>
                        </a>
                    </p>


                    <?php if ($report['share_status'] === 'Shared'): ?>

                        <p>
                            <b><?php echo htmlspecialchars($t['shared_doctor']); ?>:</b>
                            <?php echo htmlspecialchars($report['shared_doctor_name'] ?? ''); ?>
                        </p>

                        <p>
                            <b><?php echo htmlspecialchars($t['shared_hospital']); ?>:</b>
                            <?php echo htmlspecialchars($report['shared_hospital'] ?? ''); ?>
                        </p>

                    <?php else: ?>

                        <!-- SHARE FORM -->

                        <div class="share-box">

                            <h3><?php echo htmlspecialchars($t['share_this']); ?></h3>

                            <form method="POST">

                                <input type="hidden" name="action" value="share">

                                <input
                                    type="hidden"
                                    name="report_id"
                                    value="<?php echo (int) $report['report_id']; ?>"
                                >

                                <label><?php echo htmlspecialchars($t['doctor']); ?></label>

                                <input
                                    type="text"
                                    name="doctor_name"
                                    placeholder="<?php echo htmlspecialchars($t['enter_doctor']); ?>"
                                    required
                                >

                                <label><?php echo htmlspecialchars($t['hospital']); ?></label>

                                <input
                                    type="text"
                                    name="shared_hospital"
                                    placeholder="<?php echo htmlspecialchars($t['enter_hospital']); ?>"
                                    required
                                >

                                <button type="submit">
                                    <?php echo htmlspecialchars($t['share']); ?>
                                </button>

                            </form>

                        </div>

                    <?php endif; ?>


                    <!-- DELETE FORM -->

                    <hr>

                    <form
                        method="POST"
                        onsubmit="return confirm(<?php echo htmlspecialchars(json_encode($t['confirm_delete']), ENT_QUOTES, 'UTF-8'); ?>);"
                    >

                        <input type="hidden" name="action" value="delete">

                        <input
                            type="hidden"
                            name="report_id"
                            value="<?php echo (int) $report['report_id']; ?>"
                        >

                        <button type="submit" class="delete-btn">
                            <?php echo htmlspecialchars($t['delete']); ?>
                        </button>

                    </form>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p><?php echo htmlspecialchars($t['none']); ?></p>

        <?php endif; ?>

    </div>

    <a class="back" href="patient.php">
        ← <?php echo htmlspecialchars($t['back']); ?>
    </a>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
