<?php
$host = $_SERVER['HTTP_HOST'];
$link = "http://$host/thechill/chillquiz_v2/pubblico/index.php?url=giocatore&pin=" . $pin;
$qr = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($link);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>ChillQuiz - Schermo</title>
<link rel="stylesheet" href="assets/css/base.css">
<style>
body {
    background:#1e1f4f;
    color:white;
    text-align:center;
}

.pin {
    font-size:100px;
    margin-top:80px;
}

.timer {
    font-size:80px;
    margin-top:20px;
}

.domanda {
    font-size:40px;
    margin-top:50px;
}

.classifica {
    margin-top:40px;
    font-size:24px;
}
.giocatore {
    margin:10px;
}
</style>
</head>
<body>

<div id="contenuto"></div>

<script>
const partita = <?= $partita_id ?>;

async function aggiorna() {
    const res = await fetch(`index.php?url=api/stato&partita=${partita}`);
    const data = await res.json();

    const contenuto = document.getElementById("contenuto");

    if (data.stato === "attesa") {

        contenuto.innerHTML = `
            <div class="pin"><?= htmlspecialchars($pin) ?></div>
            <img src="<?= $qr ?>">
            <div class="classifica">
                ${data.giocatori.map(g => `<div class="giocatore">${g.nome}</div>`).join("")}
            </div>
        `;
    }

if (data.stato === "domanda") {

    const colori = ["rosso", "blu", "giallo", "verde"];

    let opzioniHTML = "";

    if (data.domanda && data.domanda.opzioni) {
        data.domanda.opzioni.forEach((op, i) => {
            opzioniHTML += `
                <div class="risposta ${colori[i % 4]}">
                    ${op.testo}
                </div>
            `;
        });
    }

    contenuto.innerHTML = `
        <div class="timer">${data.tempo}</div>
        <div class="domanda">${data.domanda.testo}</div>
        <div class="griglia">
            ${opzioniHTML}
        </div>
    `;
}
    if (data.stato === "risultati") {

        contenuto.innerHTML = `
            <h1>Risultati</h1>
            <div class="classifica">
                ${data.classifica.map(g => `<div>${g.nome} - ${g.punteggio} pt</div>`).join("")}
            </div>
        `;
    }
}

setInterval(aggiorna, 1000);
aggiorna();
</script>

</body>
</html>