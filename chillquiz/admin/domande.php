<?php
include "../config.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$quiz_id = $_GET["quiz_id"] ?? 0;

if (!$quiz_id) {
    die("Quiz non valido");
}

/* AGGIUNTA DOMANDA */
if (isset($_POST["aggiungi_domanda"])) {

    $testo = $_POST["testo"];
    $r1 = $_POST["r1"];
    $r2 = $_POST["r2"];
    $r3 = $_POST["r3"];
    $r4 = $_POST["r4"];
    $corretta = $_POST["corretta"];

    $stmt = $conn->prepare("INSERT INTO domande (quiz_id, testo) VALUES (?, ?)");
    $stmt->bind_param("is", $quiz_id, $testo);
    $stmt->execute();

    $domanda_id = $stmt->insert_id;

    $risposte = [$r1, $r2, $r3, $r4];

    foreach ($risposte as $index => $risp) {
        $corr = ($corretta == $index) ? 1 : 0;

        $stmt = $conn->prepare("
            INSERT INTO risposte (domanda_id, testo, corretta)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("isi", $domanda_id, $risp, $corr);
        $stmt->execute();
    }
}

/* MODIFICA DOMANDA */
if (isset($_POST["salva_modifica"])) {

    $domanda_id = $_POST["domanda_id"];
    $testo = $_POST["testo"];
    $corretta = $_POST["corretta"];

    $conn->query("
        UPDATE domande
        SET testo='$testo'
        WHERE id=$domanda_id
    ");

    $risposte = $conn->query("
        SELECT * FROM risposte
        WHERE domanda_id=$domanda_id
        ORDER BY id
    ");

    $i = 0;
    while ($r = $risposte->fetch_assoc()) {
        $testo_r = $_POST["r".$i];
        $corr = ($corretta == $i) ? 1 : 0;

        $conn->query("
            UPDATE risposte
            SET testo='$testo_r', corretta=$corr
            WHERE id=".$r["id"]
        );

        $i++;
    }
}

/* ELIMINA DOMANDA */
if (isset($_GET["delete"])) {
    $domanda_id = $_GET["delete"];

    $conn->query("DELETE FROM risposte WHERE domanda_id=$domanda_id");
    $conn->query("DELETE FROM domande WHERE id=$domanda_id");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestione Domande</title>
<style>
body {
    font-family: Arial;
    background: #46178f;
    color: white;
    margin: 0;
}
header {
    background: #2d0f5f;
    padding: 15px;
    text-align: center;
}
.container {
    padding: 20px;
}
.card {
    background: #2d0f5f;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}
input, button, textarea, select {
    width: 100%;
    padding: 10px;
    margin: 6px 0;
    border: none;
    border-radius: 8px;
    font-size: 16px;
}
button {
    background: #ff3355;
    color: white;
    cursor: pointer;
}
.domanda {
    background: #2d0f5f;
    padding: 15px;
    margin: 15px 0;
    border-radius: 10px;
}
a {
    color: white;
}
</style>
</head>
<body>

<header>
    <h1>Gestione Domande</h1>
    <a href="dashboard.php">← Torna alla dashboard</a>
</header>

<div class="container">

<!-- NUOVA DOMANDA -->
<div class="card">
<h2>Aggiungi domanda</h2>
<form method="post">
<textarea name="testo" placeholder="Testo domanda" required></textarea>

<input type="text" name="r1" placeholder="Risposta 1" required>
<input type="text" name="r2" placeholder="Risposta 2" required>
<input type="text" name="r3" placeholder="Risposta 3" required>
<input type="text" name="r4" placeholder="Risposta 4" required>

<label>Risposta corretta:</label>
<select name="corretta">
<option value="0">Risposta 1</option>
<option value="1">Risposta 2</option>
<option value="2">Risposta 3</option>
<option value="3">Risposta 4</option>
</select>

<button name="aggiungi_domanda">Salva domanda</button>
</form>
</div>

<!-- ELENCO DOMANDE -->
<div class="card">
<h2>Domande del quiz</h2>

<?php
$res = $conn->query("SELECT * FROM domande WHERE quiz_id=$quiz_id");

while ($d = $res->fetch_assoc()):

$risposte = $conn->query("
SELECT * FROM risposte
WHERE domanda_id=".$d["id"]."
ORDER BY id
");
?>

<div class="domanda">
<form method="post">
<input type="hidden" name="domanda_id" value="<?php echo $d["id"]; ?>">

<textarea name="testo"><?php echo $d["testo"]; ?></textarea>

<?php
$i = 0;
while ($r = $risposte->fetch_assoc()):
?>
<input type="text" name="r<?php echo $i; ?>" value="<?php echo $r["testo"]; ?>">
<label>
<input type="radio" name="corretta" value="<?php echo $i; ?>"
<?php if ($r["corretta"]) echo "checked"; ?>>
 Corretta
</label>
<?php
$i++;
endwhile;
?>

<button name="salva_modifica">Salva modifiche</button>
<a href="?quiz_id=<?php echo $quiz_id; ?>&delete=<?php echo $d["id"]; ?>">
Elimina domanda
</a>
</form>
</div>

<?php endwhile; ?>

</div>
</div>

</body>
</html>
