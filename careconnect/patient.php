
<?php
session_start();

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.html");
    exit();
}

$language = $_SESSION['language'] ?? 'en';

$allowed_languages = ['en', 'ta', 'hi'];

if (!in_array($language, $allowed_languages, true)) {
    $language = 'en';
}

$translations = [
    'en' => [
        'logout' => 'Logout',
        'welcome' => 'Welcome',
        'tagline' => 'Your Health. Your Records. Your Control.',
        'manage' => 'Manage your healthcare activities from one place.',
        'patient_id' => 'Patient ID',
        'services' => 'Patient Services',
        'appointment' => 'Book Appointment',
        'appointment_desc' => 'Schedule an appointment with a doctor for your healthcare needs.',
        'book_now' => 'Book Now',
        'health' => 'Current Health Issue',
        'health_desc' => 'Enter your current symptoms and health concerns for consultation.',
        'enter_details' => 'Enter Details',
        'report' => 'Upload Medical Report',
        'report_desc' => 'Upload a medical report or scan related to your current visit.',
        'upload' => 'Upload Report',
        'consultation' => 'Online Consultation',
        'consultation_desc' => 'Access online consultation services with your doctor.',
        'consult_now' => 'Consult Now',
        'confirmation' => 'Appointment Confirmation',
        'confirmation_desc' => 'View the confirmation for your scheduled appointment.',
        'view_confirmation' => 'View Confirmation',
        'profile' => 'My Profile',
        'profile_desc' => 'View your basic patient account information.',
        'edit_profile' => 'Edit Profile',
        'prescriptions' => 'Prescriptions',
        'prescriptions_desc' => 'View your prescribed medications and treatment plans.',
        'view_prescriptions' => 'View Prescriptions',
        'footer' => 'Your Health. Your Records. Your Control.'
    ],

    'ta' => [
        'logout' => 'வெளியேறு',
        'welcome' => 'வரவேற்கிறோம்',
        'tagline' => 'உங்கள் ஆரோக்கியம். உங்கள் பதிவுகள். உங்கள் கட்டுப்பாடு.',
        'manage' => 'உங்கள் மருத்துவச் சேவைகளை ஒரே இடத்தில் நிர்வகிக்கலாம்.',
        'patient_id' => 'நோயாளி அடையாள எண்',
        'services' => 'நோயாளி சேவைகள்',
        'appointment' => 'மருத்துவர் சந்திப்பைப் பதிவு செய்க',
        'appointment_desc' => 'உங்கள் மருத்துவத் தேவைகளுக்காக மருத்துவரைச் சந்திக்க நேரம் பதிவு செய்யுங்கள்.',
        'book_now' => 'இப்போது பதிவு செய்க',
        'health' => 'தற்போதைய உடல்நலப் பிரச்சினை',
        'health_desc' => 'ஆலோசனைக்காக உங்கள் தற்போதைய அறிகுறிகள் மற்றும் உடல்நலப் பிரச்சினைகளைப் பதிவு செய்யுங்கள்.',
        'enter_details' => 'விவரங்களை உள்ளிடுக',
        'report' => 'மருத்துவ அறிக்கையைப் பதிவேற்றுக',
        'report_desc' => 'தற்போதைய மருத்துவச் சந்திப்பு தொடர்பான அறிக்கை அல்லது ஸ்கேன் பதிவேற்றுக.',
        'upload' => 'அறிக்கையைப் பதிவேற்றுக',
        'consultation' => 'இணையவழி மருத்துவ ஆலோசனை',
        'consultation_desc' => 'உங்கள் மருத்துவருடன் இணையவழி ஆலோசனை பெறுங்கள்.',
        'consult_now' => 'ஆலோசனை பெறுக',
        'confirmation' => 'சந்திப்பு உறுதிப்படுத்தல்',
        'confirmation_desc' => 'நீங்கள் பதிவு செய்த மருத்துவச் சந்திப்பின் உறுதிப்படுத்தலைப் பார்க்கவும்.',
        'view_confirmation' => 'உறுதிப்படுத்தலைப் பார்க்கவும்',
        'profile' => 'எனது சுயவிவரம்',
        'profile_desc' => 'உங்கள் நோயாளிக் கணக்கின் அடிப்படைத் தகவல்களைப் பார்க்கவும்.',
        'edit_profile' => 'சுயவிவரத்தைத் திருத்துக',
        'prescriptions' => 'மருந்துச் சீட்டுகள்',
        'prescriptions_desc' => 'மருத்துவர் பரிந்துரைத்த மருந்துகள் மற்றும் சிகிச்சைத் திட்டங்களைப் பார்க்கவும்.',
        'view_prescriptions' => 'மருந்துச் சீட்டுகளைப் பார்க்கவும்',
        'footer' => 'உங்கள் ஆரோக்கியம். உங்கள் பதிவுகள். உங்கள் கட்டுப்பாடு.'
    ],

    'hi' => [
        'logout' => 'लॉग आउट',
        'welcome' => 'स्वागत है',
        'tagline' => 'आपका स्वास्थ्य। आपके रिकॉर्ड। आपका नियंत्रण।',
        'manage' => 'अपनी स्वास्थ्य सेवाओं को एक ही जगह से प्रबंधित करें।',
        'patient_id' => 'मरीज़ पहचान संख्या',
        'services' => 'मरीज़ सेवाएँ',
        'appointment' => 'अपॉइंटमेंट बुक करें',
        'appointment_desc' => 'अपनी स्वास्थ्य आवश्यकताओं के लिए डॉक्टर से मिलने का समय तय करें।',
        'book_now' => 'अभी बुक करें',
        'health' => 'वर्तमान स्वास्थ्य समस्या',
        'health_desc' => 'परामर्श के लिए अपने वर्तमान लक्षण और स्वास्थ्य संबंधी चिंताएँ दर्ज करें।',
        'enter_details' => 'विवरण दर्ज करें',
        'report' => 'मेडिकल रिपोर्ट अपलोड करें',
        'report_desc' => 'अपनी वर्तमान विज़िट से संबंधित मेडिकल रिपोर्ट या स्कैन अपलोड करें।',
        'upload' => 'रिपोर्ट अपलोड करें',
        'consultation' => 'ऑनलाइन परामर्श',
        'consultation_desc' => 'अपने डॉक्टर से ऑनलाइन परामर्श प्राप्त करें।',
        'consult_now' => 'अभी परामर्श लें',
        'confirmation' => 'अपॉइंटमेंट की पुष्टि',
        'confirmation_desc' => 'अपने निर्धारित अपॉइंटमेंट की पुष्टि देखें।',
        'view_confirmation' => 'पुष्टि देखें',
        'profile' => 'मेरी प्रोफ़ाइल',
        'profile_desc' => 'अपने मरीज़ खाते की बुनियादी जानकारी देखें।',
        'edit_profile' => 'प्रोफ़ाइल संपादित करें',
        'prescriptions' => 'दवाइयों के पर्चे',
        'prescriptions_desc' => 'डॉक्टर द्वारा निर्धारित दवाइयाँ और उपचार योजनाएँ देखें।',
        'view_prescriptions' => 'पर्चे देखें',
        'footer' => 'आपका स्वास्थ्य। आपके रिकॉर्ड। आपका नियंत्रण।'
    ]
];

