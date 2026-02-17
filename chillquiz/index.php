<?php
include "config.php";

/* se arriva il PIN dal QR */
if (isset($_GET["pin"])) {
    $_SESSION["pin"] = $_GET["pin"];
}

/* se PIN già presente */
if (isset($_SESSION["pin"])) {

    $pin = $_SESSION["pin"];

    $res = $conn->query("SELECT * FROM partite WHERE pin='$pin'");
    $partita = $res->fetch_assoc();

    if ($partita) {
        $_SESSION["partita_id"] = $partita["id"];
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
</style>
</head>
<body>

<div class="box">
    <h2>Inserisci PIN</h2>

    <form method="get">
        <input type="text" name="pin" placeholder="PIN quiz" required>
        <button type="submit">Entra</button>
    </form>
</div>

</body>
</html>
