<?php
include "config.php";

/* se si entra senza nome e senza pin, azzera il nome precedente */
if (!isset($_GET["nome"]) && !isset($_GET["pin"])) {
    unset($_SESSION["nome_cliente"]);
}

/* se arriva il nome dal gestionale */
if (isset($_GET["nome"])) {
    $_SESSION["nome_cliente"] = trim($_GET["nome"]);
}

/* se arriva il PIN dal form */
if (isset($_GET["pin"])) {

    $pin = $_GET["pin"];

    $res = $conn->query("SELECT * FROM partite WHERE pin='$pin'");
    $partita = $res->fetch_assoc();

    if ($partita) {

        $_SESSION["pin"] = $pin;
        $_SESSION["partita_id"] = $partita["id"];

        /* inserisce giocatore solo se abbiamo il nome */
        if (isset($_SESSION["nome_cliente"])) {

            $nome = $_SESSION["nome_cliente"];
            $partita_id = $partita["id"];

$nome = trim($_SESSION["nome_cliente"]);
$session_id = session_id();

/* controllo nome duplicato */
$stmt = $conn->prepare("
    SELECT id FROM giocatori
    WHERE partita_id = ? AND nome = ?
    LIMIT 1
");
$stmt->bind_param("is", $partita_id, $nome);
$stmt->execute();
$res = $stmt->get_result();
$esistente = $res->fetch_assoc();

if ($esistente) {

    /* sessione unica: aggiorna session_id */
    $giocatore_id = $esistente["id"];

    $stmt = $conn->prepare("
        UPDATE giocatori
        SET session_id = ?
        WHERE id = ?
    ");
    $stmt->bind_param("si", $session_id, $giocatore_id);
    $stmt->execute();

} else {

    /* inserimento nuovo giocatore */
    $stmt = $conn->prepare("
        INSERT INTO giocatori (partita_id, nome, punteggio, session_id)
        VALUES (?, ?, 0, ?)
    ");
    $stmt->bind_param("iss", $partita_id, $nome, $session_id);
    $stmt->execute();

    $giocatore_id = $stmt->insert_id;
}

$_SESSION["giocatore_id"] = $giocatore_id;
        }

        header("Location: lobby.php");
        exit;

    } else {
        session_destroy();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entra nel quiz</title>
<style>
body {
    font-family: Arial;
    background: #46178f;
    color: white;
    text-align: center;
    padding: 40px;
}
.box {
    background: #2d0f5f;
    padding: 30px;
    border-radius: 15px;
    display: inline-block;
}
input, button {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: none;
    border-radius: 8px;
    font-size: 18px;
}
button {
    background: #ff3355;
    color: white;
}
.nome-fisso {
    font-size: 22px;
    font-weight: bold;
    margin: 15px 0;
}
</style>
</head>
<body>

<div class="box">

<?php if (isset($_SESSION["nome_cliente"])): ?>

    <h2>Benvenuto</h2>
    <div class="nome-fisso">
        <?php echo htmlspecialchars($_SESSION["nome_cliente"]); ?>
    </div>

<?php else: ?>

    <h2>Inserisci PIN</h2>

<?php endif; ?>

<form method="get">

<?php if (!isset($_SESSION["nome_cliente"])): ?>
    <input type="text" name="nome" placeholder="Il tuo nome" required>
<?php endif; ?>

    <input type="text" name="pin" placeholder="PIN quiz" required>
    <button type="submit">Entra</button>
</form>

</div>

</body>
</html>