$t = $translations[$language];

$patient_name = htmlspecialchars(
    $_SESSION['patient_name'] ?? 'Patient',
    ENT_QUOTES,
    'UTF-8'
);

$patient_id = htmlspecialchars(
    (string)$_SESSION['patient_id'],
    ENT_QUOTES,
    'UTF-8'
);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CareConnect - <?php echo $t['services']; ?></title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f8fa;
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

header h2 {
    font-size: 25px;
}

header a {
    color: white;
    text-decoration: none;
    background: #05636e;
    padding: 10px 18px;
    border-radius: 6px;
}

.container {
    max-width: 1100px;
    margin: 35px auto;
    padding: 0 20px;
}

.welcome {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 12px #0000000d;
    margin-bottom: 30px;
}

.welcome h1 {
    color: #087f8c;
    margin-bottom: 10px;
}

.welcome p {
    color: #687985;
    line-height: 1.8;
}

.patient-id {
    margin-top: 12px;
    font-weight: bold;
    color: #087f8c;
}

.section-title {
    margin-bottom: 20px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 22px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 12px #0000000d;
    border: 1px solid #e7eff1;
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 18px #00000012;
}

.card .emoji {
    font-size: 32px;
    margin-bottom: 15px;
}

.card h3 {
    margin-bottom: 10px;
    color: #087f8c;
    line-height: 1.6;
}

