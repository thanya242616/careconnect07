<?php
session_start();

// Check patient login
if (!isset($_SESSION['patient_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $language = $_POST['language'] ?? 'en';

    // Allow only supported languages
    $allowed_languages = ['en', 'ta', 'hi'];

    if (in_array($language, $allowed_languages, true)) {

        $_SESSION['language'] = $language;

        header("Location: patient.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Language - CareConnect</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 30px;
            width: 320px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #0002;
            text-align: center;
        }

        h2 {
            color: #087f8c;
        }

        select, button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border-radius: 6px;
            font-size: 16px;
        }

        select {
            border: 1px solid #ccc;
        }

        button {
            background: #087f8c;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #05636e;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>CareConnect</h2>

    <p>Choose Your Preferred Language</p>
    <p>உங்களுக்கு விருப்பமான மொழியைத் தேர்ந்தெடுக்கவும்</p>

    <form method="POST" action="language.php">

        <select name="language" required>
            <option value="en">English</option>
            <option value="ta">தமிழ் (Tamil)</option>
            <option value="hi">हिन्दी (Hindi)</option>
        </select>

        <button type="submit">Continue / தொடரவும்</button>

    </form>

</div>

</body>
</html>