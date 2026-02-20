const partita = 1;

async function aggiorna() {
    const res = await fetch(`index.php?url=api/stato&partita=${partita}`);
    const data = await res.json();

    if (data.stato === "attesa") {
        aggiornaGiocatori(data.giocatori);
    }
}

function aggiornaGiocatori(giocatori) {
    const lista = document.getElementById("listaGiocatori");
    lista.innerHTML = "";

    giocatori.forEach(g => {
        const div = document.createElement("div");
        div.className = "giocatore";
        div.innerText = g.nome;
        lista.appendChild(div);
    });
}

setInterval(aggiorna, 1000);
aggiorna();