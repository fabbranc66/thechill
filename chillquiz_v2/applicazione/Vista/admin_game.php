<?php

use Applicazione\Modello\Partita;

$partita = Partita::attiva();

if (!$partita) {
    die("Nessuna partita attiva");
}

$partita_id = $partita->id();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Regia ChillQuiz</title>
<link rel="stylesheet" href="assets/css/base.css">

<style>
body {
    background:#121212;
    color:white;
    font-family:Arial, sans-serif;
    padding:40px;
}

h1 {
    margin-bottom:30px;
}

.dashboard {
    display:flex;
    gap:40px;
}

.box {
    background:#1f1f1f;
    padding:20px;
    border-radius:12px;
    flex:1;
}

button {
    padding:12px 20px;
    margin:5px;
    font-size:15px;
    cursor:pointer;
    border:none;
    border-radius:6px;
    transition:0.2s;
}

button:hover {
    opacity:0.8;
}

.primary { background:#1368ce; color:white; }
.warning { background:#d89e00; color:white; }
.success { background:#26890c; color:white; }
.danger { background:#e21b3c; color:white; }

.status-line {
    margin:8px 0;
}

.log {
    background:#000;
    padding:15px;
    height:250px;
    overflow:auto;
    font-size:13px;
    border-radius:8px;
}
</style>
</head>
<body>

<h1>🎛 Regia ChillQuiz</h1>

<div class="dashboard">

    <div class="box">
        <h2>Stato Partita</h2>
        <div id="stato"></div>
    </div>

    <div class="box">
        <h2>Controlli</h2>
        <button class="primary" onclick="azione('avvia_domanda')">Avvia Domanda</button>
        <button class="warning" onclick="azione('mostra_risultati')">Mostra Risultati</button>
        <button class="success" onclick="azione('prossima_domanda')">Prossima Domanda</button>
        <button class="danger" onclick="azione('reset')">Reset Partita</button>
        <button class="primary" onclick="apriSchermo()">Apri Schermo Pubblico</button>    </div>

</div>

<div style="margin-top:30px;">
    <h2>Log Eventi</h2>
    <div class="log" id="log"></div>
</div>

<script>
const partita = <?= $partita_id ?>;

async function aggiornaStato() {

    const res = await fetch(`index.php?url=api/stato&partita=${partita}`);
    const data = await res.json();

    let html = `
        <div class="status-line"><strong>Stato:</strong> ${data.stato}</div>
        <div class="status-line"><strong>Tempo:</strong> ${data.tempo}</div>
    `;

    if (data.giocatori) {
        html += `<div class="status-line"><strong>Giocatori:</strong> ${data.giocatori.length}</div>`;
    }

    if (data.classifica) {
        html += `<div class="status-line"><strong>Classifica:</strong><br>`;
        data.classifica.forEach(g => {
            html += `${g.nome} - ${g.punteggio} pt<br>`;
        });
        html += `</div>`;
    }

    document.getElementById("stato").innerHTML = html;
}

async function azione(tipo) {

    const res = await fetch("index.php?url=api/admin_control", {
        method:"POST",
        headers:{ "Content-Type":"application/x-www-form-urlencoded" },
        body:`azione=${tipo}&partita=${partita}`
    });

    const data = await res.json();

    if (data.successo) {
        log("✔ Azione eseguita: " + tipo);
    } else {
        log("✖ Errore: " + (data.errore ?? ''));
    }

    aggiornaStato();
}

function log(testo) {
    const box = document.getElementById("log");
    box.innerHTML += testo + "<br>";
    box.scrollTop = box.scrollHeight;
}

setInterval(aggiornaStato, 1000);
aggiornaStato();

function apriSchermo() {

    const base = window.location.origin;
    const url = base + "/thechill/chillquiz_v2/pubblico/index.php?url=schermo";

    window.open(
        url,
        "ChillQuizSchermo",
        "width=1280,height=800,resizable=yes,scrollbars=no"
    );
}
</script>
</body>
</html>