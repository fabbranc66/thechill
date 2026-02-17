<?php
include "config.php";

if (!isset($_SESSION["partita_id"])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Attendi</title>
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
    padding: 40px;
    border-radius: 15px;
    display: inline-block;
}
</style>
</head>
<body>

<div class="box">
    <h2>Guarda lo schermo principale</h2>
    <p>In attesa della prossima domanda...</p>
</div>

<script>
setInterval(()=>{
    fetch("stato.php")
    .then(r=>r.text())
    .then(stato=>{
        stato = stato.trim();

        if(stato === "domanda"){
            location.href = "domanda.php";
        }

        if(stato === "finita"){
            location.href = "podio.php";
        }
    });
},1500);
</script>

</body>
</html>
