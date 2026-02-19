<?php
include "config.php";

/* HEADER NO CACHE */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION["partita_id"])) {
    header("Location: index.php");
    exit;
}

$partita_id = $_SESSION["partita_id"];

/* controllo sessione giocatore */
if (!isset($_SESSION["giocatore_id"])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$giocatore_id = (int)$_SESSION["giocatore_id"];
$session_id = session_id();

$res_check = $conn->query("
    SELECT session_id
    FROM giocatori
    WHERE id=$giocatore_id
    LIMIT 1
");

$row_check = $res_check->fetch_assoc();

if (!$row_check || $row_check["session_id"] !== $session_id) {
    session_destroy();
    die("Sei stato disconnesso: accesso da un altro dispositivo.");
}


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

<!-- META NO CACHE -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<style>
body {
    font-family: Arial;
    background: #46178f;
    color: white;
    text-align: center;
    padding: 15px;
}
.timer { font-size: 28px; margin: 10px 0 20px 0; }
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
}
.r1 { background: #e21b3c; }
.r2 { background: #1368ce; }
.r3 { background: #d89e00; }
.r4 { background: #26890c; }
.box {
    background: #2d0f5f;
    padding: 30px;
    border-radius: 15px;
    display: inline-block;
}
.selected {
    outline: 5px solid white;
    transform: scale(1.05);
    opacity: 1;
}

.disabled {
    opacity: 0.5;
}

</style>
</head>
<body>

<?php if ($stato == "attesa"): ?>

<div class="box">
    <h2>In attesa dell’inizio quiz</h2>

    <h3>Giocatori presenti:</h3>

    <?php
    $res_gioc = $conn->query("
        SELECT nome
        FROM giocatori
        WHERE partita_id=".(int)$partita_id."
        ORDER BY id ASC
    ");

    if ($res_gioc && $res_gioc->num_rows > 0):
        while ($g = $res_gioc->fetch_assoc()):
    ?>
        <div style="margin:6px 0; font-size:18px;">
            <?php echo htmlspecialchars($g["nome"]); ?>
        </div>
    <?php
        endwhile;
    else:
    ?>
        <div>Nessun giocatore collegato</div>
    <?php endif; ?>
</div>

<?php elseif ($stato == "domanda"): ?>

<?php if ($tempo_finito): ?>

<?php
$res_class = $conn->query("
    SELECT nome, punteggio
    FROM giocatori
    WHERE partita_id=".(int)$partita_id."
    ORDER BY punteggio DESC
    LIMIT 10
");
?>

<div class="box">
    <?php
    if (isset($_SESSION["ultimo_punteggio"])) {
        $punti = $_SESSION["ultimo_punteggio"];
    ?>
        <h2>+<?php echo $punti; ?> punti</h2>
    <?php
    } else {
    ?>
        <h2>Classifica</h2>
    <?php
    }
    ?>

    <div style="margin-top:20px;">
    <?php
    $pos = 1;
    while ($row = $res_class->fetch_assoc()):
    ?>
        <div style="margin:6px 0; font-size:18px;">
            <?php echo $pos; ?>.
            <?php echo htmlspecialchars($row["nome"]); ?>
            — <?php echo (int)$row["punteggio"]; ?> pt
        </div>
    <?php
        $pos++;
    endwhile;
    ?>
    </div>
</div>

<h3 style="margin-top:20px;">Storico risposte</h3>

<?php
$res_gioc = $conn->query("
    SELECT id, nome
    FROM giocatori
    WHERE partita_id=".(int)$partita_id."
    ORDER BY id ASC
");

if ($res_gioc && $res_gioc->num_rows > 0):
    while ($g = $res_gioc->fetch_assoc()):

        $res_storico = $conn->query("
            SELECT punti
            FROM risposte_giocatori
            WHERE giocatore_id=".$g["id"]."
            ORDER BY id ASC
        ");

        $sequenza = "";

        if ($res_storico && $res_storico->num_rows > 0) {
            while ($r = $res_storico->fetch_assoc()) {
                if ((int)$r["punti"] > 0) {
                    $sequenza .= "✔";
                } else {
                    $sequenza .= "✖";
                }
            }
        }
?>
    <div style="margin:6px 0; font-size:18px;">
        <?php echo htmlspecialchars($g["nome"]); ?>
        — <?php echo $sequenza ?: "-"; ?>
    </div>
<?php
    endwhile;
else:
?>
    <div>Nessun giocatore</div>
<?php endif; ?>

<?php else: ?>

<?php
$offset = $numero - 1;
$res_domanda = $conn->query("
SELECT * FROM domande
WHERE quiz_id=$quiz_id
ORDER BY id
LIMIT 1 OFFSET $offset
");

$domanda = $res_domanda->fetch_assoc();

if (!$domanda) {
    // nessuna domanda disponibile
    $stato = "finita";
}

$risposte = $conn->query("
SELECT * FROM risposte
WHERE domanda_id=".$domanda["id"]);
?>

<h2><?php echo htmlspecialchars($domanda["testo"]); ?></h2>
<div class="timer" id="timer">0</div>

<form action="risposta.php" method="post">
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
let offset = 0;

fetch("time.php?nocache=" + Date.now())
.then(r => r.text())
.then(serverTime => {
    let clientTime = Math.floor(Date.now()/1000);
    offset = serverTime - clientTime;
    avviaTimer();
});

function avviaTimer(){
    aggiornaTimer();
    window.t = setInterval(aggiornaTimer, 1000);
}

function aggiornaTimer(){
    let adesso = Math.floor(Date.now()/1000) + offset;
    let restante = fine - adesso;

    if(restante < 0) restante = 0;

    let el = document.getElementById("timer");
    if(el) el.innerText = restante;

    if(restante === 0){
        clearInterval(window.t);
        location.href = "gioco.php?refresh=" + Date.now();
    }
}
</script>

<?php endif; ?>

<?php elseif ($stato == "classifica" || ($stato == "domanda" && $tempo_finito)): ?>

<?php
$res_class = $conn->query("
    SELECT nome, punteggio
    FROM giocatori
    WHERE partita_id=".(int)$partita_id."
    ORDER BY punteggio DESC
    LIMIT 10
");
?>

<div class="box">
    <h2>Classifica</h2>

    <?php
    $pos = 1;
    while ($row = $res_class->fetch_assoc()):
    ?>
        <div style="margin:8px 0; font-size:20px;">
            <?php echo $pos; ?>.
            <?php echo htmlspecialchars($row["nome"]); ?>
            — <?php echo (int)$row["punteggio"]; ?> pt
        </div>
    <?php
        $pos++;
    endwhile;
    ?>
</div>

<?php elseif ($stato == "finita"): ?>

<?php
$res_podio = $conn->query("
    SELECT nome, punteggio
    FROM giocatori
    WHERE partita_id=".(int)$partita_id."
    ORDER BY punteggio DESC
    LIMIT 3
");
?>

<div class="box">
    <h2>Quiz terminato</h2>
    <h3>Podio finale</h3>

    <?php
    $pos = 1;
    $medaglie = ["🥇", "🥈", "🥉"];

    while ($row = $res_podio->fetch_assoc()):
    ?>
        <div style="margin:10px 0; font-size:22px;">
            <?php echo $medaglie[$pos-1]; ?>
            <?php echo htmlspecialchars($row["nome"]); ?>
            — <?php echo (int)$row["punteggio"]; ?> pt
        </div>
    <?php
        $pos++;
    endwhile;
    ?>
</div>

<?php endif; ?>

<script>
setTimeout(() => {
    location.href = "gioco.php?refresh=" + Date.now();
}, 2000);

function selezionaRisposta(btn) {

    // disabilita tutti i bottoni
    let buttons = document.querySelectorAll(".btn");
    buttons.forEach(b => {
        b.disabled = true;
        b.classList.add("disabled");
    });

    // evidenzia quello scelto
    btn.classList.remove("disabled");
    btn.classList.add("selected");

    // piccolo ritardo per far vedere l'effetto
    setTimeout(() => {
        btn.form.submit();
    }, 150);

    return false;
}
</script>

</body>
</html>
