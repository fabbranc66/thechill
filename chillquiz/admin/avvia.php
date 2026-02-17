<?php
include "../config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$quiz_id = $_GET["quiz_id"] ?? 0;

if (!$quiz_id) {
    die("Quiz non valido");
}

// genera PIN
$pin = rand(100000, 999999);

// crea partita
$stmt = $conn->prepare("
INSERT INTO partite (quiz_id, pin, stato, domanda_corrente, tempo_fine)
VALUES (?, ?, 'attesa', 1, 0)
");

if (!$stmt) {
    die("Errore prepare: " . $conn->error);
}

$stmt->bind_param("is", $quiz_id, $pin);

if (!$stmt->execute()) {
    die("Errore execute: " . $stmt->error);
}

$partita_id = $stmt->insert_id;

// link quiz
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base = dirname($_SERVER['SCRIPT_NAME'], 2);

$link = $protocol . "://" . $host . $base . "/?pin=" . $pin;

// QR code
$qr = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($link);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Partita avviata</title>
<style>
body {
    font-family: Arial;
    background: #46178f;
    color: white;
    text-align: center;
    padding: 20px;
}
.card {
    background: #2d0f5f;
    padding: 30px;
    border-radius: 15px;
    max-width: 400px;
    margin: auto;
}
.pin {
    font-size: 60px;
    margin: 20px 0;
    font-weight: bold;
}
a.button {
    display: inline-block;
    padding: 12px 20px;
    background: #ff3355;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    margin: 5px;
}
</style>
</head>
<body>

<div class="card">
    <h2>Partita avviata</h2>

    <div>PIN della partita:</div>
    <div class="pin"><?php echo $pin; ?></div>

    <h3>QR Code</h3>
    <img src="<?php echo $qr; ?>" alt="QR code">

    <br><br>

    <a class="button" href="partita.php?partita_id=<?php echo $partita_id; ?>">
        Pannello controllo partita
    </a>

    <br><br>
    <a class="button" href="../schermo.php?pin=<?php echo $pin; ?>" target="_blank">
        Apri schermata proiettore
    </a>

    <br><br>
    <a class="button" href="dashboard.php">Torna alla dashboard</a>
</div>

</body>
</html>
