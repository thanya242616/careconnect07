<?php
session_start();

if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctorlogin.html");
    exit();
}

include "db.php";

$doctor_id = $_SESSION['doctor_id'];
$doctor_name = $_SESSION['doctor_name'] ?? '';
$message = '';
$message_type = '';

/* Accept, reject, or save a Google Meet link */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consultation_id = (int)($_POST['consultation_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    // Verify that this consultation belongs to the logged-in doctor.
    $check = $conn->prepare(
        "SELECT consultation_id, status FROM consultations
         WHERE consultation_id = ? AND doctor_name = ? LIMIT 1"
    );
    $check->bind_param("is", $consultation_id, $doctor_name);
    $check->execute();
    $consultation = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$consultation) {
        $message = "Consultation not found for your account.";
        $message_type = 'error';
    } elseif ($action === 'accept') {
        $response = "Your online consultation request has been accepted by Dr. " . $doctor_name . ".";
        $stmt = $conn->prepare(
            "UPDATE consultations SET status='Accepted', doctor_response=?
             WHERE consultation_id=? AND doctor_name=? AND status='Pending'"
        );
        $stmt->bind_param("sis", $response, $consultation_id, $doctor_name);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $message = "Consultation accepted successfully.";
            $message_type = 'success';
        } else {
            $message = "This request is no longer Pending, so it was not changed.";
            $message_type = 'error';
        }
        $stmt->close();
    } elseif ($action === 'reject') {
        $response = "Your online consultation request has been rejected by Dr. " . $doctor_name . ".";
        $stmt = $conn->prepare(
            "UPDATE consultations SET status='Rejected', doctor_response=?
             WHERE consultation_id=? AND doctor_name=? AND status='Pending'"
        );
        $stmt->bind_param("sis", $response, $consultation_id, $doctor_name);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $message = "Consultation request rejected.";
            $message_type = 'success';
        } else {
            $message = "This request is no longer Pending, so it was not changed.";
            $message_type = 'error';
        }
        $stmt->close();
    } elseif ($action === 'save_meet_link') {
        $meet_link = trim($_POST['meet_link'] ?? '');

        if (!in_array($consultation['status'], ['Accepted', 'In Progress'], true)) {
            $message = "Accept the consultation before adding a Meet link.";
            $message_type = 'error';
        } elseif (!preg_match('~^https://meet\.google\.com/[a-zA-Z0-9-]+/?$~', $meet_link)) {
            $message = "Enter a valid Google Meet link, for example https://meet.google.com/abc-defg-hij";
            $message_type = 'error';
        } else {
            $stmt = $conn->prepare(
                "UPDATE consultations SET meet_link=?, doctor_response=?
                 WHERE consultation_id=? AND doctor_name=? AND status IN ('Accepted','In Progress')"
            );
            $response = "Dr. " . $doctor_name . " has shared a Google Meet link for your online consultation.";
            $stmt->bind_param("ssis", $meet_link, $response, $consultation_id, $doctor_name);
            if ($stmt->execute() && $stmt->affected_rows >= 0) {
                $message = "Google Meet link saved. It can now be shown on the patient's consultation page.";
                $message_type = 'success';
            } else {
                $message = "Unable to save the Meet link. Please try again.";
                $message_type = 'error';
            }
            $stmt->close();
        }
    } else {
        $message = "Invalid action.";
        $message_type = 'error';
    }
}

/* Load consultations for this doctor */
$sql = "SELECT * FROM consultations
        WHERE doctor_name = ?
        ORDER BY CASE
            WHEN status='Pending' THEN 1
            WHEN status='Accepted' THEN 2
            WHEN status='In Progress' THEN 3
            WHEN status='Completed' THEN 4
            WHEN status='Rejected' THEN 5
            ELSE 6 END,
        consultation_date ASC, consultation_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $doctor_name);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CareConnect - Online Consultations</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Arial,sans-serif;background:#f2f7fb;color:#263238}
