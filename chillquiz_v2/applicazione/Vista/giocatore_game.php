<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ChillQuiz - Gioco</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/giocatore.css">

</head>
<body>

<h1>ChillQuiz</h1>

<div class="timer" id="timer"></div>
<div class="domanda" id="domanda"></div>
<div class="griglia" id="griglia"></div>
<div class="esito" id="esito"></div>
<div class="classifica" id="classifica"></div>

<script>
const giocatore = localStorage.getItem("giocatore_id");
const partita = localStorage.getItem("partita_id");

let haRisposto = false;

async function aggiorna() {

    const res = await fetch(`index.php?url=api/stato&partita=${partita}`);
    const data = await res.json();

    document.getElementById("timer").innerText = "Tempo: " + data.tempo;

    if (data.stato === "attesa") {
        document.getElementById("domanda").innerText = "In attesa della prossima domanda...";
        document.getElementById("griglia").innerHTML = "";
        document.getElementById("esito").innerText = "";
        mostraClassifica(data.giocatori);
    }

    if (data.stato === "domanda") {
        document.getElementById("esito").innerText = "";
        mostraDomanda(data.domanda);
    }


if (data.stato === "risultati") {

    haRisposto = false;

    document.getElementById("griglia").innerHTML = "";
    document.getElementById("domanda").innerText = "";
    document.getElementById("esito").innerText = "";

    mostraClassifica(data.classifica);
}
}

function mostraDomanda(domanda) {
    document.getElementById("domanda").innerText = domanda.testo;

    if (haRisposto) return;

    const colori = ["rosso", "blu", "giallo", "verde"];
    const griglia = document.getElementById("griglia");
    griglia.innerHTML = "";

    domanda.opzioni.forEach((op, i) => {
        const div = document.createElement("div");
        div.className = "risposta " + colori[i % 4];
        div.innerText = op.testo;
div.onclick = () => inviaRisposta(op.id, div);        griglia.appendChild(div);
    });
}

async function inviaRisposta(opzione, elemento) {

    if (haRisposto) return;

    haRisposto = true;

    // Evidenzia solo la risposta scelta
    elemento.classList.add("selezionata");

    // Disabilita click su tutte (ma senza cambiare stile)
    document.querySelectorAll(".risposta").forEach(btn => {
        btn.style.pointerEvents = "none";
    });

    // Invia risposta al server
    await fetch("index.php?url=api/rispondi", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `partita=${partita}&giocatore=${giocatore}&opzione=${opzione}`
    });
}
function mostraClassifica(lista) {
    if (!lista) return;

    let html = "<h2>Classifica</h2>";
    lista.forEach(g => {
        html += `<div>${g.nome} - ${g.punteggio} pt</div>`;
    });

    document.getElementById("classifica").innerHTML = html;
}

setInterval(aggiorna, 1000);
aggiorna();
</script>

</body>
</html>