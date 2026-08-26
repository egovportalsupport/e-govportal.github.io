<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['validpage']) || $_SESSION['validpage'] !== TRUE) {
    header('location: login.php');
    exit;
}

$isLoggedIn   = TRUE;
$userId       = (int) ($_SESSION['id_application'] ?? 0);
$firstName    = htmlspecialchars($_SESSION['firstname'] ?? '');
$lastName     = htmlspecialchars($_SESSION['lastname']  ?? '');
$userEmail    = htmlspecialchars($_SESSION['email']     ?? '');
$initials     = strtoupper(substr($firstName,0,1).substr($lastName,0,1));
$fullName     = trim("$firstName $lastName");

// ═══════════════════════════════════════════════════════════
//  AJAX ENDPOINT — live Valid ID application status
//  GET track_application.php?ajax=vid_status
//
//  The Valid ID service is the only one backed by a real database
//  table (`validid`, written by vid_requirements.php and updated by
//  admin_application_detail.php). Everything below returns that
//  user's rows as JSON so the tracker can show the *actual* status
//  instead of the client-side localStorage simulation used by the
//  other (not-yet-built) services.
// ═══════════════════════════════════════════════════════════
if (($_GET['ajax'] ?? '') === 'vid_status') {
    header('Content-Type: application/json');

    // connection.php lives alongside this file (governmentservice/),
    // same place valid_id/*.php's include('../connection.php') points to.
    $dbFile = __DIR__ . '/connection.php';
    if (!file_exists($dbFile)) {
        $dbFile = __DIR__ . '/../connection.php';
    }

    if (!file_exists($dbFile)) {
        echo json_encode(['error' => 'connection.php not found', 'rows' => []]);
        exit;
    }

    require_once $dbFile;

    if (!isset($Connection)) {
        echo json_encode(['error' => 'connection.php did not provide $Connection', 'rows' => []]);
        exit;
    }

    $rows = [];
    $res = @mysqli_query(
        $Connection,
        "SELECT id_validid, reference, appstatus, datesubmit, gov_id
         FROM validid
         WHERE id_application = " . (int) $userId . "
         ORDER BY id_validid DESC"
    );

    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $rows[] = [
                'id_validid' => (int) $r['id_validid'],
                'reference'  => $r['reference'] ?: ('VID-' . $r['id_validid']),
                'status'     => $r['appstatus'] ?: 'Submitted',
                'datesubmit' => $r['datesubmit'],
                'idtype'     => $r['gov_id'],
            ];
        }
    }

    echo json_encode(['rows' => $rows]);
    exit;
}

// ═══════════════════════════════════════════════════════════
//  AJAX ENDPOINT — live TESDA Enrollment application status
//  GET track_application.php?ajax=tesda_status
//
//  Mirrors the vid_status block above. Requires tesda_schema_update.sql
//  to have been run (adds id_application to the `tesda` table) —
//  falls back to an empty row set gracefully if that column isn't
//  there yet, or isn't populated for a given row.
// ═══════════════════════════════════════════════════════════
if (($_GET['ajax'] ?? '') === 'tesda_status') {
    header('Content-Type: application/json');

    $dbFile = __DIR__ . '/connection.php';
    if (!file_exists($dbFile)) {
        $dbFile = __DIR__ . '/../connection.php';
    }

    if (!file_exists($dbFile)) {
        echo json_encode(['error' => 'connection.php not found', 'rows' => []]);
        exit;
    }

    require_once $dbFile;

    if (!isset($Connection)) {
        echo json_encode(['error' => 'connection.php did not provide $Connection', 'rows' => []]);
        exit;
    }

    $rows = [];
    $res = @mysqli_query(
        $Connection,
        "SELECT id_tesda, reference, appstatus, datesubmit, course_type
         FROM tesda
         WHERE id_application = " . (int) $userId . "
         ORDER BY id_tesda DESC"
    );

    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $rows[] = [
                'id_tesda'   => (int) $r['id_tesda'],
                'reference'  => $r['reference'] ?: ('TESDA-' . $r['id_tesda']),
                'status'     => $r['appstatus'] ?: 'Submitted',
                'datesubmit' => $r['datesubmit'],
                'course'     => $r['course_type'],
            ];
        }
    }

    echo json_encode(['rows' => $rows]);
    exit;
}

// ═══════════════════════════════════════════════════════════
//  AJAX ENDPOINT — live CHED Scholarship application status
//  GET track_application.php?ajax=ched_status
//
//  Mirrors the vid_status / tesda_status blocks above. Requires
//  ched_schema_update.sql to have been run (adds id_application to
//  the `ched` table) — falls back to an empty row set gracefully if
//  that column isn't there yet, or isn't populated for a given row.
// ═══════════════════════════════════════════════════════════
if (($_GET['ajax'] ?? '') === 'ched_status') {
    header('Content-Type: application/json');

    $dbFile = __DIR__ . '/connection.php';
    if (!file_exists($dbFile)) {
        $dbFile = __DIR__ . '/../connection.php';
    }

    if (!file_exists($dbFile)) {
        echo json_encode(['error' => 'connection.php not found', 'rows' => []]);
        exit;
    }

    require_once $dbFile;

    if (!isset($Connection)) {
        echo json_encode(['error' => 'connection.php did not provide $Connection', 'rows' => []]);
        exit;
    }

    $rows = [];
    $res = @mysqli_query(
        $Connection,
        "SELECT id_ched, reference, appstatus, datesubmit, degree
         FROM ched
         WHERE id_application = " . (int) $userId . "
         ORDER BY id_ched DESC"
    );

    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $rows[] = [
                'id_ched'    => (int) $r['id_ched'],
                'reference'  => $r['reference'] ?: ('CHED-' . $r['id_ched']),
                'status'     => $r['appstatus'] ?: 'Under Review',
                'datesubmit' => $r['datesubmit'],
                'course'     => $r['degree'],
            ];
        }
    }

    echo json_encode(['rows' => $rows]);
    exit;
}
//  POST track_application.php?ajax=queue_next
//  Body: date=YYYY-MM-DD&time=<slot label>&prefix=<service prefix>
//
//  Each service (kind of application — VID, TESDA, CHED, etc.) gets its
//  OWN queue counter per date+time slot; they are never combined into a
//  single shared line. Requires appt_queue_schema.sql to have been run
//  (adds the appt_queue_counter and appointment_queue tables, with
//  appt_queue_counter keyed on appt_date+appt_time+service_prefix).
//  Falls back to a client-side localStorage counter in the JS below if
//  this endpoint errors or those tables don't exist yet.
// ═══════════════════════════════════════════════════════════
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_GET['ajax'] ?? '') === 'queue_next') {
    header('Content-Type: application/json');

    $dbFile = __DIR__ . '/connection.php';
    if (!file_exists($dbFile)) {
        $dbFile = __DIR__ . '/../connection.php';
    }

    if (!file_exists($dbFile)) {
        echo json_encode(['error' => 'connection.php not found']);
        exit;
    }

    require_once $dbFile;

    if (!isset($Connection)) {
        echo json_encode(['error' => 'connection.php did not provide $Connection']);
        exit;
    }

    $apptDate = mysqli_real_escape_string($Connection, trim($_POST['date'] ?? ''));
    $apptTime = mysqli_real_escape_string($Connection, trim($_POST['time'] ?? ''));
    $servicePrefix = mysqli_real_escape_string($Connection, trim($_POST['prefix'] ?? ''));

    if ($apptDate === '' || $apptTime === '') {
        echo json_encode(['error' => 'Missing date or time']);
        exit;
    }

    if ($servicePrefix === '') {
        echo json_encode(['error' => 'Missing service prefix']);
        exit;
    }

    // Separate counter per date+time+service (each kind of application —
    // VID, TESDA, CHED, etc. — gets its own queue), bumped atomically with
    // ON DUPLICATE KEY UPDATE so two applicants of the SAME service
    // requesting the SAME slot at once still get distinct numbers. Requires
    // appt_queue_counter's unique key to be (appt_date, appt_time, service_prefix).
    $bumped = @mysqli_query(
        $Connection,
        "INSERT INTO appt_queue_counter (appt_date, appt_time, service_prefix, counter)
         VALUES ('$apptDate', '$apptTime', '$servicePrefix', 1)
         ON DUPLICATE KEY UPDATE counter = counter + 1"
    );

    if (!$bumped) {
        echo json_encode(['error' => 'appt_queue_counter table missing/outdated — run appt_queue_schema.sql (needs a service_prefix column)']);
        exit;
    }

    $res = mysqli_query(
        $Connection,
        "SELECT counter FROM appt_queue_counter
         WHERE appt_date = '$apptDate' AND appt_time = '$apptTime' AND service_prefix = '$servicePrefix'"
    );
    $row = $res ? mysqli_fetch_assoc($res) : null;
    $queueNumber = (int) ($row['counter'] ?? 1);

    // Log which applicant holds this ticket (useful for an admin queue view later).
    // NOTE: this used to run with @ (error suppression) and its result was
    // never checked — so if this INSERT failed (missing table/column,
    // duplicate key, etc.) the applicant still saw a queue number on
    // screen, but no row ever landed in `appointment_queue`, and the
    // admin Appointment Queue page silently showed nothing for that
    // ticket. Now the failure is surfaced instead of hidden.
    $logRes = mysqli_query(
        $Connection,
        "INSERT INTO appointment_queue (appt_date, appt_time, service_prefix, id_application, queue_number)
         VALUES ('$apptDate', '$apptTime', '$servicePrefix', " . (int) $userId . ", $queueNumber)"
    );

    $response = ['queue_number' => $queueNumber];
    if (!$logRes) {
        $dbErr = mysqli_error($Connection);
        error_log('queue_next: appointment_queue insert failed — ' . $dbErr);
        // Still return the queue number (applicant flow shouldn't block on
        // this), but flag it so it shows up in the browser console instead
        // of vanishing silently.
        $response['logError'] = 'Ticket was not logged for admin view: ' . $dbErr;
    }

    echo json_encode($response);
    exit;
}