.sidebar{position:fixed;left:0;top:0;width:240px;height:100vh;background:#073b4c;color:#fff;padding:25px 15px}
.logo{text-align:center;font-size:24px;font-weight:bold;margin-bottom:35px}
.logo span{color:#6ee7b7}
.sidebar a{display:block;text-decoration:none;color:#fff;padding:13px 15px;margin:7px 0;border-radius:8px;font-size:15px}
.sidebar a:hover,.sidebar a.active{background:#0b5368}
.main{margin-left:240px;padding:30px}
.topbar{background:#fff;padding:18px 25px;border-radius:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:25px;box-shadow:0 3px 12px #0000000f}
.topbar h2{margin:0;color:#073b4c}
.doctor-info{font-size:14px;color:#607d8b}
.hero{background:linear-gradient(135deg,#dff6ff,#eefaff);padding:28px;border-radius:15px;margin-bottom:25px}
.hero h1{margin:0 0 8px;color:#073b4c}
.hero p{margin:0;color:#546e7a;line-height:1.5}
.message{padding:14px 18px;border-radius:8px;margin-bottom:20px;font-weight:bold}
.success{background:#d1fae5;color:#065f46}.error{background:#fee2e2;color:#991b1b}
.filters{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
.filter-btn{border:0;padding:10px 18px;border-radius:20px;background:#fff;color:#073b4c;cursor:pointer;font-weight:bold}
.filter-btn:hover{background:#dff6ff}
.consultation-card{background:#fff;border-radius:14px;padding:24px;margin-bottom:20px;box-shadow:0 3px 12px #0000000f;border-left:5px solid #0b7285}
.card-header{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:18px}
.patient-name{font-size:20px;font-weight:bold;color:#073b4c}
.status{padding:7px 14px;border-radius:20px;font-size:13px;font-weight:bold}
.status-pending{background:#fff3cd;color:#856404}.status-accepted{background:#dbeafe;color:#1d4ed8}
.status-progress{background:#d1fae5;color:#065f46}.status-rejected{background:#fee2e2;color:#991b1b}
.status-completed{background:#e5e7eb;color:#374151}
.details{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:18px}
.detail-box{background:#f8fbfd;padding:13px;border-radius:8px;overflow-wrap:anywhere}
.detail-label{font-size:12px;color:#78909c;margin-bottom:5px}
.detail-value{font-weight:bold;color:#37474f}
.symptoms{background:#f8fbfd;padding:15px;border-radius:8px;margin-bottom:18px;white-space:normal;overflow-wrap:anywhere}
.symptoms strong{display:block;margin-bottom:7px;color:#073b4c}
.response{background:#eefaf5;padding:14px;border-radius:8px;margin-bottom:18px;color:#065f46;overflow-wrap:anywhere}
.actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.btn{display:inline-block;border:0;padding:11px 18px;border-radius:7px;cursor:pointer;font-weight:bold;font-size:14px;text-decoration:none}
.btn-accept{background:#198754;color:#fff}.btn-reject{background:#dc3545;color:#fff}
.btn-save{background:#0b7285;color:#fff}.btn-join{background:#087f5b;color:#fff}
.meet-form{margin-top:15px;padding:16px;background:#f0fbff;border:1px solid #c8eaf2;border-radius:10px}
.meet-form label{display:block;font-weight:bold;color:#073b4c;margin-bottom:8px}
.meet-form input{width:100%;padding:12px;border:1px solid #b8cbd2;border-radius:7px;margin-bottom:10px;font-size:14px}
.meet-form small{display:block;color:#607d8b;margin:0 0 12px;line-height:1.5}
.start-info{background:#fff8e1;color:#795548;padding:12px 15px;border-radius:8px;margin-bottom:15px;font-size:14px}
.empty{background:#fff;padding:50px;border-radius:14px;text-align:center;color:#78909c}
.btn:hover{opacity:.9}
@media(max-width:800px){.sidebar{width:100%;height:auto;position:relative}.sidebar a{display:inline-block}.main{margin-left:0;padding:16px}.consultation-card{padding:17px}}
</style>
</head>
<body>
<div class="sidebar">
  <div class="logo">❤️ Care<span>Connect</span></div>
  <a href="doctor_profile.php">🏠 Dashboard</a>
  <a href="doctor_appointments.php">📅 Appointments</a>
  <a href="doctor_online_consultation.php" class="active">💻 Online Consultations</a>
  <a href="doctor_reports.php">📄 Medical Reports</a>
  <a href="doctor_prescription.php">💊 Prescriptions</a>
  <a href="doctor_logout.php">🚪 Logout</a>
</div>

<div class="main">
  <div class="topbar">
    <h2>Online Consultations</h2>
    <div class="doctor-info">👨‍⚕️ Dr. <?= htmlspecialchars($doctor_name) ?></div>
  </div>

  <div class="hero">
    <h1>Online Consultation Requests</h1>
    <p>Review patient requests, accept or reject them, and share a Google Meet link for accepted consultations.</p>
  </div>

  <?php if ($message !== ''): ?>
    <div class="message <?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="filters">
    <button type="button" class="filter-btn" onclick="filterConsultations('all')">All</button>
    <button type="button" class="filter-btn" onclick="filterConsultations('Pending')">Pending</button>
    <button type="button" class="filter-btn" onclick="filterConsultations('Accepted')">Accepted</button>
    <button type="button" class="filter-btn" onclick="filterConsultations('In Progress')">In Progress</button>
    <button type="button" class="filter-btn" onclick="filterConsultations('Completed')">Completed</button>
    <button type="button" class="filter-btn" onclick="filterConsultations('Rejected')">Rejected</button>
  </div>

  <?php if ($result->num_rows === 0): ?>
    <div class="empty">
      <h3>No consultation requests</h3>
      <p>Patient online consultation requests will appear here.</p>
    </div>
  <?php else: ?>
    <?php while ($row = $result->fetch_assoc()):
      $status = $row['status'] ?? '';
      $status_class = 'status-pending';
      if ($status === 'Accepted') $status_class = 'status-accepted';
      elseif ($status === 'In Progress') $status_class = 'status-progress';
      elseif ($status === 'Rejected') $status_class = 'status-rejected';
      elseif ($status === 'Completed') $status_class = 'status-completed';

      $meet_link = trim($row['meet_link'] ?? '');
      $appointment_datetime = null;
      $appointment_reached = false;
      if (!empty($row['consultation_date']) && !empty($row['consultation_time'])) {
          try {
              $appointment_datetime = new DateTime(
                  $row['consultation_date'].' '.$row['consultation_time'],
                  new DateTimeZone('Asia/Kolkata')
              );
              $now = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
              $appointment_reached = ($now >= $appointment_datetime);
          } catch (Exception $e) {
              $appointment_datetime = null;
          }
      }
    ?>
      <div class="consultation-card" data-status="<?= htmlspecialchars($status) ?>">
        <div class="card-header">
          <div class="patient-name">👤 <?= htmlspecialchars($row['patient_name'] ?? 'Patient') ?></div>
          <span class="status <?= $status_class ?>"><?= htmlspecialchars($status) ?></span>
        </div>

        <div class="details">
          <div class="detail-box"><div class="detail-label">Patient ID</div><div class="detail-value"><?= htmlspecialchars((string)($row['patient_id'] ?? '')) ?></div></div>
          <div class="detail-box"><div class="detail-label">Hospital</div><div class="detail-value"><?= htmlspecialchars($row['hospital_name'] ?? '') ?></div></div>
          <div class="detail-box"><div class="detail-label">Consultation Date</div><div class="detail-value"><?= htmlspecialchars($row['consultation_date'] ?? '') ?></div></div>
          <div class="detail-box"><div class="detail-label">Consultation Time</div><div class="detail-value"><?= htmlspecialchars($row['consultation_time'] ?? '') ?></div></div>
        </div>

        <div class="symptoms"><strong>Patient Symptoms</strong><?= nl2br(htmlspecialchars($row['symptoms'] ?? 'Not provided')) ?></div>

        <?php if (!empty($row['doctor_response'])): ?>
          <div class="response"><strong>Doctor Response:</strong><br><?= nl2br(htmlspecialchars($row['doctor_response'])) ?></div>
        <?php endif; ?>

        <div class="actions">
          <?php if ($status === 'Pending'): ?>
            <form method="POST">
              <input type="hidden" name="consultation_id" value="<?= (int)$row['consultation_id'] ?>">
              <input type="hidden" name="action" value="accept">
              <button class="btn btn-accept" type="submit">✓ Accept</button>
            </form>
            <form method="POST" onsubmit="return confirm('Reject this consultation request?');">
              <input type="hidden" name="consultation_id" value="<?= (int)$row['consultation_id'] ?>">
              <input type="hidden" name="action" value="reject">
              <button class="btn btn-reject" type="submit">✕ Reject</button>
            </form>
          <?php elseif (in_array($status, ['Accepted', 'In Progress'], true)): ?>
            <?php if ($appointment_datetime && !$appointment_reached): ?>
              <div class="start-info">🕐 Scheduled for <strong><?= htmlspecialchars($appointment_datetime->format('d M Y, h:i A')) ?></strong>. You can add the Meet link now; the patient should join at the scheduled time.</div>
            <?php endif; ?>

            <?php if ($meet_link !== ''): ?>
              <a class="btn btn-join" href="<?= htmlspecialchars($meet_link, ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer">↗ Open Google Meet</a>
            <?php endif; ?>

            <form method="POST" class="meet-form">
              <input type="hidden" name="consultation_id" value="<?= (int)$row['consultation_id'] ?>">
              <input type="hidden" name="action" value="save_meet_link">
              <label for="meet_link_<?= (int)$row['consultation_id'] ?>">Google Meet link</label>
              <input
                type="url"
                id="meet_link_<?= (int)$row['consultation_id'] ?>"
                name="meet_link"
                placeholder="https://meet.google.com/abc-defg-hij"
                value="<?= htmlspecialchars($meet_link, ENT_QUOTES) ?>"
                pattern="https://meet\.google\.com/[A-Za-z0-9-]+/?"
                title="Enter a valid https://meet.google.com/ link"
                required>
              <small>First create the meeting in Google Meet, copy its link, paste it here, and click Save & Share. The link is saved in CareConnect for the patient page to display.</small>
              <button type="submit" class="btn btn-save">Save &amp; Share Link</button>
            </form>
          <?php elseif ($status === 'Completed'): ?>
            <div class="start-info">✓ This consultation has been completed.</div>
          <?php elseif ($status === 'Rejected'): ?>
            <div class="start-info">✕ This consultation request was rejected.</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

<script>
function filterConsultations(status) {
  document.querySelectorAll('.consultation-card').forEach(function(card) {
    card.style.display = (status === 'all' || card.getAttribute('data-status') === status) ? 'block' : 'none';
  });
}
</script>
</body>
</html>
<?php
$stmt->close();
?>
