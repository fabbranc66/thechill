<?php
include "config.php";

if (!isset($_SESSION["partita_id"])) {
    header("Location: index.php");
    exit;
}

$partita_id = $_SESSION["partita_id"];

$res = $conn->query("SELECT * FROM partite WHERE id=$partita_id");
$partita = $res->fetch_assoc();

if (!$partita) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$quiz_id = $partita["quiz_id"];
$stato = $partita["stato"];
$numero = (int)$partita["domanda_corrente"];
$tempo_fine = (int)$partita["tempo_fine"];

$adesso = time();
$tempo_finito = ($tempo_fine > 0 && $adesso >= $tempo_fine);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Quiz</title>
<style>
body {
    font-family: Arial;
    background: #46178f;
    color: white;
    text-align: center;
    padding: 15px;
}

.timer {
    font-size: 28px;
    margin: 10px 0 20px 0;
}

.risposte {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 15px;
}

.btn {
    border: none;
    border-radius: 14px;
    padding: 25px 15px;
    font-size: 18px;
    color: white;
    font-weight: bold;
    text-align: center;
}

.r1 { background: #e21b3c; }
.r2 { background: #1368ce; }
.r3 { background: #d89e00; }
.r4 { background: #26890c; }

.selected {
    outline: 5px solid white;
    transform: scale(1.05);
}

.box {
    background: #2d0f5f;
    padding: 30px;
    border-radius: 15px;
    display: inline-block;
}
</style>
</head>
<body>

<?php if ($stato == "attesa"): ?>

<div class="box">
    <h2>In attesa dell’inizio quiz</h2>
</div>

<?php elseif ($stato == "domanda"): ?>

<?php if ($tempo_finito): ?>

<div class="box">
<?php
$punti = $_SESSION["ultimo_punteggio"] ?? 0;
unset($_SESSION["ultimo_punteggio"]);
?>
    <h2>+<?php echo $punti; ?> punti</h2>
    <p>Guarda la classifica sullo schermo</p>
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

<h2><?php echo htmlspecialchars($domanda["testo"]); ?></h2>
<div class="timer" id="timer">0</div>

<form id="form" action="risposta.php" method="post">
<div class="risposte">
<?php
$i = 1;
while($r = $risposte->fetch_assoc()):
?>
<button
    type="submit"
    name="risposta"
    value="<?php echo $r["id"]; ?>"
    class="btn r<?php echo $i; ?>"
>
    <?php echo htmlspecialchars($r["testo"]); ?>
</button>
<?php
$i++;
endwhile;
?>
</div>
</form>

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

<div class="box">
    <h2>Nessuna domanda disponibile</h2>
</div>

<?php endif; ?>

<?php endif; ?>

<?php elseif ($stato == "classifica"): ?>

<div class="box">
    <h2>Classifica in aggiornamento</h2>
</div>

<?php elseif ($stato == "finita"): ?>

<div class="box">
    <h2>Quiz terminato</h2>
</div>

<?php endif; ?>

<script>
setTimeout(() => {
    location.reload();
}, 2000);
</script>

</body>
</html>