.card p {
    color: #687985;
    font-size: 14px;
    line-height: 1.8;
    margin-bottom: 20px;
}

button {
    background: #087f8c;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

button:hover {
    background: #05636e;
}

footer {
    text-align: center;
    padding: 25px;
    color: #7b8992;
    margin-top: 35px;
    line-height: 1.8;
}

@media(max-width: 650px) {
    .cards {
        grid-template-columns: 1fr;
    }

    header {
        padding: 18px;
    }

    header h2 {
        font-size: 21px;
    }

    .container {
        margin-top: 20px;
    }
}
</style>
</head>

<body>

<header>
    <h2>♥ CareConnect</h2>
    <a href="index.html"><?php echo $t['logout']; ?></a>
</header>

<div class="container">

    <div class="welcome">
        <h1><?php echo $t['welcome']; ?>, <?php echo $patient_name; ?>!</h1>

        <p>
            <?php echo $t['tagline']; ?><br>
            <?php echo $t['manage']; ?>
        </p>

        <p class="patient-id">
            <?php echo $t['patient_id']; ?>: <?php echo $patient_id; ?>
        </p>
    </div>

    <h2 class="section-title"><?php echo $t['services']; ?></h2>

    <div class="cards">

        <div class="card">
            <div class="emoji">👨‍⚕️</div>
            <h3><?php echo $t['appointment']; ?></h3>
            <p><?php echo $t['appointment_desc']; ?></p>
            <button onclick="location.href='appointment.php'">
                <?php echo $t['book_now']; ?>
            </button>
        </div>

        <div class="card">
            <div class="emoji">📝</div>
            <h3><?php echo $t['health']; ?></h3>
            <p><?php echo $t['health_desc']; ?></p>
            <button onclick="location.href='healthissue.php'">
                <?php echo $t['enter_details']; ?>
            </button>
        </div>

        <div class="card">
            <div class="emoji">📄</div>
            <h3><?php echo $t['report']; ?></h3>
            <p><?php echo $t['report_desc']; ?></p>
            <button onclick="location.href='upload_report.php'">
                <?php echo $t['upload']; ?>
            </button>
        </div>

        <div class="card">
            <div class="emoji">💻</div>
            <h3><?php echo $t['consultation']; ?></h3>
            <p><?php echo $t['consultation_desc']; ?></p>
            <button onclick="location.href='online_consultation.php'">
                <?php echo $t['consult_now']; ?>
            </button>
        </div>

        <div class="card">
            <div class="emoji">🏥</div>
            <h3><?php echo $t['confirmation']; ?></h3>
            <p><?php echo $t['confirmation_desc']; ?></p>
            <button onclick="location.href='confirmation.php'">
                <?php echo $t['view_confirmation']; ?>
            </button>
        </div>

        <div class="card">
            <div class="emoji">👤</div>
            <h3><?php echo $t['profile']; ?></h3>
            <p><?php echo $t['profile_desc']; ?></p>
            <button onclick="location.href='edit_profile.php'">
                <?php echo $t['edit_profile']; ?>
            </button>
        </div>

        <div class="card">
            <div class="emoji">📋</div>
            <h3><?php echo $t['prescriptions']; ?></h3>
            <p><?php echo $t['prescriptions_desc']; ?></p>
            <button onclick="location.href='prescriptions.php'">
                <?php echo $t['view_prescriptions']; ?>
            </button>
        </div>

    </div>
</div>

<footer>
    © 2026 CareConnect | <?php echo $t['footer']; ?>
</footer>

</body>
</html>
