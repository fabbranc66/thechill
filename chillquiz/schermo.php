<?php
include "config.php";

/* HEADER NO CACHE */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


$pin = $_GET["pin"] ?? "";

if (!$pin) {
    die("PIN mancante");
}

$res = $conn->query("SELECT * FROM partite WHERE pin='$pin'");
$partita = $res->fetch_assoc();

if (!$partita) {
    die("Partita non trovata");
}

$partita_id = $partita["id"];
$quiz_id = $partita["quiz_id"];
$stato = $partita["stato"];
$numero = (int)$partita["domanda_corrente"];
$tempo_fine = (int)$partita["tempo_fine"];

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base = dirname($_SERVER['SCRIPT_NAME']);

$link = $protocol . "://" . $host . $base . "/?pin=" . $pin;
$qr = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=" . urlencode($link);

$adesso = time();
$tempo_finito = ($tempo_fine > 0 && $adesso >= $tempo_fine);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Schermo Quiz</title>
<style>
body {
    font-family: Arial;
    background: #46178f;
    color: white;
    text-align: center;
    padding: 30px;
}
.pin { font-size: 80px; margin: 20px 0; }
.domanda { font-size: 40px; margin: 30px 0; }
.timer { font-size: 40px; margin-bottom: 20px; }

.risposte {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    max-width: 900px;
    margin: auto;
}

.box {
    padding: 40px;
    font-size: 28px;
    border-radius: 15px;
}

.r1 { background: #e21b3c; }
.r2 { background: #1368ce; }
.r3 { background: #d89e00; }
.r4 { background: #26890c; }

.classifica {
    font-size: 28px;
    max-width: 500px;
    margin: auto;
    background: rgba(0,0,0,0.2);
    padding: 20px;
    border-radius: 15px;
}
</style>
</head>
<body>

<?php if ($stato == "attesa"): ?>

<h1>Entra nel quiz</h1>
<div class="pin"><?php echo $pin; ?></div>
<img src="<?php echo $qr; ?>">

<?php elseif ($stato == "domanda"): ?>

<?php if ($tempo_finito): ?>

<h2>Prossima domanda in preparazione…</h2>

<div class="classifica">
<h3>Classifica attuale</h3>
<?php
$res = $conn->query("
SELECT nome, punteggio
FROM giocatori
WHERE partita_id=$partita_id
ORDER BY punteggio DESC
LIMIT 10
");

$pos = 1;
while($row = $res->fetch_assoc()){
    echo $pos.". ".$row["nome"]." - ".$row["punteggio"]."<br>";
    $pos++;
}
?>
</div>

<?php else: ?>

<?php
$offset = $numero - 1;

$domanda_res = $conn->query("
SELECT * FROM domande
WHERE quiz_id=$quiz_id
ORDER BY id
LIMIT 1 OFFSET $offset
");
$domanda = $domanda_res->fetch_assoc();

if ($domanda):

$risposte = $conn->query("
SELECT * FROM risposte
WHERE domanda_id=".$domanda["id"]);
?>

<div class="domanda">
<?php echo htmlspecialchars($domanda["testo"]); ?>
</div>

<div class="timer" id="timer">0</div>

<div class="risposte">
<?php
$i = 1;
while($r = $risposte->fetch_assoc()):
?>
    <div class="box r<?php echo $i; ?>">
        <?php echo htmlspecialchars($r["testo"]); ?>
    </div>
<?php
$i++;
endwhile;
?>
</div>

<script>
let fine = <?php echo $tempo_fine; ?>;

function aggiornaTimer(){
    let adesso = Math.floor(Date.now()/1000);
    let restante = fine - adesso;

    if(restante < 0) restante = 0;

    let el = document.getElementById("timer");
    if(el) el.innerText = restante;

    if(restante === 0){
        clearInterval(window.t);
        location.reload();
    }
}

aggiornaTimer();
window.t = setInterval(aggiornaTimer, 1000);
</script>

<?php else: ?>

<h2>Nessuna domanda disponibile</h2>

<?php endif; ?>

<?php endif; ?>

<?php elseif ($stato == "classifica"): ?>

<h2>Classifica</h2>
<div class="classifica">
<?php
$res = $conn->query("
SELECT nome, punteggio
FROM giocatori
WHERE partita_id=$partita_id
ORDER BY punteggio DESC
LIMIT 10
");

$pos = 1;
while($row = $res->fetch_assoc()){
    echo $pos.". ".$row["nome"]." - ".$row["punteggio"]."<br>";
    $pos++;
}
?>
</div>

<?php elseif ($stato == "finita"): ?>

<h2>Podio finale</h2>
<div class="classifica">
<?php
$res = $conn->query("
SELECT nome, punteggio
FROM giocatori
WHERE partita_id=$partita_id
ORDER BY punteggio DESC
LIMIT 3
");

$pos = 1;
while($row = $res->fetch_assoc()){
    echo $pos."° - ".$row["nome"]." (".$row["punteggio"].")<br>";
    $pos++;
}
?>
</div>

<?php endif; ?>

<script>
setTimeout(() => {
    location.reload();
}, 2000);
</script>

</body>
</html>
