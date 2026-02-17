<?php
include "config.php";

if (!isset($_SESSION["partita_id"])) {
    header("Location: index.php");
    exit;
}

$partita_id = $_SESSION["partita_id"];

$partita = $conn->query("
SELECT * FROM partite
WHERE id=$partita_id
")->fetch_assoc();

$quiz_id = $partita["quiz_id"];
$stato = $partita["stato"];
$numero = $partita["domanda_corrente"];

/* se non siamo nello stato domanda */
if ($stato != "domanda") {
    header("Location: attesa.php");
    exit;
}

$domanda = $conn->query("
SELECT * FROM domande
WHERE quiz_id=$quiz_id
ORDER BY id
LIMIT 1 OFFSET " . ($numero - 1)
)->fetch_assoc();

if (!$domanda) {
    echo "Fine quiz";
    exit;
}

$risposte = $conn->query("
SELECT * FROM risposte
WHERE domanda_id=".$domanda["id"]);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Domanda</title>
<style>
body {
    font-family: Arial;
    background: #46178f;
    color: white;
    text-align: center;
    padding: 30px;
}
button {
    width: 90%;
    padding: 20px;
    margin: 10px;
    font-size: 20px;
    border: none;
    border-radius: 10px;
}
</style>
</head>
<body>

<h2><?php echo htmlspecialchars($domanda["testo"]); ?></h2>
<div id="timer">0</div>

<form id="form" action="risposta.php" method="post">
<?php while($r = $risposte->fetch_assoc()): ?>
    <button type="submit" name="risposta" value="<?php echo $r["id"]; ?>">
        <?php echo htmlspecialchars($r["testo"]); ?>
    </button>
<?php endwhile; ?>
</form>

<script>
let fine = <?php echo (int)$partita["tempo_fine"]; ?>;

function aggiornaTimer(){
    let adesso = Math.floor(Date.now() / 1000);
    let restante = fine - adesso;

    if(restante < 0) restante = 0;

    document.getElementById("timer").innerText = restante;

    if(restante === 0){
        clearInterval(t);
        window.location.href = "attesa.php";
    }
}

aggiornaTimer();
let t = setInterval(aggiornaTimer, 1000);

// controllo stato partita
setInterval(()=>{
    fetch("stato.php")
    .then(r=>r.text())
    .then(stato=>{
        stato = stato.trim();
        if(stato !== "domanda"){
            location.href = "attesa.php";
        }
    });
},1500);
</script>

</body>
</html>