// ═══════════════════════════════════════════════════════════
//  AJAX ENDPOINT — remove a real, DB-backed application
//  POST track_application.php?ajax=remove_application
//  Body: prefix=<vid_|tesda_>&id=<id_validid or id_tesda>
//
//  Only covers the services that are actually backed by a table
//  (validid, tesda). Every other service's "Remove" button never
//  reaches the server — those are pure localStorage entries removed
//  entirely client-side. Scoped to id_application = the logged-in
//  user's session id, so an applicant can never delete a record that
//  isn't theirs even if they tamper with the posted id.
// ═══════════════════════════════════════════════════════════
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_GET['ajax'] ?? '') === 'remove_application') {
    header('Content-Type: application/json');

    $dbFile = __DIR__ . '/connection.php';
    if (!file_exists($dbFile)) {
        $dbFile = __DIR__ . '/../connection.php';
    }

    if (!file_exists($dbFile)) {
        echo json_encode(['error' => 'connection.php not found']);
        exit;
    }

    require_once $dbFile;

    if (!isset($Connection)) {
        echo json_encode(['error' => 'connection.php did not provide $Connection']);
        exit;
    }

    $prefix = trim($_POST['prefix'] ?? '');
    $recId  = (int) ($_POST['id'] ?? 0);

    $REMOVABLE = [
        'vid_'   => ['table' => 'validid', 'idCol' => 'id_validid'],
        'tesda_' => ['table' => 'tesda',   'idCol' => 'id_tesda'],
        'ched_'  => ['table' => 'ched',    'idCol' => 'id_ched'],
    ];

    if (!isset($REMOVABLE[$prefix]) || $recId <= 0) {
        echo json_encode(['error' => 'Unsupported service or missing id']);
        exit;
    }

    $table = $REMOVABLE[$prefix]['table'];
    $idCol = $REMOVABLE[$prefix]['idCol'];

    // id_application scoping is what actually makes this safe — without
    // it, any logged-in applicant could delete anyone else's row just by
    // posting a different id.
    $delRes = mysqli_query(
        $Connection,
        "DELETE FROM `$table` WHERE `$idCol` = $recId AND id_application = " . (int) $userId
    );

    if (!$delRes) {
        echo json_encode(['error' => 'Delete failed: ' . mysqli_error($Connection)]);
        exit;
    }

    if (mysqli_affected_rows($Connection) === 0) {
        echo json_encode(['error' => 'No matching application found (already removed, or not yours)']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

// ═══════════════════════════════════════════════════════════
//  AJAX ENDPOINT — save/refresh a QR verification record
//  POST track_application.php?ajax=save_verification
//  Body: prefix, reference, applicant_name, status, signature_code, signed_at
//
//  This is what makes QR Code Verification real instead of just a QR
//  image containing text: the record is written server-side into
//  `qr_verifications`, and the public verify_qr.php page (no login
//  required) looks THAT table up when someone scans the code — it
//  never trusts data embedded in the QR image itself. A random
//  verify_token is generated once per (service_prefix, reference) and
//  is what verify_qr.php requires alongside the reference; the token
//  is never guessable from the reference number alone.
//
//  For the three DB-backed services (Valid ID / TESDA / CHED) the
//  reference is cross-checked against that service's real table to
//  confirm it actually belongs to the logged-in applicant before a
//  verification record is created or updated for it. The other,
//  still-simulated services have no DB-backed source of truth to
//  check against yet, so that ownership check is skipped for them —
//  same limitation the rest of this file already has for those
//  services (see remove_application above).
// ═══════════════════════════════════════════════════════════
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_GET['ajax'] ?? '') === 'save_verification') {
    header('Content-Type: application/json');

    $dbFile = __DIR__ . '/connection.php';
    if (!file_exists($dbFile)) {
        $dbFile = __DIR__ . '/../connection.php';
    }

    if (!file_exists($dbFile)) {
        echo json_encode(['error' => 'connection.php not found']);
        exit;
    }

    require_once $dbFile;

    if (!isset($Connection)) {
        echo json_encode(['error' => 'connection.php did not provide $Connection']);
        exit;
    }

    $prefix        = trim($_POST['prefix'] ?? '');
    $reference     = trim($_POST['reference'] ?? '');
    $applicantName = trim($_POST['applicant_name'] ?? '') ?: $fullName;
    $status        = trim($_POST['status'] ?? '');
    $sigCode       = trim($_POST['signature_code'] ?? '');
    $sigImage      = trim($_POST['signature_image'] ?? ''); // base64 PNG data URL from the e-sign pad
    $signedAtRaw   = trim($_POST['signed_at'] ?? '');
    $signedAt      = $signedAtRaw !== '' ? date('Y-m-d H:i:s', strtotime($signedAtRaw)) : date('Y-m-d H:i:s');

    if ($prefix === '' || $reference === '') {
        echo json_encode(['error' => 'Missing service prefix or reference']);
        exit;
    }

    // Self-healing: create the table on first use, same pattern as
    // appt_schema.php does for appointment_queue. signature_image is
    // included here for fresh installs; qr_schema.php's
    // ensure_qr_verifications_columns() backfills it on existing tables.
    mysqli_query($Connection, "
        CREATE TABLE IF NOT EXISTS qr_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_prefix VARCHAR(20) NOT NULL,
            reference VARCHAR(100) NOT NULL,
            applicant_name VARCHAR(150) NOT NULL,
            id_application INT NOT NULL,
            status VARCHAR(50) NOT NULL,
            signature_code VARCHAR(100) NOT NULL,
            signature_image LONGTEXT NULL,
            signed_at DATETIME NOT NULL,
            verify_token VARCHAR(64) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_prefix_reference (service_prefix, reference),
            KEY idx_token (verify_token)
        )
    ");

    $qrSchemaFile = __DIR__ . '/qr_schema.php';
    if (!file_exists($qrSchemaFile)) $qrSchemaFile = __DIR__ . '/../qr_schema.php';
    if (file_exists($qrSchemaFile)) {
        require_once $qrSchemaFile;
        ensure_qr_verifications_columns($Connection);
    }

    // DB-backed services: confirm the reference really belongs to this
    // applicant before we let a verification record be created for it.
    $DB_BACKED = [
        'vid_'   => ['table' => 'validid', 'idCol' => 'id_application'],
        'tesda_' => ['table' => 'tesda',   'idCol' => 'id_application'],
        'ched_'  => ['table' => 'ched',    'idCol' => 'id_application'],
    ];
    if (isset($DB_BACKED[$prefix])) {
        $table    = $DB_BACKED[$prefix]['table'];
        $idCol    = $DB_BACKED[$prefix]['idCol'];
        $refEsc   = mysqli_real_escape_string($Connection, $reference);
        $ownerRes = mysqli_query($Connection, "SELECT `$idCol` FROM `$table` WHERE reference = '$refEsc' LIMIT 1");
        $ownerRow = $ownerRes ? mysqli_fetch_assoc($ownerRes) : null;
        if (!$ownerRow || (int) $ownerRow[$idCol] !== $userId) {
            echo json_encode(['error' => 'Reference does not belong to this account']);
            exit;
        }
    }

    $prefixEsc  = mysqli_real_escape_string($Connection, $prefix);
    $refEsc     = mysqli_real_escape_string($Connection, $reference);
    $nameEsc    = mysqli_real_escape_string($Connection, $applicantName);
    $statusEsc  = mysqli_real_escape_string($Connection, $status);
    $sigEsc     = mysqli_real_escape_string($Connection, $sigCode);
    $sigImgEsc  = mysqli_real_escape_string($Connection, $sigImage);
    $signedEsc  = mysqli_real_escape_string($Connection, $signedAt);
    $newToken   = bin2hex(random_bytes(16));

    // ON DUPLICATE KEY UPDATE refreshes applicant/status/signature info
    // on every re-save, but deliberately leaves verify_token out of the
    // UPDATE clause — an existing record keeps its original token, so a
    // previously printed/shared QR code stays valid instead of breaking
    // every time the applicant reopens the modal.
    $saved = mysqli_query($Connection, "
        INSERT INTO qr_verifications
            (service_prefix, reference, applicant_name, id_application, status, signature_code, signature_image, signed_at, verify_token)
        VALUES
            ('$prefixEsc', '$refEsc', '$nameEsc', $userId, '$statusEsc', '$sigEsc', '$sigImgEsc', '$signedEsc', '$newToken')
        ON DUPLICATE KEY UPDATE
            applicant_name = VALUES(applicant_name),
            status = VALUES(status),
            signature_code = VALUES(signature_code),
            signature_image = VALUES(signature_image),
            signed_at = VALUES(signed_at)
    ");

    if (!$saved) {
        echo json_encode(['error' => 'Could not save verification record: ' . mysqli_error($Connection)]);
        exit;
    }

    $tokRes = mysqli_query($Connection, "SELECT verify_token FROM qr_verifications WHERE service_prefix = '$prefixEsc' AND reference = '$refEsc' LIMIT 1");
    $tokRow = $tokRes ? mysqli_fetch_assoc($tokRes) : null;

    echo json_encode(['token' => $tokRow['verify_token'] ?? $newToken]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Application — E-Gov Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="navbar.css">
    <style>
:root {
    --primary-color: #004aad;
    --secondary-color: #0077ff;
    --accent-color: #ffd700;
    --text-dark: #333333;
    --text-light: #666666;
    --bg-light: #f8f9fa;
    --white: #ffffff;
    --shadow: 0 10px 30px rgba(0,0,0,0.08);
    --transition: all 0.3s ease;
    --success: #2e7d32;
    --warning: #e65100;
    --info: #0277bd;
}

* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; scroll-behavior: smooth; }
body { background: var(--bg-light); color: var(--text-dark); line-height: 1.6; }

/* ── PAGE HEADER ─────────────────────── */
.page-header {
    text-align: center;
    padding: 70px 20px 90px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: var(--white);
    position: relative;
    overflow: hidden;
}
.page-header::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 320px; height: 320px;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
}
.page-header::after {
    content: '';
    position: absolute; bottom: -40px; left: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.page-header h1 { font-size: 38px; font-weight: 700; margin-bottom: 12px; position: relative; }
.page-header p { font-size: 16px; opacity: 0.88; max-width: 560px; margin: 0 auto; position: relative; }

/* ── SEARCH BAR ────────────────────── */
.search-wrap {
    max-width: 900px;
    margin: -28px auto 0;
    padding: 0 20px;
    position: relative;
    z-index: 10;
}
.search-box {
    background: var(--white);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,74,173,0.15);
    display: flex;
    align-items: center;
    padding: 14px 22px;
    gap: 14px;
}
.search-box i { color: var(--primary-color); font-size: 18px; flex-shrink: 0; }
.search-box input {
    border: none; outline: none; flex: 1;
    font-size: 15px; font-family: 'Poppins', sans-serif;
    color: var(--text-dark);
    background: transparent;
}
.search-box input::placeholder { color: #bbb; }
.search-box select {
    border: none; outline: none;
    font-size: 14px; font-family: 'Poppins', sans-serif;
    color: var(--text-dark); background: #f0f5ff;
    padding: 8px 14px; border-radius: 10px; cursor: pointer;
    font-weight: 500;
}

/* ── MAIN CONTENT ─────────────────── */
.main-content {
    max-width: 900px;
    margin: 36px auto 60px;
    padding: 0 20px;
}

/* ── STATS BAR ───────────────────── */
.stats-bar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}
.stat-card {
    background: var(--white);
    border-radius: 16px;
    padding: 20px 18px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    border-top: 3px solid transparent;
    transition: var(--transition);
}
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.stat-card.total  { border-top-color: var(--primary-color); }
.stat-card.pending  { border-top-color: #f59e0b; }
.stat-card.approved { border-top-color: #10b981; }
.stat-card.done     { border-top-color: #6366f1; }

.stat-card .stat-num {
    font-size: 32px; font-weight: 700;
    color: var(--primary-color); line-height: 1;
    margin-bottom: 6px;
}
.stat-card.pending  .stat-num { color: #f59e0b; }
.stat-card.approved .stat-num { color: #10b981; }
.stat-card.done     .stat-num { color: #6366f1; }
.stat-card .stat-label { font-size: 12px; font-weight: 500; color: var(--text-light); }

/* ── SECTION TITLE ───────────────── */
.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    flex-wrap: wrap;
    gap: 10px;
}
.section-head h2 {
    font-size: 20px; font-weight: 700;
    color: var(--text-dark);
    display: flex; align-items: center; gap: 10px;
}
.section-head h2 i { color: var(--primary-color); font-size: 18px; }
.section-head span { font-size: 13px; color: var(--text-light); }

/* ── APPLICATION CARDS ───────────── */
.app-list { display: flex; flex-direction: column; gap: 16px; }

.app-card {
    background: var(--white);
    border-radius: 18px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    border: 1.5px solid #eef0f8;
    overflow: hidden;
    transition: var(--transition);
    animation: cardIn 0.4s ease both;
}
.app-card:hover { box-shadow: 0 12px 32px rgba(0,74,173,0.12); border-color: #d0d9f0; transform: translateY(-2px); }

@keyframes cardIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.app-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px 16px;
}
.app-service-icon {
    width: 50px; height: 50px; border-radius: 14px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 20px; flex-shrink: 0;
}
.app-service-icon.icon-tesda  { background: linear-gradient(135deg, #1565c0, #0288d1); }
.app-service-icon.icon-bir    { background: linear-gradient(135deg, #1b5e20, #388e3c); }
.app-service-icon.icon-dswd   { background: linear-gradient(135deg, #880e4f, #c2185b); }
.app-service-icon.icon-vid    { background: linear-gradient(135deg, #4527a0, #7b1fa2); }
.app-service-icon.icon-smf    { background: linear-gradient(135deg, #bf360c, #e64a19); }
.app-service-icon.icon-ched   { background: linear-gradient(135deg, #006064, #00838f); }

.app-info { flex: 1; min-width: 0; }
.app-service-name { font-size: 16px; font-weight: 700; color: var(--text-dark); margin-bottom: 3px; }
.app-detail-line { font-size: 12.5px; font-weight: 600; color: var(--secondary-color); margin-bottom: 3px; }
.app-ref { font-size: 12px; color: var(--text-light); font-weight: 500; }
.app-ref span { color: var(--primary-color); font-weight: 600; }

.app-status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 30px;
    font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    flex-shrink: 0;
}
.badge-submitted  { background: #e3f2fd; color: #1565c0; }
.badge-processing { background: #fff3e0; color: #e65100; }
.badge-approved   { background: #e8f5e9; color: #2e7d32; }
.badge-completed  { background: #ede7f6; color: #4527a0; }
.badge-rejected   { background: #ffebee; color: #c62828; }
.badge-pending    { background: #fff8e1; color: #f9a825; }

/* ── PROGRESS STEPPER ─────────────── */
.app-progress { padding: 0 24px 20px; }
.progress-stepper {
    display: flex;
    align-items: center;
    gap: 0;
    position: relative;
}
.step-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
    z-index: 1;
}
.step-circle {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
    border: 2px solid #e0e0e0;
    background: var(--white);
    color: #bbb;
    transition: var(--transition);
    margin-bottom: 6px;
}
.step-circle.done  { background: var(--primary-color); border-color: var(--primary-color); color: white; }
.step-circle.active { background: var(--white); border-color: var(--primary-color); color: var(--primary-color); box-shadow: 0 0 0 4px rgba(0,74,173,0.12); }
.step-label { font-size: 10px; font-weight: 600; color: #bbb; text-align: center; white-space: nowrap; max-width: 70px; }
.step-label.done   { color: var(--primary-color); }
.step-label.active { color: var(--primary-color); font-weight: 700; }

.step-line {
    flex: 1; height: 2px;
    background: #e0e0e0;
    margin-bottom: 22px;
    transition: background 0.4s;
}
.step-line.done { background: var(--primary-color); }

/* ── CARD FOOTER ───────────────────── */
.app-card-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 24px;
    border-top: 1px solid #f0f2f8;
    background: #fafbff;
    flex-wrap: wrap; gap: 10px;
}
.app-meta { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
.app-meta-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-light); font-weight: 500; }
.app-meta-item i { color: var(--primary-color); font-size: 11px; }

.btn-details {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--primary-color); color: white;
    padding: 8px 18px; border-radius: 30px;
    font-size: 13px; font-weight: 600;
    border: none; cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    font-family: 'Poppins', sans-serif;
}
.btn-details:hover { background: var(--secondary-color); transform: translateY(-1px); }

.btn-remove {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent; color: #e53935;
    padding: 8px 18px; border-radius: 30px;
    font-size: 13px; font-weight: 600;
    border: 1.5px solid #e53935; cursor: pointer;
    transition: var(--transition);
    font-family: 'Poppins', sans-serif;
}
.btn-remove:hover { background: #e53935; color: white; transform: translateY(-1px); }

/* ── EMPTY STATE ───────────────────── */
.empty-state {
    text-align: center;
    padding: 80px 30px;
    background: var(--white);
    border-radius: 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    display: none;
}
.empty-state.visible { display: block; }
.empty-icon {
    width: 90px; height: 90px; border-radius: 50%;
    background: #eef3ff;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
    font-size: 36px; color: var(--primary-color);
}
.empty-state h3 { font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 10px; }
.empty-state p { font-size: 14px; color: var(--text-light); max-width: 380px; margin: 0 auto 28px; }
.btn-go-services {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--primary-color); color: white;
    padding: 13px 28px; border-radius: 50px;
    text-decoration: none; font-weight: 600; font-size: 15px;
    transition: var(--transition);
    box-shadow: 0 6px 20px rgba(0,74,173,0.25);
}
.btn-go-services:hover { background: var(--secondary-color); transform: translateY(-2px); }

/* ── DETAIL MODAL ──────────────────── */
#detailOverlay {
    display: none; position: fixed; inset: 0;
    background: radial-gradient(circle at 50% 20%, rgba(0,74,173,0.35), rgba(0,15,40,0.72));
    backdrop-filter: blur(6px);
    z-index: 99999;
    align-items: center; justify-content: center;
    padding: 20px;
}
#detailOverlay.active { display: flex; }
#detailModal {
    background: var(--white);
    border-radius: 26px;
    max-width: 1040px; width: 100%;
    max-height: 90vh; overflow-y: auto;
    overflow-x: hidden;
    box-shadow: 0 40px 90px rgba(0,20,60,0.35), 0 0 0 1px rgba(255,255,255,0.06);
    animation: modalPop 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards;
    scrollbar-width: thin;
    scrollbar-color: #c7d3ee transparent;
}
#detailModal::-webkit-scrollbar { width: 8px; }
#detailModal::-webkit-scrollbar-thumb { background: #c7d3ee; border-radius: 10px; }
@keyframes modalPop {
    from { opacity:0; transform: scale(0.9) translateY(24px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.modal-header {
    position: relative;
    padding: 32px 32px 26px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
    overflow: hidden;
}
.modal-header-pattern {
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(circle at 92% -10%, rgba(255,255,255,0.22) 0%, transparent 45%),
        radial-gradient(circle at 8% 120%, rgba(255,215,0,0.18) 0%, transparent 40%);
}
.modal-service-info { position: relative; display: flex; align-items: center; gap: 16px; z-index: 1; }
.modal-icon {
    width: 58px; height: 58px; border-radius: 18px;
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.35);
    backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 24px; flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(0,20,60,0.18);
}
.modal-title { font-size: 19px; font-weight: 700; color: #fff; margin-bottom: 6px; letter-spacing: 0.2px; }
.modal-ref {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11.5px; font-weight: 600; color: #fff;
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.28);
    padding: 4px 12px; border-radius: 20px;
    letter-spacing: 0.2px;
}
.modal-ref i { font-size: 10px; opacity: 0.85; }
.modal-close {
    position: relative; z-index: 1;
    width: 36px; height: 36px; border-radius: 12px;
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.3);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 15px;
    transition: var(--transition); flex-shrink: 0;
}
.modal-close:hover { background: rgba(229,57,53,0.9); border-color: transparent; transform: rotate(90deg); }

.modal-body { padding: 26px 28px 28px; }

.modal-status-section {
    margin-bottom: 22px;
    background: #f7f9fd;
    border: 1px solid #eef1fa;
    border-radius: 16px;
    padding: 16px 18px;
}
.modal-status-section h4,
.modal-timeline h4 {
    display: flex; align-items: center; gap: 8px;
    font-size: 11.5px; font-weight: 700; color: var(--text-light);
    text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 14px;
}
.modal-status-section h4 i, .modal-timeline h4 i { color: var(--primary-color); font-size: 12px; }

.modal-info-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 12px; margin-bottom: 24px;
}
.info-item {
    background: #fff;
    border: 1px solid #eef1fa;
    border-radius: 14px;
    padding: 12px 14px;
    transition: var(--transition);
}
.info-item:hover { border-color: #d7e2fa; box-shadow: 0 4px 14px rgba(0,74,173,0.08); }
.info-item .info-label { font-size: 10.5px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 5px; }
.info-item .info-value { font-size: 14px; font-weight: 600; color: var(--text-dark); }
.info-item .info-value.primary { color: var(--primary-color); }

.modal-timeline { }
#modalTimeline {
    display: flex;
    align-items: flex-start;
    overflow-x: auto;
    padding-bottom: 4px;
}
.timeline-item {
    display: flex; flex-direction: column; align-items: center;
    text-align: center;
    position: relative;
    flex: 1 1 0;
    min-width: 108px;
    padding: 0 6px;
}
.timeline-item::before {
    content: ''; position: absolute;
    left: -50%; top: 17px;
    width: 100%; height: 2px;
    background: linear-gradient(90deg, #dbe4f5, #eef1fa);
    z-index: 0;
}
.timeline-item:first-child::before { display: none; }
.timeline-dot {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: white; flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0,20,60,0.12);
    border: 3px solid var(--white);
    position: relative; z-index: 1;
}
.timeline-dot.done   { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
.timeline-dot.active { background: linear-gradient(135deg, #f59e0b, #fbbf24); animation: pulseDot 1.8s ease-in-out infinite; }
.timeline-dot.pending{ background: #e8edf5; color: #b7c1d6; box-shadow: none; }
@keyframes pulseDot {
    0%, 100% { box-shadow: 0 4px 10px rgba(245,158,11,0.25), 0 0 0 0 rgba(245,158,11,0.35); }
    50% { box-shadow: 0 4px 10px rgba(245,158,11,0.25), 0 0 0 6px rgba(245,158,11,0); }
}
.timeline-text { padding-top: 8px; }
.timeline-text strong { display: block; font-size: 13px; font-weight: 600; color: var(--text-dark); }
.timeline-text span   { display: block; font-size: 11.5px; color: var(--text-light); margin-top: 2px; }

/* ── DIGITAL SIGNATURE ──────────────── */
.modal-signature-section { margin-top: 24px; padding-top: 22px; border-top: 1px dashed #e0e6f2; min-width: 0; }
.modal-signature-section h4 { display: flex; align-items: center; gap: 8px; font-size: 11.5px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
.modal-signature-section h4 i, .modal-appointment-section h4 i { color: var(--primary-color); font-size: 12px; }
.sig-hint { font-size: 12px; color: var(--text-light); margin-bottom: 14px; }

.sig-pad-wrap {
    border: 1.5px dashed #c7d3ee;
    border-radius: 14px;
    background: #fafbff;
    padding: 10px;
}
.sig-canvas {
    width: 100%; height: 160px;
    display: block;
    border-radius: 10px;
    background: var(--white);
    touch-action: none;
    cursor: crosshair;
    border: 1px solid #eef0f8;
}
.sig-baseline { font-size: 11px; color: #bbb; text-align: center; margin-top: 6px; }

.sig-actions { display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
.btn-sig-save {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--success); color: white;
    padding: 9px 20px; border-radius: 30px;
    font-size: 13px; font-weight: 600;
    border: none; cursor: pointer;
    transition: var(--transition);
    font-family: 'Poppins', sans-serif;
}
.btn-sig-save:hover { background: #245e27; transform: translateY(-1px); }
.btn-sig-save:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }
.btn-sig-clear {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent; color: var(--text-light);
    padding: 9px 20px; border-radius: 30px;
    font-size: 13px; font-weight: 600;
    border: 1.5px solid #d5dbe8; cursor: pointer;
    transition: var(--transition);
    font-family: 'Poppins', sans-serif;
}
.btn-sig-clear:hover { border-color: #b8c2d9; color: var(--text-dark); }

.sig-signed-box {
    display: flex; align-items: center; flex-wrap: wrap; gap: 16px;
    background: #f2f9f3; border: 1.5px solid #cdeacf;
    border-radius: 16px; padding: 16px 18px;
    box-shadow: 0 4px 14px rgba(46,125,50,0.08);
}
.sig-signed-img { max-width: 160px; max-height: 70px; background: white; border-radius: 8px; border: 1px solid #e0e6f2; padding: 4px 8px; flex-shrink: 0; }
.sig-signed-meta { flex: 1; min-width: 0; }
.sig-signed-meta .sig-verified { display: inline-flex; align-items: center; gap: 6px; color: var(--success); font-weight: 700; font-size: 13px; margin-bottom: 4px; }
.sig-signed-meta .sig-by { font-size: 13px; font-weight: 600; color: var(--text-dark); }
.sig-signed-meta .sig-meta-line { font-size: 11.5px; color: var(--text-light); margin-top: 2px; }
.sig-signed-meta .sig-code { font-family: monospace; color: var(--primary-color); }
.btn-sig-remove {
    background: transparent; border: none; color: #e53935;
    font-size: 12px; font-weight: 600; cursor: pointer;
    padding: 6px 10px; border-radius: 20px;
    transition: var(--transition);
    flex-basis: 100%; margin-top: 4px; padding-top: 10px;
    border-top: 1px dashed #cdeacf;
    text-align: center;
}
.btn-sig-remove:hover { background: #ffeeee; }

/* ── APPOINTMENT SCHEDULING ─────────── */
.modal-appointment-section { margin-top: 24px; padding-top: 22px; border-top: 1px dashed #e0e6f2; min-width: 0; }
.modal-appointment-section h4 { display: flex; align-items: center; gap: 8px; font-size: 11.5px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
.appt-hint { font-size: 12px; color: var(--text-light); margin-bottom: 14px; }

.appt-form-row { display: flex; gap: 12px; flex-wrap: wrap; }
.appt-field { flex: 1; min-width: 160px; }
.appt-field label { display: block; font-size: 11.5px; font-weight: 600; color: var(--text-light); margin-bottom: 6px; }
.appt-field input[type="date"],
.appt-field select {
    width: 100%; padding: 10px 12px;
    border: 1.5px solid #dfe4ee; border-radius: 10px;
    font-size: 13.5px; font-family: 'Poppins', sans-serif;
    color: var(--text-dark); background: var(--white);
    transition: var(--transition);
}
.appt-field input[type="date"]:focus,
.appt-field select:focus { outline: none; border-color: var(--primary-color); }
.appt-error { font-size: 12px; color: #e53935; margin-top: 8px; display: none; }

.btn-appt-request {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--primary-color); color: white;
    padding: 9px 20px; border-radius: 30px;
    font-size: 13px; font-weight: 600;
    border: none; cursor: pointer;
    transition: var(--transition);
    font-family: 'Poppins', sans-serif;
    margin-top: 14px;
}
.btn-appt-request:hover { background: var(--secondary-color); transform: translateY(-1px); }

.appt-status-box {
    display: flex; align-items: center; flex-wrap: wrap; gap: 16px;
    border-radius: 16px; padding: 16px 18px;
}
.appt-status-box.pending   { background: #fff8ec; border: 1.5px solid #f4dfb0; box-shadow: 0 4px 14px rgba(230,81,0,0.06); }
.appt-status-box.confirmed { background: #f2f9f3; border: 1.5px solid #cdeacf; box-shadow: 0 4px 14px rgba(46,125,50,0.08); }
.appt-status-icon {
    width: 42px; height: 42px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0;
}
.appt-status-box.pending   .appt-status-icon { background: #fbe7bd; color: var(--warning); }
.appt-status-box.confirmed .appt-status-icon { background: #cdeacf; color: var(--success); }
.appt-status-meta { flex: 1; min-width: 0; }
.appt-status-meta .appt-label { font-weight: 700; font-size: 13px; margin-bottom: 3px; }
.appt-status-box.pending   .appt-label { color: #b5790a; }
.appt-status-box.confirmed .appt-label { color: var(--success); }
.appt-status-meta .appt-when { font-size: 13.5px; font-weight: 600; color: var(--text-dark); }
.appt-status-meta .appt-note { font-size: 12px; color: var(--text-light); margin-top: 4px; }
.appt-status-actions {
    display: flex; gap: 8px;
    flex-basis: 100%; justify-content: center;
    margin-top: 4px; padding-top: 12px;
}
.appt-status-box.pending   .appt-status-actions { border-top: 1px dashed #f4dfb0; }
.appt-status-box.confirmed .appt-status-actions { border-top: 1px dashed #cdeacf; }
.appt-queue-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 6px; }
.appt-queue-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: #eef3ff; color: var(--primary-color);
    font-size: 11.5px; font-weight: 700; padding: 3px 10px 3px 8px;
    border-radius: 20px; white-space: nowrap;
}
.appt-wait-est { font-size: 12px; color: var(--text-light); }
.appt-days-until { font-size: 12px; color: var(--text-light); margin-top: 2px; }
.btn-appt-cancel, .btn-appt-reschedule {
    background: transparent; border: 1.5px solid #d5dbe8; color: var(--text-light);
    font-size: 12px; font-weight: 600; cursor: pointer;
    padding: 7px 14px; border-radius: 20px;
    transition: var(--transition); white-space: nowrap;
}
.btn-appt-cancel:hover, .btn-appt-reschedule:hover { border-color: #b8c2d9; color: var(--text-dark); }

/* ── TWO-COLUMN: SIGNATURE + APPOINTMENT ── */
.modal-two-col-sections {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 28px;
    align-items: stretch;
}
.modal-two-col-sections.single-col { grid-template-columns: 1fr; }
.modal-signature-section,
.modal-appointment-section { display: flex; flex-direction: column; }
.sig-signed-box,
.appt-status-box { flex: 1; }

/* ── QR CODE VERIFICATION ───────────── */
.modal-qr-section { margin-top: 24px; padding-top: 22px; border-top: 1px dashed #e0e6f2; min-width: 0; }
.modal-qr-section h4 { display: flex; align-items: center; gap: 8px; font-size: 11.5px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
.modal-qr-section h4 i { color: var(--primary-color); font-size: 12px; }
.qr-hint { font-size: 12px; color: var(--text-light); margin-bottom: 14px; }
.qr-locked-box {
    display: flex; align-items: center; gap: 14px;
    background: #f7f8fb; border: 1.5px dashed #d5dbe8;
    border-radius: 16px; padding: 16px 18px;
    color: var(--text-light); font-size: 12.5px;
}
.qr-locked-box i { font-size: 18px; color: #c1c8db; flex-shrink: 0; }
.qr-verified-box {
    display: flex; align-items: center; gap: 18px;
    background: #f2f9f3; border: 1.5px solid #cdeacf;
    border-radius: 16px; padding: 18px;
    box-shadow: 0 4px 14px rgba(46,125,50,0.08);
    flex-wrap: wrap;
}
.qr-code-img {
    width: 128px; height: 128px; flex-shrink: 0;
    background: white; border-radius: 10px; border: 1px solid #e0e6f2;
    padding: 6px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    cursor: pointer; transition: var(--transition);
}
.qr-code-img:hover { border-color: var(--primary-color); transform: scale(1.03); box-shadow: 0 4px 14px rgba(0,74,173,0.18); }
.qr-code-img img, .qr-code-img canvas { width: 100%; height: 100%; display: block; }

/* ── QR CODE LIGHTBOX (click to view big) ── */
#qrLightboxOverlay {
    display: none; position: fixed; inset: 0;
    background: radial-gradient(circle at 50% 20%, rgba(0,74,173,0.35), rgba(0,15,40,0.78));
    backdrop-filter: blur(6px);
    z-index: 100000;
    align-items: center; justify-content: center;
    padding: 20px;
}
#qrLightboxOverlay.active { display: flex; animation: qrOverlayFadeIn 0.3s ease; }
.qr-lightbox-box {
    background: var(--white);
    border-radius: 22px;
    padding: 28px;
    max-width: 380px; width: 100%;
    text-align: center;
    position: relative;
    box-shadow: 0 20px 60px rgba(0,0,0,0.35);
    animation: qrBoxPopIn 0.3s ease;
}
@keyframes qrOverlayFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes qrBoxPopIn {
    from { opacity: 0; transform: scale(0.92) translateY(8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.qr-lightbox-close {
    position: absolute; top: 14px; right: 14px;
    width: 34px; height: 34px; border-radius: 50%;
    background: #f2f4f8; border: none; color: var(--text-light);
    font-size: 15px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition);
}
.qr-lightbox-close:hover { background: #e4e8f0; color: var(--text-dark); }
.qr-lightbox-title { font-size: 14px; font-weight: 700; color: var(--text-dark); margin-bottom: 16px; }
#qrLightboxImg {
    width: 260px; height: 260px; margin: 0 auto;
    background: white; border-radius: 12px; border: 1px solid #e0e6f2;
    padding: 8px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
#qrLightboxImg img, #qrLightboxImg canvas { width: 100%; height: 100%; display: block; }
.qr-lightbox-ref { margin-top: 14px; font-size: 12px; color: var(--text-light); }
.qr-lightbox-ref span { font-family: monospace; color: var(--primary-color); }
@media (max-width: 480px) {
    .qr-lightbox-box { padding: 22px; }
    #qrLightboxImg { width: 210px; height: 210px; }
}
.qr-verified-meta { flex: 1; min-width: 180px; }
.qr-verified-meta .qr-verified-label { display: inline-flex; align-items: center; gap: 6px; color: var(--success); font-weight: 700; font-size: 13px; margin-bottom: 4px; }
.qr-verified-meta .qr-meta-line { font-size: 11.5px; color: var(--text-light); margin-top: 2px; }
.qr-verified-meta .qr-code-text { font-family: monospace; color: var(--primary-color); }
.qr-actions { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }
.btn-qr-download, .btn-qr-regenerate {
    background: transparent; border: 1.5px solid #d5dbe8; color: var(--text-light);
    font-size: 12px; font-weight: 600; cursor: pointer;
    padding: 7px 14px; border-radius: 20px;
    transition: var(--transition); white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
    font-family: 'Poppins', sans-serif;
}
.btn-qr-download:hover, .btn-qr-regenerate:hover { border-color: #b8c2d9; color: var(--text-dark); }

/* ── NO RESULTS ─────────────────────── */
.no-filter-results {
    text-align: center; padding: 50px 20px;
    color: var(--text-light); display: none;
}
.no-filter-results i { font-size: 40px; margin-bottom: 14px; display: block; opacity: 0.35; }
.no-filter-results p { font-size: 15px; }

/* ── FOOTER ─────────────────────────── */
footer { background: #002a66; color: rgba(255,255,255,0.8); text-align: center; padding: 36px 20px; border-top: 4px solid var(--accent-color); }
footer p { font-size: 14px; }

/* ── RESPONSIVE ──────────────────────── */
@media (max-width: 768px) {
    .page-header { padding: 50px 20px 70px; }
    .page-header h1 { font-size: 26px; }
    .stats-bar { grid-template-columns: 1fr 1fr; }
    .app-card-header { flex-wrap: wrap; }
    .step-label { display: none; }
    .modal-info-grid { grid-template-columns: 1fr; }
    .modal-two-col-sections { grid-template-columns: 1fr; gap: 0; }

    /* ── Application Timeline: mobile ── */
    #modalTimeline { justify-content: flex-start; }
    .timeline-item { min-width: 92px; flex: 0 0 auto; }
    .timeline-text strong { font-size: 12px; }
    .timeline-text span { font-size: 11px; }
}
@media (max-width: 480px) {
    .page-header h1 { font-size: 22px; }
    .stats-bar { grid-template-columns: 1fr 1fr; gap: 10px; }
    .stat-card { padding: 14px 12px; }
    .stat-card .stat-num { font-size: 24px; }
    .search-box select { display: none; }
    .app-card-header { padding: 16px 16px 12px; }
    .app-progress { padding: 0 16px 16px; }
    .app-card-footer { padding: 12px 16px; }
    .modal-header { padding: 22px 20px 20px; }
    .modal-icon { width: 48px; height: 48px; font-size: 20px; }
    .modal-title { font-size: 16.5px; }
    .modal-body { padding: 18px 20px 22px; }
    .modal-status-section { padding: 14px; }

    /* ── QR Code Verification: mobile ── */
    .modal-qr-section { margin-top: 20px; padding-top: 18px; }
    .qr-verified-box { flex-direction: column; align-items: flex-start; }
    .qr-code-img { width: 108px; height: 108px; }
    .qr-actions { flex-direction: row; flex-wrap: wrap; }

    /* ── Digital Signature: mobile ── */
    .modal-signature-section { margin-top: 20px; padding-top: 18px; }
    .sig-pad-wrap { padding: 8px; }
    .sig-canvas { height: 130px; }
    .sig-actions { gap: 8px; }
    .sig-actions .btn-sig-save,
    .sig-actions .btn-sig-clear {
        flex: 1 1 auto; justify-content: center;
        padding: 11px 14px; font-size: 13px;
    }
    .sig-signed-box {
        flex-direction: column; align-items: stretch;
        text-align: center; gap: 12px; padding: 16px;
    }
    .sig-signed-img { max-width: 140px; max-height: 60px; margin: 0 auto; }
    .sig-signed-meta { text-align: center; }
    .sig-signed-meta .sig-verified { justify-content: center; }
    .sig-signed-meta .sig-meta-line { word-break: break-word; }
    .btn-sig-remove {
        align-self: center;
        border: 1.5px solid #f3c9c7; padding: 8px 18px;
    }

    /* ── Appointment: mobile ── */
    .modal-appointment-section { margin-top: 20px; padding-top: 18px; }
    .appt-form-row { flex-direction: column; gap: 14px; }
    .appt-field { min-width: 0; }
    .appt-field input[type="date"],
    .appt-field select { padding: 11px 12px; font-size: 14px; }
    .btn-appt-request { width: 100%; justify-content: center; padding: 12px 18px; }

    .appt-status-box {
        flex-direction: column; align-items: stretch;
        text-align: center; gap: 12px; padding: 16px;
    }
    .appt-status-icon { margin: 0 auto; }
    .appt-status-meta { text-align: center; }
    .appt-queue-row { justify-content: center; }
    .appt-status-actions {
        flex-direction: row; width: 100%;
    }
    .appt-status-actions button { flex: 1; }
    .btn-appt-cancel, .btn-appt-reschedule {
        width: 100%; padding: 10px 14px;
    }
}
@media (max-width: 900px) { .nav-center { display: none; } .hamburger { display: flex; } .user-profile { display: none; } }
@media (max-width: 600px) { nav { padding: 14px 4%; } .nav-left h2 { font-size: 18px; } .nav-right .login-btn { display: none; } }
    </style>
</head>
<body>

<!-- ═══ NAVBAR ═══ -->
    <nav>
        <div class="nav-left">
            <h2><i class="fa-solid fa-building-columns"></i> E-Gov Portal</h2>
        </div>
        <ul class="nav-center">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="service.php">Services</a></li>
            <li><a href="schedule.php">Online Schedule</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>
        <div class="nav-right">
            <?php if ($isLoggedIn): ?>
            <div class="user-profile" id="userProfile">
                <button class="profile-btn" id="profileBtn" aria-expanded="false" aria-haspopup="true">
                    <div class="profile-avatar"><?= $initials ?></div>
                    <span class="profile-name"><?= $firstName ?></span>
                    <i class="fas fa-chevron-down profile-caret"></i>
                </button>
                <div class="profile-dropdown" id="profileDropdown" role="menu">
                    <div class="dropdown-header">
                        <div class="dh-name"><?= $fullName ?></div>
                        <div class="dh-email"><?= $userEmail ?></div>
                        <div class="dh-badge"><i class="fas fa-circle-check"></i> Active Account</div>
                    </div>
                    <ul class="dropdown-menu">
                        <li><a href="track_application.php" role="menuitem"><i class="fas fa-location-dot"></i> Track Application</a></li>
                        <li class="divider-item"></li>
                        <li class="logout-item"><a href="login.php?logout=1" role="menuitem"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
            <?php else: ?>
            <a href="login.php" class="login-btn">Login <i class="fa-solid fa-arrow-right-to-bracket"></i></a>
            <?php endif; ?>
        </div>
        <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Navigation menu">
        <div class="mobile-menu-backdrop" id="menuBackdrop"></div>
        <div class="mobile-menu-drawer">
            <div class="drawer-header" style="display:flex; align-items:center; justify-content:space-between;">
                <h2><i class="fa-solid fa-building-columns"></i> E-Gov Portal</h2>
                <button class="drawer-close" id="drawerClose" aria-label="Close menu"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php if ($isLoggedIn): ?>
            <div class="drawer-user-section">
                <div class="drawer-avatar"><?= $initials ?></div>
                <div class="drawer-user-info">
                    <div class="dui-name"><?= $fullName ?></div>
                    <div class="dui-email"><?= $userEmail ?></div>
                </div>
            </div>
            <?php endif; ?>
            <ul class="drawer-nav">
                <li><a href="index.php" class="active"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="service.php"><i class="fa-solid fa-list-check"></i> Services</a></li>
                <li><a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Online Schedule</a></li>
                <li><a href="about.php"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
                <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> Contact Us</a></li>
                <?php if ($isLoggedIn): ?>
                <li><a href="track-application.html"><i class="fa-solid fa-location-dot"></i> Track Application</a></li>
                <?php endif; ?>
            </ul>
            <div class="drawer-footer">
                <?php if ($isLoggedIn): ?>
                <a href="login.php?logout=1" class="login-btn" style="background:#e53935;">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
                <?php else: ?>
                <a href="login.php" class="login-btn">Login <i class="fa-solid fa-arrow-right-to-bracket"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

<!-- ═══ PAGE HEADER ═══ -->
<div class="page-header">
    <h1><i class="fa-solid fa-radar" style="margin-right:12px;"></i>Track My Applications</h1>
    <p>Monitor the status of all your government service applications in real time.</p>
</div>

<!-- ═══ SEARCH BAR ═══ -->
<div class="search-wrap">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search by service name or reference number...">
        <select id="filterStatus">
            <option value="all">All Status</option>
            <option value="submitted">Submitted</option>
            <option value="processing">Processing</option>
            <option value="approved">Approved</option>
            <option value="completed">Completed</option>
        </select>
    </div>
</div>

<!-- ═══ MAIN CONTENT ═══ -->
<div class="main-content">

    <!-- Stats -->
    <div class="stats-bar" id="statsBar">
        <div class="stat-card total">
            <div class="stat-num" id="statTotal">0</div>
            <div class="stat-label">Total Applications</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-num" id="statPending">0</div>
            <div class="stat-label">Pending / Submitted</div>
        </div>
        <div class="stat-card approved">
            <div class="stat-num" id="statApproved">0</div>
            <div class="stat-label">Processing / Approved</div>
        </div>
        <div class="stat-card done">
            <div class="stat-num" id="statDone">0</div>
            <div class="stat-label">Completed</div>
        </div>
    </div>

    <!-- Section Head -->
    <div class="section-head">
        <h2><i class="fa-solid fa-list-check"></i> Application History</h2>
        <span id="resultCount"></span>
    </div>

    <!-- Application List -->
    <div class="app-list" id="appList"></div>

    <!-- No Filter Results -->
    <div class="no-filter-results" id="noFilter">
        <i class="fa-solid fa-filter-circle-xmark"></i>
        <p>No applications match your search or filter.</p>
    </div>

    <!-- Empty State (no applications at all) -->
    <div class="empty-state" id="emptyState">
        <div class="empty-icon"><i class="fa-solid fa-folder-open"></i></div>
        <h3>No Applications Found</h3>
        <p>You haven't submitted any government service applications yet. Start by browsing our available services.</p>
        <a href="service.php" class="btn-go-services">
            <i class="fa-solid fa-list-check"></i> Browse Services
        </a>
    </div>

</div>

<!-- ═══ DETAIL MODAL ═══ -->
<div id="detailOverlay" role="dialog" aria-modal="true">
    <div id="detailModal">
        <div class="modal-header">
            <div class="modal-header-pattern"></div>
            <div class="modal-service-info">
                <div class="modal-icon" id="modalIcon"><i class="fa-solid fa-file-circle-check"></i></div>
                <div>
                    <div class="modal-title" id="modalTitle">Service Name</div>
                    <div class="modal-ref" id="modalRef"><i class="fa-solid fa-hashtag"></i><span>Reference: —</span></div>
                </div>
            </div>
            <button class="modal-close" id="modalClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="modal-status-section">
                <h4><i class="fa-solid fa-circle-info"></i> Current Status</h4>
                <div id="modalStatusBadge"></div>
            </div>
            <div class="modal-info-grid" id="modalInfoGrid"></div>
            <div class="modal-timeline">
                <h4><i class="fa-solid fa-timeline"></i> Application Timeline</h4>
                <div id="modalTimeline"></div>
            </div>
            <div class="modal-two-col-sections" id="modalTwoColSections">
                <div class="modal-signature-section" id="modalSignatureSection" style="display:none;"></div>
                <div class="modal-appointment-section" id="modalAppointmentSection" style="display:none;"></div>
            </div>
            <div class="modal-qr-section" id="modalQrSection" style="display:none;"></div>
        </div>
    </div>
</div>

<!-- ═══ QR CODE LIGHTBOX (click-to-enlarge) ═══ -->
<div id="qrLightboxOverlay" role="dialog" aria-modal="true">
    <div class="qr-lightbox-box">
        <button class="qr-lightbox-close" id="qrLightboxClose" aria-label="Close"><i class="fas fa-xmark"></i></button>
        <div class="qr-lightbox-title"><i class="fa-solid fa-qrcode"></i> Verification QR Code</div>
        <div id="qrLightboxImg"></div>
        <div class="qr-lightbox-ref">Reference: <span id="qrLightboxRef"></span></div>
    </div>
</div>

<!-- ═══ FOOTER ═══ -->
<footer>
    <p>Accessible Government Digital System &copy; 2026. All Rights Reserved.</p>
    <p style="margin-top:8px;font-size:12px;opacity:0.55;">Designed for Accessibility &amp; Inclusivity</p>
</footer>

<script src="navbar.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════════
//  CURRENT USER  (merged from egov_storage.php)
//  Injected by PHP — never read from JS / localStorage.
//
//  USER_NS is the localStorage namespace for this user.
//  All keys are stored as:  USER_NS + original_key
//  e.g.  "u42_tesda_reference",  "u42_bir_status"
//
//  Service sub-pages that save application data must also
//  prefix their setItem() calls with USER_NS.  The easiest
//  way is to read it from the session in their own PHP block:
//
//    var USER_NS = 'u<?= $userId ?>_';   // paste this line in each sub-page
//
// ═══════════════════════════════════════════════════════════
const CURRENT_USER_ID = <?= $userId ?>;
const USER_NS         = 'u' + CURRENT_USER_ID + '_';
const CURRENT_USER_NAME = <?= json_encode($fullName) ?>;

// ═══════════════════════════════════════════════════════════
//  SERVICE DEFINITIONS
// ═══════════════════════════════════════════════════════════
const SERVICES = [
    {
        prefix: 'tesda_',
        name:   'TESDA Enrollment',
        icon:   'fa-graduation-cap',
        iconClass: 'icon-tesda',
        refKey: 'tesda_reference',
        nameKey: 'tesda_program',
        dateKey: 'tesda_submittedAt',
        statusKey: 'tesda_status',
        steps: ['Submitted','Under Review','Enrolled','Completed'],
    },
    {
        prefix: 'bir_',
        name:   'BIR TIN Registration',
        icon:   'fa-file-invoice-dollar',
        iconClass: 'icon-bir',
        refKey: 'bir_reference',
        nameKey: 'bir_taxType',
        dateKey: 'bir_submittedAt',
        statusKey: 'bir_status',
        steps: ['Submitted','Verification','Processing','TIN Issued'],
    },
    {
        prefix: 'dswd_',
        name:   'DSWD Assistance',
        icon:   'fa-hand-holding-heart',
        iconClass: 'icon-dswd',
        refKey: 'dswd_reference',
        nameKey: 'dswd_assistanceType',
        dateKey: 'dswd_submittedAt',
        statusKey: 'dswd_status',
        steps: ['Submitted','Assessment','Approved','Released'],
    },
    {
        prefix: 'vid_',
        name:   'Valid ID Application',
        icon:   'fa-id-card',
        iconClass: 'icon-vid',
        refKey: 'vid_reference',
        nameKey: 'vid_idType',
        dateKey: 'vid_submittedAt',
        statusKey: 'vid_status',
        steps: ['Submitted','Verification','Processing','ID Ready'],
    },
    {
        prefix: 'smf_',
        name:   'SM Foundation Scholarship',
        icon:   'fa-book-open',
        iconClass: 'icon-smf',
        refKey: 'smf_reference',
        nameKey: 'smf_course',
        dateKey: 'smf_submittedAt',
        statusKey: 'smf_status',
        steps: ['Submitted','Initial Review','Final Evaluation','Awarded'],
    },
    {
        prefix: 'ched_',
        name:   'CHED Scholarship',
        icon:   'fa-university',
        iconClass: 'icon-ched',
        refKey: 'ched_reference',
        nameKey: 'ched_course',
        dateKey: 'ched_submittedAt',
        statusKey: 'ched_status',
        steps: ['Submitted','Evaluation','Endorsed','Granted'],
    },
    {
        prefix: 'comelec_',
        name:   'COMELEC Voter Registration',
        icon:   'fa-vote-yea',
        iconClass: '',
        refKey: 'comelec_reference',
        nameKey: 'comelec_regType',
        dateKey: 'comelec_submittedAt',
        statusKey: 'comelec_status',
        steps: ['Submitted','Under Review','Office Visit','Registered'],
    },
    {
        prefix: 'psa_',
        name:   'PSA Certificate Request',
        icon:   'fa-scroll',
        iconClass: '',
        refKey: 'psa_reference',
        nameKey: 'psa_certType',
        dateKey: 'psa_submittedAt',
        statusKey: 'psa_status',
        steps: ['Submitted','Verification','Printing','Released'],
    },
    {
        prefix: 'lgu_',
        name:   'LGU Online Services',
        icon:   'fa-landmark',
        iconClass: '',
        refKey: 'lgu_reference',
        nameKey: 'lgu_service',
        dateKey: 'lgu_submittedAt',
        statusKey: 'lgu_status',
        steps: ['Submitted','Verification','Processing','Ready for Pickup'],
    },
    {
        prefix: 'lto_',
        name:   "LTO Driver's License",
        icon:   'fa-car',
        iconClass: '',
        refKey: 'lto_reference',
        nameKey: 'lto_licenseType',
        dateKey: 'lto_submittedAt',
        statusKey: 'lto_status',
        steps: ['Submitted','Verification','Appointment','License Issued'],
    },
    {
        prefix: 'aha_',
        name:   'AHA Learning Center',
        icon:   'fa-chalkboard-teacher',
        iconClass: '',
        refKey: 'aha_reference',
        nameKey: 'aha_program',
        dateKey: 'aha_submittedAt',
        statusKey: 'aha_status',
        steps: ['Submitted','Under Review','Slot Confirmed','Enrolled'],
    },
];

// ═══════════════════════════════════════════════════════════
//  NAVBAR — driven entirely by PHP session (no JS simulation)
// ═══════════════════════════════════════════════════════════
function initNavbar() {
    // The navbar HTML is already rendered by PHP with the correct user data.
    // We only need to wire up the profile-dropdown toggle here.
}

// ═══════════════════════════════════════════════════════════
//  COLLECT APPLICATIONS
//  vid_ (Valid ID) is backed by a real database table, fetched via
//  fetchRealVidStatus() below. Every other service is still a
//  client-side localStorage simulation (no backend yet).
// ═══════════════════════════════════════════════════════════
function collectApplications(vidRows, tesdaRows, chedRows) {
    const apps = [];

    SERVICES.forEach(svc => {
        if (svc.prefix === 'vid_' && vidRows && vidRows.length > 0) {
            // Real records from the `validid` table — rows already come back
            // ordered newest-first. Each row is its OWN application card: if
            // the applicant submitted more than one ID type, every type gets
            // its own entry instead of only the latest one being shown and
            // the rest silently dropped/merged.
            vidRows.forEach(row => {
                apps.push({
                    service: svc,
                    ref: row.reference,
                    detail: row.idtype || '—',
                    date: row.datesubmit ? new Date(row.datesubmit.replace(' ', 'T')) : new Date(),
                    status: row.status,
                    raw: svc,
                    isReal: true,
                    id_validid: row.id_validid,
                });
            });
            return;
        }

        if (svc.prefix === 'tesda_' && tesdaRows && tesdaRows.length > 0) {
            // Real records from the `tesda` table — rows already come back
            // ordered newest-first. Each row is its OWN application card: if
            // the applicant enrolled in more than one course/type, every
            // course gets its own entry instead of only the latest one being
            // shown and the rest silently dropped/merged.
            // Only populated once tesda_schema_update.sql has been run AND
            // the applicant was logged in (id_application set) when they submitted.
            tesdaRows.forEach(row => {
                apps.push({
                    service: svc,
                    ref: row.reference,
                    detail: row.course || '—',
                    date: row.datesubmit ? new Date(row.datesubmit.replace(' ', 'T')) : new Date(),
                    status: row.status,
                    raw: svc,
                    isReal: true,
                    id_tesda: row.id_tesda,
                });
            });
            return;
        }

        if (svc.prefix === 'ched_' && chedRows && chedRows.length > 0) {
            // Real records from the `ched` table — rows already come back
            // ordered newest-first. Each row is its OWN application card: if
            // the applicant applied for more than one degree/type, every
            // degree gets its own entry instead of only the latest one being
            // shown and the rest silently dropped/merged.
            // Only populated once ched_schema_update.sql has been run AND
            // the applicant was logged in (id_application set) when they submitted.
            chedRows.forEach(row => {
                apps.push({
                    service: svc,
                    ref: row.reference,
                    detail: row.course || '—',
                    date: row.datesubmit ? new Date(row.datesubmit.replace(' ', 'T')) : new Date(),
                    status: row.status,
                    raw: svc,
                    isReal: true,
                    id_ched: row.id_ched,
                });
            });
            return;
        }

        // Every key is stored as  USER_NS + original_key  (e.g. "u42_tesda_reference")
        const scopedPrefix = USER_NS + svc.prefix;
        const keys = Object.keys(localStorage).filter(k => k.startsWith(scopedPrefix));
        if (keys.length === 0) return;

        const ref     = localStorage.getItem(USER_NS + svc.refKey)  || generateFakeRef(svc.prefix);
        const detail  = localStorage.getItem(USER_NS + svc.nameKey) || '—';
        const dateRaw = localStorage.getItem(USER_NS + svc.dateKey);
        const date    = dateRaw ? new Date(dateRaw) : new Date();

        const statusKey = svc.statusKey || (svc.prefix + 'status');
        let status = localStorage.getItem(USER_NS + statusKey) || 'submitted';

        apps.push({ service: svc, ref, detail, date, status, raw: svc, isReal: false });
    });

    // Each service's own rows already arrive newest-first, but the services
    // are appended one after another above, so the combined list isn't in
    // date order overall. Sort everything together by date, present to past,
    // so the most recently submitted application (of any service) leads.
    apps.sort((a, b) => b.date - a.date);

    return apps;
}

// Fetches the logged-in user's real Valid ID application rows from the
// database via the JSON endpoint defined at the top of this PHP file.
async function fetchRealVidStatus() {
    try {
        const res = await fetch('track_application.php?ajax=vid_status', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return [];
        const data = await res.json();
        if (data.error) console.warn('vid_status:', data.error);
        return data.rows || [];
    } catch (e) {
        console.error('Could not load live Valid ID status:', e);
        return [];
    }
}

// Fetches the logged-in user's real TESDA application rows from the
// database via the JSON endpoint defined at the top of this PHP file.
async function fetchRealTesdaStatus() {
    try {
        const res = await fetch('track_application.php?ajax=tesda_status', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return [];
        const data = await res.json();
        if (data.error) console.warn('tesda_status:', data.error);
        return data.rows || [];
    } catch (e) {
        console.error('Could not load live TESDA status:', e);
        return [];
    }
}

// Fetches the logged-in user's real CHED Scholarship application rows
// from the database via the JSON endpoint defined at the top of this file.
async function fetchRealChedStatus() {
    try {
        const res = await fetch('track_application.php?ajax=ched_status', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return [];
        const data = await res.json();
        if (data.error) console.warn('ched_status:', data.error);
        return data.rows || [];
    } catch (e) {
        console.error('Could not load live CHED status:', e);
        return [];
    }
}

function generateFakeRef(prefix) {
    const code = prefix.replace('_','').toUpperCase().slice(0,4);
    return code + '-2026-' + Math.floor(10000 + Math.random() * 90000);
}

// ═══════════════════════════════════════════════════════════
//  DETERMINE STEP INDEX
//  Mirrors the substring-based matching used in admin_application_detail.php
//  so real DB values like "Under Review" or "Rejected" (not just the old
//  simulated lowercase keys) map to the right step.
// ═══════════════════════════════════════════════════════════
function normalizeStatus(status) {
    const s = (status || '').toLowerCase();
    if (s.includes('reject') || s.includes('denied'))   return 'rejected';
    if (s.includes('complete') || s.includes('release') || s.includes('ready') ||
        s.includes('issue') || s.includes('grant') || s.includes('award') || s.includes('done'))
        return 'completed';
    if (s.includes('approve'))                           return 'approved';
    if (s.includes('renewal'))                            return 'processing';
    if (s.includes('review') || s.includes('process'))   return 'processing';
    return 'submitted';
}

function getStepIndex(status, totalSteps) {
    switch (normalizeStatus(status)) {
        // Fully finished — every step (including the last one) should show
        // as done/checked, not stuck on a spinning "in progress" icon.
        case 'rejected':
        case 'completed':  return totalSteps;
        case 'approved':   return Math.max(0, totalSteps - 2);
        case 'processing': return 1;
        default:            return 0;
    }
}

function statusLabel(status) {
    const s = (status || '').toLowerCase();
    const labels = {
        submitted: 'Submitted', pending: 'Pending',
        processing: 'Processing', review: 'Under Review',
        approved: 'Approved', completed: 'Completed',
        done: 'Completed', issued: 'Completed',
        granted: 'Granted', awarded: 'Awarded', released: 'Released',
        rejected: 'Rejected',
    };
    if (labels[s]) return labels[s];
    // Already a human-readable string straight from the DB (e.g. "Under Review") — title-case it.
    return (status || '—').replace(/\w\S*/g, t => t.charAt(0).toUpperCase() + t.slice(1).toLowerCase());
}

function statusBadgeClass(status) {
    const map = {
        submitted: 'badge-submitted', processing: 'badge-processing',
        approved: 'badge-approved', completed: 'badge-completed',
        rejected: 'badge-rejected',
    };
    return map[normalizeStatus(status)];
}

// ═══════════════════════════════════════════════════════════
//  BUILD PROGRESS STEPPER HTML
// ═══════════════════════════════════════════════════════════
function buildStepper(steps, activeIndex) {
    let html = '<div class="progress-stepper">';
    steps.forEach((label, i) => {
        let circleClass = '', labelClass = '', icon = (i + 1).toString();
        if (i < activeIndex)      { circleClass = 'done';   labelClass = 'done';   icon = '<i class="fas fa-check"></i>'; }
        else if (i === activeIndex){ circleClass = 'active'; labelClass = 'active'; }

        html += `<div class="step-node">
            <div class="step-circle ${circleClass}">${icon}</div>
            <div class="step-label ${labelClass}">${label}</div>
        </div>`;

        if (i < steps.length - 1) {
            html += `<div class="step-line ${i < activeIndex ? 'done' : ''}"></div>`;
        }
    });
    html += '</div>';
    return html;
}

// ═══════════════════════════════════════════════════════════
//  RENDER CARDS
// ═══════════════════════════════════════════════════════════
let allApps = [];
let cachedVidRows = [];
let cachedTesdaRows = [];
let cachedChedRows = [];

function renderCards(apps) {
    const list = document.getElementById('appList');
    const noFilter = document.getElementById('noFilter');

    list.innerHTML = '';

    if (apps.length === 0) {
        noFilter.style.display = 'block';
        document.getElementById('resultCount').textContent = '';
        return;
    }

    noFilter.style.display = 'none';
    document.getElementById('resultCount').textContent = `${apps.length} application${apps.length !== 1 ? 's' : ''} found`;

    apps.forEach((app, idx) => {
        const svc = app.service;
        const stepIdx = getStepIndex(app.status, svc.steps.length);
        const dateStr = app.date.toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
        const badgeClass = statusBadgeClass(app.status);
        const label = statusLabel(app.status);

        const card = document.createElement('div');
        card.className = 'app-card';
        card.style.animationDelay = (idx * 0.07) + 's';
        card.innerHTML = `
            <div class="app-card-header">
                <div class="app-service-icon ${svc.iconClass}"><i class="fas ${svc.icon}"></i></div>
                <div class="app-info">
                    <div class="app-service-name">${svc.name}</div>
                    <div class="app-detail-line">${app.detail}</div>
                    <div class="app-ref">Reference: <span>${app.ref}</span></div>
                </div>
                <div class="app-status-badge ${badgeClass}">
                    <i class="fas fa-circle" style="font-size:7px;"></i> ${label}
                </div>
            </div>
            <div class="app-progress">
                ${buildStepper(svc.steps, stepIdx)}
            </div>
            <div class="app-card-footer">
                <div class="app-meta">
                    <div class="app-meta-item"><i class="fas fa-calendar-alt"></i> Submitted: ${dateStr}</div>
                    <div class="app-meta-item"><i class="fas fa-layer-group"></i> Step ${stepIdx + 1} of ${svc.steps.length}</div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn-details" data-idx="${allApps.indexOf(app)}">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                    <button class="btn-remove" data-prefix="${svc.prefix}" data-real="${app.isReal ? '1' : '0'}" data-rec-id="${app.isReal ? (app.id_validid || app.id_tesda || app.id_ched || '') : ''}">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </div>
            </div>
        `;
        list.appendChild(card);
    });

    // Bind detail buttons
    list.querySelectorAll('.btn-details').forEach(btn => {
        btn.addEventListener('click', function() {
            openModal(allApps[parseInt(this.dataset.idx)]);
        });
    });

    // Bind remove buttons
    list.querySelectorAll('.btn-remove').forEach(btn => {
        btn.addEventListener('click', async function() {
            const prefix = this.dataset.prefix;
            const isReal = this.dataset.real === '1';

            if (isReal) {
                const recId = this.dataset.recId;
                if (!recId) return;
                if (!confirm('Remove this application? This deletes the actual record and cannot be undone.')) return;

                this.disabled = true;
                try {
                    const res = await fetch('track_application.php?ajax=remove_application', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new URLSearchParams({ prefix, id: recId }).toString(),
                    });
                    const data = await res.json();
                    if (!data.success) {
                        alert(data.error || 'Could not remove this application.');
                        this.disabled = false;
                        return;
                    }
                } catch (e) {
                    alert('Could not reach the server to remove this application.');
                    this.disabled = false;
                    return;
                }

                // Drop it from the cached rows so the next collectApplications() omits it
                if (prefix === 'vid_') {
                    cachedVidRows = cachedVidRows.filter(r => String(r.id_validid) !== String(recId));
                } else if (prefix === 'tesda_') {
                    cachedTesdaRows = cachedTesdaRows.filter(r => String(r.id_tesda) !== String(recId));
                } else if (prefix === 'ched_') {
                    cachedChedRows = cachedChedRows.filter(r => String(r.id_ched) !== String(recId));
                }
            } else {
                if (!confirm('Remove this application from your tracking list?')) return;
                // Remove only THIS user's keys for this service
                const scopedPrefix = USER_NS + prefix;
                Object.keys(localStorage)
                    .filter(k => k.startsWith(scopedPrefix))
                    .forEach(k => localStorage.removeItem(k));
            }

            // Refresh
            allApps = collectApplications(cachedVidRows, cachedTesdaRows, cachedChedRows);
            updateStats(allApps);
            if (allApps.length === 0) {
                document.getElementById('emptyState').classList.add('visible');
                document.getElementById('statsBar').style.display = 'none';
                document.querySelector('.section-head').style.display = 'none';
                list.innerHTML = '';
                document.getElementById('noFilter').style.display = 'none';
            } else {
                applyFilter();
            }
        });
    });
}

// ═══════════════════════════════════════════════════════════
//  DETAIL MODAL
// ═══════════════════════════════════════════════════════════
function openModal(app) {
    const svc = app.service;
    const stepIdx = getStepIndex(app.status, svc.steps.length);
    const dateStr = app.date.toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' });
    const badgeClass = statusBadgeClass(app.status);
    const label = statusLabel(app.status);

    // Header
    document.querySelector('.modal-icon').innerHTML = `<i class="fas ${svc.icon}"></i>`;
    document.querySelector('.modal-icon').className = `modal-icon ${svc.iconClass}`;
    document.getElementById('modalTitle').textContent = (app.detail && app.detail !== '—')
        ? `${svc.name} — ${app.detail}`
        : svc.name;
    document.getElementById('modalRef').innerHTML = `<i class="fa-solid fa-hashtag"></i><span>Reference: ${app.ref}</span>`;

    // Status badge
    document.getElementById('modalStatusBadge').innerHTML =
        `<span class="app-status-badge ${badgeClass}" style="font-size:13px;padding:8px 18px;">
            <i class="fas fa-circle" style="font-size:8px;"></i> ${label}
        </span>`;

    // Info grid
    document.getElementById('modalInfoGrid').innerHTML = `
        <div class="info-item">
            <div class="info-label">Service</div>
            <div class="info-value">${svc.name}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Reference No.</div>
            <div class="info-value primary">${app.ref}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Date Submitted</div>
            <div class="info-value">${dateStr}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Detail / Type</div>
            <div class="info-value">${app.detail}</div>
        </div>
    `;

    // Timeline
    let timelineHTML = '';
    svc.steps.forEach((step, i) => {
        let dotClass = 'pending', icon = (i + 1).toString();
        let desc = 'Awaiting processing';
        if (i < stepIdx)       { dotClass = 'done';   icon = '<i class="fas fa-check"></i>'; desc = 'Completed'; }
        else if (i === stepIdx){ dotClass = 'active';  icon = '<i class="fas fa-spinner fa-spin"></i>'; desc = 'Currently at this stage'; }

        timelineHTML += `
            <div class="timeline-item">
                <div class="timeline-dot ${dotClass}">${icon}</div>
                <div class="timeline-text">
                    <strong>${step}</strong>
                    <span>${desc}</span>
                </div>
            </div>`;
    });
    document.getElementById('modalTimeline').innerHTML = timelineHTML;

    // Digital signature (only for Completed / Approved applications)
    renderSignatureSection(app);

    // QR Code verification (only unlocked once the signature is done)
    renderQRSection(app);

    // Appointment scheduling (only unlocked once the signature is done)
    renderAppointmentSection(app);
    syncTwoColSections();

    // Show
    document.getElementById('detailOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('detailOverlay').classList.remove('active');
    document.body.style.overflow = '';
}

// ═══════════════════════════════════════════════════════════
//  DIGITAL SIGNATURE (e-sign pad, shown for Completed/Approved apps)
// ═══════════════════════════════════════════════════════════
function isSignatureEligible(status) {
    const n = normalizeStatus(status);
    return n === 'approved' || n === 'completed';
}

function sigKeys(svc) {
    const base = USER_NS + svc.prefix;
    return {
        data:   base + 'signature',
        signedAt: base + 'signedAt',
        signedBy: base + 'signedBy',
        code:   base + 'signatureCode',
    };
}

// Keeps the Signature / Appointment two-column row from leaving a blank
// column when only one of the two sections is currently visible.
function syncTwoColSections() {
    const wrap = document.getElementById('modalTwoColSections');
    if (!wrap) return;
    const sig  = document.getElementById('modalSignatureSection');
    const appt = document.getElementById('modalAppointmentSection');
    const sigVisible  = sig  && sig.style.display  !== 'none' && sig.innerHTML.trim()  !== '';
    const apptVisible = appt && appt.style.display !== 'none' && appt.innerHTML.trim() !== '';
    wrap.classList.toggle('single-col', !(sigVisible && apptVisible));
}

function generateVerificationCode(prefix) {
    const rand = Math.random().toString(36).slice(2, 8).toUpperCase();
    return 'SIG-' + prefix.replace('_', '').toUpperCase() + '-' + rand;
}

function renderSignatureSection(app) {
    const section = document.getElementById('modalSignatureSection');
    const svc = app.service;

    if (!isSignatureEligible(app.status)) {
        section.style.display = 'none';
        section.innerHTML = '';
        return;
    }
    section.style.display = 'block';

    const keys = sigKeys(svc);
    const existingSig = localStorage.getItem(keys.data);

    if (existingSig) {
        const signedAt = localStorage.getItem(keys.signedAt);
        const signedBy = localStorage.getItem(keys.signedBy) || fullNameForSig();
        const code      = localStorage.getItem(keys.code) || '—';
        const dateStr   = signedAt ? new Date(signedAt).toLocaleString('en-PH', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }) : '—';

        section.innerHTML = `
            <h4><i class="fa-solid fa-signature"></i> Digital Signature</h4>
            <div class="sig-signed-box">
                <img class="sig-signed-img" src="${existingSig}" alt="Applicant signature">
                <div class="sig-signed-meta">
                    <div class="sig-verified"><i class="fas fa-shield-check"></i> Digitally Signed &amp; Verified</div>
                    <div class="sig-by">${signedBy}</div>
                    <div class="sig-meta-line">Signed ${dateStr}</div>
                    <div class="sig-meta-line">Verification code: <span class="sig-code">${code}</span></div>
                </div>
                <button class="btn-sig-remove" id="btnSigRemove"><i class="fas fa-rotate-left"></i> Re-sign</button>
            </div>
        `;
        document.getElementById('btnSigRemove').addEventListener('click', function () {
            if (!confirm('Remove this signature? You will need to sign again.')) return;
            localStorage.removeItem(keys.data);
            localStorage.removeItem(keys.signedAt);
            localStorage.removeItem(keys.signedBy);
            localStorage.removeItem(keys.code);
            renderSignatureSection(app);
            renderQRSection(app);
            renderAppointmentSection(app);
            syncTwoColSections();
        });
        return;
    }

    // No signature yet — show the signing pad
    section.innerHTML = `
        <h4><i class="fa-solid fa-signature"></i> Digital Signature</h4>
        <p class="sig-hint">This application is ${statusLabel(app.status).toLowerCase()}. Sign below to digitally endorse and finalize this record.</p>
        <div class="sig-pad-wrap">
            <canvas class="sig-canvas" id="sigCanvas"></canvas>
            <div class="sig-baseline">Sign above using your mouse, stylus, or finger</div>
        </div>
        <div class="sig-actions">
            <button class="btn-sig-save" id="btnSigSave" disabled><i class="fas fa-signature"></i> Save Signature</button>
            <button class="btn-sig-clear" id="btnSigClear"><i class="fas fa-eraser"></i> Clear</button>
        </div>
    `;

    initSignaturePad(svc, app);
}

function fullNameForSig() {
    return CURRENT_USER_NAME || 'Applicant';
}

function initSignaturePad(svc, app) {
    const canvas = document.getElementById('sigCanvas');
    const ctx = canvas.getContext('2d');
    const saveBtn = document.getElementById('btnSigSave');
    const clearBtn = document.getElementById('btnSigClear');

    let drawing = false, hasStroke = false;
    let lastX = 0, lastY = 0;

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.scale(ratio, ratio);
        ctx.lineWidth = 2.4;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#1a2540';
    }
    // The signature pad is built while #detailOverlay is still display:none
    // (openModal() renders this section, then makes the overlay visible
    // afterward). Sizing the canvas synchronously here would read a 0x0
    // bounding rect and leave the canvas with zero width/height, so the
    // very first signature attempt after opening the modal silently fails
    // to draw. Defer to the next frame, by which point the overlay is
    // visible and getBoundingClientRect() returns real dimensions.
    requestAnimationFrame(resizeCanvas);

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const point = e.touches ? e.touches[0] : e;
        return { x: point.clientX - rect.left, y: point.clientY - rect.top };
    }

    function start(e) {
        e.preventDefault();
        drawing = true;
        const pos = getPos(e);
        lastX = pos.x; lastY = pos.y;
    }
    function move(e) {
        if (!drawing) return;
        e.preventDefault();
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        lastX = pos.x; lastY = pos.y;
        if (!hasStroke) { hasStroke = true; saveBtn.disabled = false; }
    }
    function end() { drawing = false; }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    window.addEventListener('mouseup', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', end);

    clearBtn.addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasStroke = false;
        saveBtn.disabled = true;
    });

    saveBtn.addEventListener('click', function () {
        if (!hasStroke) return;
        const dataUrl = canvas.toDataURL('image/png');
        const keys = sigKeys(svc);
        localStorage.setItem(keys.data, dataUrl);
        localStorage.setItem(keys.signedAt, new Date().toISOString());
        localStorage.setItem(keys.signedBy, fullNameForSig());
        localStorage.setItem(keys.code, generateVerificationCode(svc.prefix));
        renderSignatureSection(app);
        renderQRSection(app);
        renderAppointmentSection(app);
        syncTwoColSections();
    });
}

// ═══════════════════════════════════════════════════════════
//  QR CODE VERIFICATION
//  Final step of the tracking flow: Tracking Status → Review →
//  Digital Signature → Approval → QR Code.
//
//  The QR code does NOT carry the proof itself — it only carries a
//  reference number + a random verify_token. Scanning it opens the
//  public verify_qr.php page, which looks that pair up in the
//  `qr_verifications` table (written server-side by the
//  ajax=save_verification endpoint above) and reports VALID or
//  INVALID / NOT FOUND from the actual database record — never from
//  data embedded in the image. A copied/edited QR with a made-up or
//  mismatched token simply won't resolve to a record.
// ═══════════════════════════════════════════════════════════
function qrKeys(svc) {
    const base = USER_NS + svc.prefix;
    return {
        token: base + 'qrToken',
        generatedAt: base + 'qrGeneratedAt',
    };
}

// Public verification page lives alongside this file.
function verifyPageUrl() {
    return window.location.origin + window.location.pathname.replace(/track_application\.php$/, 'verify_qr.php');
}

// Saves/refreshes the server-side verification record for this
// application and returns its verify_token (reused across calls for
// the same reference — see the ajax handler's ON DUPLICATE KEY note).
async function saveVerificationRecord(app, svc, sigCode, signedBy, signedAt, sigImage) {
    const body = new URLSearchParams({
        prefix: svc.prefix,
        reference: app.ref,
        applicant_name: signedBy || fullNameForSig(),
        status: app.status || '',
        signature_code: sigCode || '',
        signature_image: sigImage || '',
        signed_at: signedAt || new Date().toISOString(),
    });
    try {
        const res = await fetch('track_application.php?ajax=save_verification', { method: 'POST', body });
        const data = await res.json();
        return data;
    } catch (e) {
        console.error('Could not save verification record:', e);
        return { error: 'Network error while saving verification record' };
    }
}

async function renderQRSection(app) {
    const section = document.getElementById('modalQrSection');
    if (!section) return;
    const svc = app.service;

    if (!isSignatureEligible(app.status)) {
        // Not yet approved — the whole QR step is out of scope, stay hidden.
        section.style.display = 'none';
        section.innerHTML = '';
        syncTwoColSections();
        return;
    }
    section.style.display = 'block';

    const sKeys = sigKeys(svc);
    const existingSig = localStorage.getItem(sKeys.data);

    if (!existingSig) {
        // Approved, but the applicant hasn't signed yet — QR comes after signature.
        syncTwoColSections();
        section.innerHTML = `
            <h4><i class="fa-solid fa-qrcode"></i> QR Code Verification</h4>
            <div class="qr-locked-box">
                <i class="fas fa-lock"></i>
                <span>Sign this application above to generate its verification QR code.</span>
            </div>
        `;
        return;
    }

    section.innerHTML = `
        <h4><i class="fa-solid fa-qrcode"></i> QR Code Verification</h4>
        <div class="qr-locked-box"><i class="fas fa-spinner fa-spin"></i> <span>Registering this record with the verification database…</span></div>
    `;

    const sigCode  = localStorage.getItem(sKeys.code);
    const signedBy = localStorage.getItem(sKeys.signedBy) || fullNameForSig();
    const signedAt = localStorage.getItem(sKeys.signedAt);

    const qKeys = qrKeys(svc);
    let token = localStorage.getItem(qKeys.token);
    const result = await saveVerificationRecord(app, svc, sigCode, signedBy, signedAt, existingSig);

    if (result.error) {
        section.innerHTML = `
            <h4><i class="fa-solid fa-qrcode"></i> QR Code Verification</h4>
            <div class="qr-locked-box"><i class="fas fa-triangle-exclamation"></i> <span>Could not register this record for verification: ${result.error}</span></div>
        `;
        return;
    }

    token = result.token || token;
    localStorage.setItem(qKeys.token, token);
    if (!localStorage.getItem(qKeys.generatedAt)) {
        localStorage.setItem(qKeys.generatedAt, new Date().toISOString());
    }
    const generatedAt = localStorage.getItem(qKeys.generatedAt);
    const generatedStr = generatedAt ? new Date(generatedAt).toLocaleString('en-PH', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }) : '—';

    const verifyUrl = verifyPageUrl() + '?ref=' + encodeURIComponent(app.ref) + '&token=' + encodeURIComponent(token);

    section.innerHTML = `
        <h4><i class="fa-solid fa-qrcode"></i> QR Code Verification</h4>
        <p class="qr-hint">Present this QR code at the office. Scanning it opens a live database lookup — staff see VALID or INVALID straight from our records, not from the code itself.</p>
        <div class="qr-verified-box">
            <div class="qr-code-img" id="qrCodeImg"></div>
            <div class="qr-verified-meta">
                <div class="qr-verified-label"><i class="fas fa-shield-check"></i> Registered &amp; Ready to Scan</div>
                <div class="qr-meta-line">Reference: <span class="qr-code-text">${app.ref}</span></div>
                <div class="qr-meta-line">Signature code: <span class="qr-code-text">${sigCode || '—'}</span></div>
                <div class="qr-meta-line">Generated ${generatedStr}</div>
            </div>
            <div class="qr-actions">
                <button class="btn-qr-download" id="btnQrDownload"><i class="fas fa-download"></i> Download</button>
                <button class="btn-qr-regenerate" id="btnQrRegenerate"><i class="fas fa-rotate"></i> Refresh</button>
            </div>
        </div>
    `;

    // Generated client-side (no external image API to fail/load) —
    // the QR encodes verifyUrl the same as before; only the rendering
    // method changed.
    new QRCode(document.getElementById('qrCodeImg'), {
        text: verifyUrl,
        width: 220,
        height: 220,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });

    // Click the small QR to view it big in a lightbox
    document.getElementById('qrCodeImg').addEventListener('click', function () {
        openQrLightbox(verifyUrl, app.ref);
    });

    document.getElementById('btnQrDownload').addEventListener('click', function () {
        const qrImgEl = document.querySelector('#qrCodeImg img, #qrCodeImg canvas');
        if (!qrImgEl) return;
        const dataUrl = qrImgEl.tagName === 'CANVAS' ? qrImgEl.toDataURL('image/png') : qrImgEl.src;
        const a = document.createElement('a');
        a.href = dataUrl;
        a.download = (app.ref || 'application') + '-qr.png';
        document.body.appendChild(a);
        a.click();
        a.remove();
    });

    document.getElementById('btnQrRegenerate').addEventListener('click', function () {
        renderQRSection(app);
        syncTwoColSections();
    });
    syncTwoColSections();
}

// Big-view lightbox for the verification QR — reuses the same
// verifyUrl so the enlarged code scans to the exact same record.
function openQrLightbox(verifyUrl, ref) {
    const imgHost = document.getElementById('qrLightboxImg');
    imgHost.innerHTML = '';
    new QRCode(imgHost, {
        text: verifyUrl,
        width: 260,
        height: 260,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
    document.getElementById('qrLightboxRef').textContent = ref || '—';
    document.getElementById('qrLightboxOverlay').classList.add('active');
}

function closeQrLightbox() {
    document.getElementById('qrLightboxOverlay').classList.remove('active');
}

// ═══════════════════════════════════════════════════════════
//  APPOINTMENT SCHEDULING
//  Unlocked only once the applicant has digitally signed.
//  Applicant requests a date/time; it sits as "pending" until an
//  admin confirms it (that confirmation step lives in the admin panel).
// ═══════════════════════════════════════════════════════════
const APPT_TIME_SLOTS = [
    '9:00 AM – 10:00 AM',
    '10:00 AM – 11:00 AM',
    '11:00 AM – 12:00 PM',
    '1:00 PM – 2:00 PM',
    '2:00 PM – 3:00 PM',
    '3:00 PM – 4:00 PM',
];

// Average minutes it takes the office to process one applicant, used only
// to turn a queue number into a rough "estimated wait" for display.
const APPT_MINUTES_PER_APPLICANT = 12;

function apptKeys(svc) {
    const base = USER_NS + svc.prefix;
    return {
        date:        base + 'apptDate',
        time:        base + 'apptTime',
        status:      base + 'apptStatus',      // 'pending' | 'confirmed'
        requestedAt: base + 'apptRequestedAt',
        confirmedAt: base + 'apptConfirmedAt',
        note:        base + 'apptNote',
        queueNumber: base + 'apptQueueNumber',
    };
}

// ═══════════════════════════════════════════════════════════
//  AUTOMATIC QUEUE NUMBER + ESTIMATED WAIT
//  Queue numbers are handed out from a counter scoped to
//  date + time slot + service (kind of application), so booking
//  different agency services (e.g. VID vs TESDA) in the same slot
//  each get their own independent queue instead of pulling
//  sequential tickets from a combined line.
// ═══════════════════════════════════════════════════════════
function nextQueueNumber(dateVal, timeVal, svcPrefix) {
    const counterKey = 'apptQueueCounter_' + dateVal + '_' + timeVal + '_' + svcPrefix;
    const next = parseInt(localStorage.getItem(counterKey) || '0', 10) + 1;
    localStorage.setItem(counterKey, String(next));
    return next;
}

function formatDaysUntil(dateVal) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const target = new Date(dateVal + 'T00:00:00');
    const diffDays = Math.round((target - today) / (1000 * 60 * 60 * 24));
    if (diffDays <= 0) return 'today';
    if (diffDays === 1) return 'tomorrow';
    return `in ${diffDays} days`;
}

function formatQueueWait(queueNumber) {
    const minutes = queueNumber * APPT_MINUTES_PER_APPLICANT;
    if (minutes < 60) return `~${minutes} min`;
    const hrs = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return mins > 0 ? `~${hrs}h ${mins}m` : `~${hrs}h`;
}

function renderQueueRow(queueNumberRaw, dateVal) {
    if (!queueNumberRaw) return '';
    const queueNumber = parseInt(queueNumberRaw, 10);
    return `
        <div class="appt-queue-row">
            <span class="appt-queue-badge"><i class="fas fa-hashtag"></i> Queue #${queueNumber}</span>
            <span class="appt-wait-est">Estimated wait at the office: ${formatQueueWait(queueNumber)}</span>
        </div>
        <div class="appt-days-until">Appointment is ${formatDaysUntil(dateVal)}</div>
    `;
}

// Asks the server for a real, cross-applicant queue number (see the
// ajax=queue_next endpoint at the top of this file). If the request
// fails — offline, or appt_queue_schema.sql hasn't been run yet —
// falls back to the local-only counter so the feature still works.
async function assignQueueNumber(dateVal, timeVal, svcPrefix) {
    try {
        const res = await fetch('track_application.php?ajax=queue_next', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new URLSearchParams({ date: dateVal, time: timeVal, prefix: svcPrefix }).toString(),
        });
        if (res.ok) {
            const data = await res.json();
            if (data.queue_number) {
                if (data.logError) console.warn('queue_next:', data.logError);
                return data.queue_number;
            }
            if (data.error) console.warn('queue_next:', data.error);
        }
    } catch (e) {
        console.warn('Could not reach server queue counter, using local counter instead:', e);
    }
    return nextQueueNumber(dateVal, timeVal, svcPrefix);
}

function renderAppointmentSection(app) {
    const section = document.getElementById('modalAppointmentSection');
    const svc = app.service;

    // Locked until the applicant has signed
    const hasSignature = !!localStorage.getItem(sigKeys(svc).data);
    if (!hasSignature) {
        section.style.display = 'none';
        section.innerHTML = '';
        syncTwoColSections();
        return;
    }
    section.style.display = 'block';
    syncTwoColSections();

    const keys = apptKeys(svc);
    const status = localStorage.getItem(keys.status);

    if (status === 'pending' || status === 'confirmed') {
        const dateVal = localStorage.getItem(keys.date);
        const timeVal = localStorage.getItem(keys.time);
        const queueNumberVal = localStorage.getItem(keys.queueNumber);
        const whenStr = dateVal
            ? new Date(dateVal + 'T00:00:00').toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
            : '—';

        if (status === 'confirmed') {
            const note = localStorage.getItem(keys.note);
            section.innerHTML = `
                <h4><i class="fa-solid fa-calendar-check"></i> Appointment</h4>
                <div class="appt-status-box confirmed">
                    <div class="appt-status-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="appt-status-meta">
                        <div class="appt-label">Confirmed</div>
                        <div class="appt-when">${whenStr} · ${timeVal}</div>
                        ${renderQueueRow(queueNumberVal, dateVal)}
                        ${note ? `<div class="appt-note">${note}</div>` : ''}
                    </div>
                    <div class="appt-status-actions">
                        <button class="btn-appt-reschedule" id="btnApptReschedule"><i class="fas fa-calendar-days"></i> Reschedule</button>
                    </div>
                </div>
            `;
        } else {
            section.innerHTML = `
                <h4><i class="fa-solid fa-calendar-check"></i> Appointment</h4>
                <div class="appt-status-box pending">
                    <div class="appt-status-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="appt-status-meta">
                        <div class="appt-label">Pending Confirmation</div>
                        <div class="appt-when">${whenStr} · ${timeVal}</div>
                        ${renderQueueRow(queueNumberVal, dateVal)}
                        <div class="appt-note">Awaiting confirmation from the office. You'll see it here once confirmed.</div>
                    </div>
                    <div class="appt-status-actions">
                        <button class="btn-appt-cancel" id="btnApptCancel"><i class="fas fa-xmark"></i> Cancel Request</button>
                    </div>
                </div>
            `;
        }

        const cancelBtn = document.getElementById('btnApptCancel');
        if (cancelBtn) cancelBtn.addEventListener('click', function () {
            if (!confirm('Cancel this appointment request?')) return;
            clearAppointment(keys);
            renderAppointmentSection(app);
        });
        const rescheduleBtn = document.getElementById('btnApptReschedule');
        if (rescheduleBtn) rescheduleBtn.addEventListener('click', function () {
            if (!confirm('Request a new date/time? Your confirmed appointment will be cleared until the new one is confirmed.')) return;
            clearAppointment(keys);
            renderAppointmentSection(app);
        });
        return;
    }

    // No appointment yet — show the request form
    const today = new Date();
    const minDate = new Date(today.getTime() + 24 * 60 * 60 * 1000).toISOString().slice(0, 10);

    section.innerHTML = `
        <h4><i class="fa-solid fa-calendar-check"></i> Appointment</h4>
        <p class="appt-hint">Your document is signed. Request a date and time to visit the office (for pickup, interview, or in-person requirements).</p>
        <div class="appt-form-row">
            <div class="appt-field">
                <label for="apptDateInput">Preferred Date</label>
                <input type="date" id="apptDateInput" min="${minDate}">
            </div>
            <div class="appt-field">
                <label for="apptTimeInput">Preferred Time Slot</label>
                <select id="apptTimeInput">
                    <option value="">Select a time slot</option>
                    ${APPT_TIME_SLOTS.map(t => `<option value="${t}">${t}</option>`).join('')}
                </select>
            </div>
        </div>
        <div class="appt-error" id="apptError"></div>
        <button class="btn-appt-request" id="btnApptRequest"><i class="fas fa-calendar-plus"></i> Request Appointment</button>
    `;

    document.getElementById('btnApptRequest').addEventListener('click', async function () {
        const dateVal = document.getElementById('apptDateInput').value;
        const timeVal = document.getElementById('apptTimeInput').value;
        const errorEl = document.getElementById('apptError');

        if (!dateVal || !timeVal) {
            errorEl.textContent = 'Please choose both a date and a time slot.';
            errorEl.style.display = 'block';
            return;
        }
        const chosen = new Date(dateVal + 'T00:00:00');
        if (chosen.getDay() === 0) {
            errorEl.textContent = 'The office is closed on Sundays — please pick another date.';
            errorEl.style.display = 'block';
            return;
        }

        const requestBtn = this;
        requestBtn.disabled = true;
        requestBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Requesting…';

        const queueNumber = await assignQueueNumber(dateVal, timeVal, svc.prefix);

        localStorage.setItem(keys.date, dateVal);
        localStorage.setItem(keys.time, timeVal);
        localStorage.setItem(keys.status, 'pending');
        localStorage.setItem(keys.requestedAt, new Date().toISOString());
        localStorage.setItem(keys.queueNumber, String(queueNumber));
        localStorage.removeItem(keys.confirmedAt);
        localStorage.removeItem(keys.note);

        renderAppointmentSection(app);
    });
}

function clearAppointment(keys) {
    localStorage.removeItem(keys.date);
    localStorage.removeItem(keys.time);
    localStorage.removeItem(keys.status);
    localStorage.removeItem(keys.requestedAt);
    localStorage.removeItem(keys.confirmedAt);
    localStorage.removeItem(keys.note);
    localStorage.removeItem(keys.queueNumber);
}

// ═══════════════════════════════════════════════════════════
//  STATS
// ═══════════════════════════════════════════════════════════
function updateStats(apps) {
    const total    = apps.length;
    const pending  = apps.filter(a => normalizeStatus(a.status) === 'submitted').length;
    const approved = apps.filter(a => ['processing','approved'].includes(normalizeStatus(a.status))).length;
    const done     = apps.filter(a => normalizeStatus(a.status) === 'completed').length;

    document.getElementById('statTotal').textContent   = total;
    document.getElementById('statPending').textContent = pending;
    document.getElementById('statApproved').textContent= approved;
    document.getElementById('statDone').textContent    = done;
}

// ═══════════════════════════════════════════════════════════
//  FILTER / SEARCH
// ═══════════════════════════════════════════════════════════
function applyFilter() {
    const query  = document.getElementById('searchInput').value.toLowerCase().trim();
    const status = document.getElementById('filterStatus').value;

    let filtered = allApps.filter(app => {
        const matchSearch = !query ||
            app.service.name.toLowerCase().includes(query) ||
            app.ref.toLowerCase().includes(query) ||
            app.detail.toLowerCase().includes(query);
        const matchStatus = status === 'all' ||
            normalizeStatus(app.status) === status;
        return matchSearch && matchStatus;
    });

    renderCards(filtered);
}

// ═══════════════════════════════════════════════════════════
//  PROFILE DROPDOWN
// ═══════════════════════════════════════════════════════════
function initProfileDropdown() {
    const userProfile = document.getElementById('userProfile');
    const profileBtn  = document.getElementById('profileBtn');
    if (!userProfile || !profileBtn) return;
    profileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const open = userProfile.classList.toggle('open');
        profileBtn.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', function(e) {
        if (!userProfile.contains(e.target)) {
            userProfile.classList.remove('open');
            profileBtn.setAttribute('aria-expanded', 'false');
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { userProfile.classList.remove('open'); profileBtn.setAttribute('aria-expanded','false'); }
    });
}

// ═══════════════════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', async function() {
    initNavbar();
    initProfileDropdown();

    [cachedVidRows, cachedTesdaRows, cachedChedRows] = await Promise.all([
        fetchRealVidStatus(),
        fetchRealTesdaStatus(),
        fetchRealChedStatus(),
    ]);
    allApps = collectApplications(cachedVidRows, cachedTesdaRows, cachedChedRows);
    updateStats(allApps);

    if (allApps.length === 0) {
        document.getElementById('emptyState').classList.add('visible');
        document.getElementById('statsBar').style.display = 'none';
        document.querySelector('.section-head').style.display = 'none';
    } else {
        renderCards(allApps);
    }

    // Deep link: track_application.php?ref=XYZ opens that application's
    // detail modal directly (used by the dashboard's "View Details" links)
    const params = new URLSearchParams(window.location.search);
    const wantedRef = params.get('ref');
    if (wantedRef) {
        const match = allApps.find(a => a.ref === wantedRef);
        if (match) openModal(match);
    }

    // Search & filter events
    document.getElementById('searchInput').addEventListener('input',   applyFilter);
    document.getElementById('filterStatus').addEventListener('change', applyFilter);

    // Modal close
    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('detailOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // QR lightbox close (button, backdrop click, Escape)
    document.getElementById('qrLightboxClose').addEventListener('click', closeQrLightbox);
    document.getElementById('qrLightboxOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeQrLightbox();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeQrLightbox();
    });
});
</script>
    <?php include __DIR__ . '/accessibility_widget.php'; ?>
</body>
</html>