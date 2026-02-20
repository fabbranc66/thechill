<?php
$pin = $_GET['pin'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ChillQuiz - Giocatore</title>
    <link rel="stylesheet" href="assets/css/base.css">
</head>
<body style="display:flex;justify-content:center;align-items:center;height:100vh;flex-direction:column;">

<h1>Entra nel gioco</h1>

<input type="text" id="alias" placeholder="Il tuo alias" style="padding:10px;font-size:18px;margin:10px;">
<input type="text" id="pin" value="<?= htmlspecialchars($pin) ?>" placeholder="PIN" style="padding:10px;font-size:18px;margin:10px;">

<button onclick="joinGame()" style="padding:10px 20px;font-size:18px;">Entra</button>

<div id="messaggio" style="margin-top:20px;color:red;"></div>

<script>
async function joinGame() {
    const alias = document.getElementById("alias").value;
    const pin   = document.getElementById("pin").value;

    const res = await fetch("index.php?url=api/join", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `alias=${alias}&pin=${pin}`
    });

    const data = await res.json();

    if (data.successo) {
        localStorage.setItem("giocatore_id", data.giocatore_id);
        localStorage.setItem("partita_id", data.partita_id);
        window.location.href = "index.php?url=giocatore_game";
    } else {
        document.getElementById("messaggio").innerText = data.errore;
    }
}
</script>

</body>
</html>