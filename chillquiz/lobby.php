<?php
include "config.php";

if (!isset($_SESSION["partita_id"])) {
    header("Location: index.php");
    exit;
}

$partita_id = $_SESSION["partita_id"];

/* se giocatore già registrato */
if (isset($_SESSION["giocatore_id"])) {
    header("Location: gioco.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);

    if ($nome != "") {

        $stmt = $conn->prepare("
        INSERT INTO giocatori (partita_id, nome)
        VALUES (?, ?)
        ");
        $stmt->bind_param("is", $partita_id, $nome);
        $stmt->execute();

        $_SESSION["giocatore_id"] = $stmt->insert_id;

        header("Location: gioco.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lobby</title>
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

    <form method="post">
        <input type="hidden" name="nome" value="<?php echo htmlspecialchars($_SESSION["nome_cliente"]); ?>">
        <button type="submit">Entra nel quiz</button>
    </form>

    <script>
        document.forms[0].submit();
    </script>

<?php else: ?>

    <h2>Inserisci il tuo nome</h2>

    <form method="post">
        <input type="text" name="nome" placeholder="Il tuo nome" required>
        <button type="submit">Entra nel quiz</button>
    </form>

<?php endif; ?>

</div>

</body>
</html>
