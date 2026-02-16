<?php
declare(strict_types=1);

require __DIR__ . '/../../core/init.php';

$titolo = 'Prenotazione tavoli';
require __DIR__ . '/../../themes/semplice/header.php';

$API = BASE_URL . '/modules/tavoli/api';

/* token cliente */
$token = $_GET['t'] ?? '';
$cliente_nome = '';

if ($token !== '') {
    $stmt = $pdo->prepare("
        SELECT nome
        FROM utenti
        WHERE token_accesso = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $cliente_nome = (string)$stmt->fetchColumn();
}
?>

<h2>Prenotazione tavoli</h2>

<div id="tavoli-grid"></div>

<!-- MODALE -->
<div id="modal" class="modal">
  <div class="modal-box">

    <?php if ($cliente_nome): ?>
      <input type="hidden" id="nome" value="<?= htmlspecialchars($cliente_nome) ?>">
      <div class="nome-bloccato">
        <?= htmlspecialchars($cliente_nome) ?>
      </div>
    <?php else: ?>
      <input id="nome" placeholder="Nome" class="input">
    <?php endif; ?>

    <input id="ora" type="time" class="input">
    <input id="persone" type="number" min="1" placeholder="Persone" class="input">

    <button id="start-select" class="btn">Scegli tavoli</button>
  </div>
</div>

<style>
#tavoli-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, 70px);
  grid-auto-rows: 70px;
  gap: 10px;
  justify-content: center;
  margin: 20px 0;
}

.tavolo {
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  cursor: pointer;
  background: #fff;
  border: 2px solid #ccc;
}

.tavolo.libero {
  background: #00a86b;
  color: #fff;
  border-color: #00a86b;
}

.tavolo.occupato {
  background: #e74c3c;
  color: #fff;
  border-color: #e74c3c;
}

.tavolo.selected {
  outline: 4px solid #ffd700;
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.4);
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-box {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  width: 260px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.input {
  padding: 8px;
}

.btn {
  padding: 10px;
  background: #0b3d2e;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.nome-bloccato {
  background: #f0f0f0;
  padding: 8px;
  border-radius: 6px;
  font-weight: bold;
  text-align: center;
}
</style>

<script>
const API = '<?= $API ?>';
const TOKEN = '<?= $token ?>';

let tavoli = [];
let selected = [];
let tavoliRichiesti = 1;
let selezioneAttiva = false;

const grid = document.getElementById('tavoli-grid');
const modal = document.getElementById('modal');

/* ===============================
   LOAD TAVOLI
================================ */
async function loadTavoli() {
  const r = await fetch(`${API}/public_tavoli_list.php`);
  const res = await r.json();
  tavoli = res.tavoli;
  render();
}

function render() {
  grid.innerHTML = '';

  tavoli.forEach(t => {
    const div = document.createElement('div');
    div.className = `tavolo ${t.stato}`;
    div.textContent = t.id;

    /* ID reale tavolo */
    div.dataset.id = t.id;

    div.style.gridColumn = (t.x + 1);
    div.style.gridRow = (t.y + 1);

    if (selected.includes(t.id)) {
      div.classList.add('selected');
    }

    if (selezioneAttiva && t.stato === 'libero') {
      div.addEventListener('click', () => clickTavolo(t.id));
    }

    grid.appendChild(div);
  });
}

/* ===============================
   CALCOLO TAVOLI
================================ */
function calcolaTavoli(persone) {
  let tavoli = 1;
  let posti = 4;
  while (posti < persone) {
    tavoli++;
    posti += 2;
  }
  return tavoli;
}

/* ===============================
   CLICK TAVOLO
================================ */
function clickTavolo(id) {

  /* toggle */
  if (selected.includes(id)) {
    selected = selected.filter(x => x !== id);
    render();
    return;
  }

  if (selected.length >= tavoliRichiesti) {
    alert("Hai già selezionato i tavoli necessari");
    return;
  }

  const nuova = [...selected, id];

  if (!adiacenti(nuova)) {
    alert('I tavoli devono essere adiacenti');
    return;
  }

  selected = nuova;
  render();

  if (selected.length === tavoliRichiesti) {
    mostraConferma();
  }
}

function adiacenti(ids) {
  if (ids.length < 2) return true;

  const pos = ids.map(id => {
    const t = tavoli.find(x => x.id == id);
    return {id, x: t.x, y: t.y};
  });

  /* prova orizzontale */
  let orizz = [...pos].sort((a,b)=>a.x-b.x);
  let okOrizz = true;
  for (let i=1;i<orizz.length;i++) {
    if (!(orizz[i].y === orizz[0].y &&
          orizz[i].x === orizz[i-1].x + 1)) {
      okOrizz = false;
      break;
    }
  }

  /* prova verticale */
  let vert = [...pos].sort((a,b)=>a.y-b.y);
  let okVert = true;
  for (let i=1;i<vert.length;i++) {
    if (!(vert[i].x === vert[0].x &&
          vert[i].y === vert[i-1].y + 1)) {
      okVert = false;
      break;
    }
  }

  return okOrizz || okVert;
}

/* ===============================
   CONFERMA
================================ */
function mostraConferma() {
  if (!confirm("Confermare prenotazione tavoli: " + selected.join(", ") + " ?")) {
    return;
  }
  confermaPrenotazione();
}

/* ===============================
   PRENOTA
================================ */
async function confermaPrenotazione() {

  const nome = document.getElementById('nome').value.trim();
  const ora = document.getElementById('ora').value;
  const persone = document.getElementById('persone').value;

  const data = {
    tavolo_id: selected[0],
    tavoli: selected.join(','),
    nome_cliente: nome,
    ora: ora,
    persone: persone
  };

  if (TOKEN) data.token = TOKEN;

  const r = await fetch(`${API}/public_prenota.php`, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams(data)
  });

  const res = await r.json();

  if (!res.ok) {
    alert(res.error || 'Errore');
    return;
  }

  alert('Prenotazione confermata');

  if (TOKEN) {
    location.href = `<?= BASE_URL ?>/?mod=clienti&azione=cliente&t=${TOKEN}`;
  } else {
    location.href = `<?= BASE_URL ?>/`;
  }
}

/* ===============================
   AVVIO
================================ */
document.getElementById('start-select').onclick = () => {
  const persone = parseInt(document.getElementById('persone').value || 1);
  tavoliRichiesti = calcolaTavoli(persone);
  selected = [];
  selezioneAttiva = true;
  modal.style.display = 'none';
  render();
  alert('Seleziona ' + tavoliRichiesti + ' tavoli adiacenti');
};

const info = document.getElementById('selezione-info');

function aggiornaInfo() {
  if (selected.length === 0) {
    info.textContent = "Tavoli selezionati: -";
  } else {
    info.textContent = "Tavoli selezionati: " + selected.join(', ');
  }
}



loadTavoli();
</script>

<?php
require __DIR__ . '/../../themes/semplice/footer.php';
