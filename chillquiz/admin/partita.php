<?php
include "../config.php";

if (!isset($_SESSION["admin_id"])) {
    die("Non autorizzato");
}

$partita_id = $_GET["partita_id"] ?? 0;

if (!$partita_id) {
    die("Partita non valida");
}

$partita = $conn->query("
SELECT * FROM partite
WHERE id=$partita_id
")->fetch_assoc();

if (!$partita) {
    die("Partita non trovata");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Controllo partita</title>
<style>
body {
    font-family: Arial;
    background: #46178f;
    color: white;
    text-align: center;
    padding: 20px;
}
.button {
    display: inline-block;
    padding: 15px 25px;
    margin: 10px;
    background: #ff3355;
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-size: 18px;
}
.pin {
    font-size: 60px;
    margin: 20px 0;
}
.stato {
    font-size: 20px;
    margin-bottom: 20px;
}
</style>
</head>
<body>

<h1>Controllo partita</h1>

<div class="pin"><?php echo $partita["pin"]; ?></div>

<div class="stato">
Stato: <?php echo $partita["stato"]; ?> |
Domanda: <?php echo $partita["domanda_corrente"]; ?>
</div>

<a class="button" href="azione_partita.php?partita_id=<?php echo $partita_id; ?>&azione=start">
Avvia quiz
</a>

<a class="button" href="azione_partita.php?partita_id=<?php echo $partita_id; ?>&azione=next">
Prossima domanda
</a>

<a class="button" href="azione_partita.php?partita_id=<?php echo $partita_id; ?>&azione=classifica">
Mostra classifica
</a>

<a class="button" href="azione_partita.php?partita_id=<?php echo $partita_id; ?>&azione=end">
Termina quiz
</a>

<br><br>
<a class="button" href="dashboard.php">
Torna alla dashboard
</a>

</body>
</html>
