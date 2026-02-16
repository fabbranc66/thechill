<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/* =========================================
   INIT + SICUREZZA
========================================= */
require __DIR__ . '/../../core/init.php';

if (
  !isset($_SESSION['utente']) ||
  ($_SESSION['utente']['ruolo'] ?? '') !== 'amministratore'
) {
  http_response_code(403);
  die('Accesso negato');
}

$titolo = 'Gestione Tavoli';
require __DIR__ . '/../../themes/semplice/header.php';

/* BASE URL */
$API  = BASE_URL . '/modules/tavoli/api';
?>

<h2>🪑 Gestione Tavoli</h2>
<p style="opacity:.7">Dashboard amministrativa</p>

<div class="legenda-tavoli">
  <div class="legenda-item">
    <span class="box libero"></span> Libero
  </div>
  <div class="legenda-item">
    <span class="box prenotato"></span> Prenotato
  </div>
  <div class="legenda-item">
    <span class="box occupato"></span> Occupato
  </div>
</div>

<div id="msg" style="margin:.5rem 0"></div>
<div id="tavoli-grid"></div>

<style>

.legenda-tavoli {
  display: flex;
  gap: 20px;
  margin: 10px 0 20px 0;
  flex-wrap: wrap;
  font-size: 14px;
}

.legenda-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.legenda-item .box {
  width: 18px;
  height: 18px;
  border: 3px solid #ccc;
  border-radius: 4px;
  display: inline-block;
}

.box.libero     { border-color: #2ecc71; }
.box.prenotato  { border-color: #f39c12; }
.box.occupato   { border-color: #e74c3c; }

#tavoli-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
  gap: 12px;
  max-width: 800px;
}

.tavolo {
  aspect-ratio: 1 / 1; /* mantiene il quadrato */
  border: 3px solid #ccc;
  border-radius: 12px;
  background: #fff;
  font-size: 22px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  user-select: none;
  transition: transform .1s ease, box-shadow .1s ease;
}

.tavolo:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.tavolo.libero     { border-color: #2ecc71; }
.tavolo.prenotato  { border-color: #f39c12; }
.tavolo.occupato   { border-color: #e74c3c; }
</style>

<script>
const API = '<?= $API ?>';
let controller = null;

/* ===============================
   UTILS
================================ */
function msg(text, ok = true) {
  const m = document.getElementById('msg');
  m.textContent = text;
  m.style.color = ok ? 'green' : 'red';
}

/* ===============================
   LOAD TAVOLI
================================ */
async function loadTavoli() {
  if (controller) controller.abort();
  controller = new AbortController();

  try {
    const r = await fetch(`${API}/tavoli_list.php`, {
      signal: controller.signal
    });

    if (!r.ok) throw 'HTTP ' + r.status;

    const res = await r.json();
    if (!res.ok) throw res.error || 'Errore API';

    renderTavoli(res.tavoli);
  } catch (e) {
    if (e.name !== 'AbortError') {
      document.getElementById('tavoli-grid').innerHTML =
        '<div style="color:#c00">Errore caricamento tavoli</div>';
      console.error(e);
    }
  }
}

/* ===============================
   RENDER
================================ */
function renderTavoli(tavoli) {
  const grid = document.getElementById('tavoli-grid');
  grid.innerHTML = '';

  if (!Array.isArray(tavoli) || tavoli.length === 0) {
    grid.innerHTML = '<div style="opacity:.6">Nessun tavolo</div>';
    return;
  }

  tavoli.forEach(t => {
    if (t.attivo != 1) return;

    const div = document.createElement('div');
    div.className = `tavolo ${t.stato}`;
    div.textContent = t.id;

    /* posizione da mappa */
    if (typeof t.x !== 'undefined' && typeof t.y !== 'undefined') {
      div.style.gridColumnStart = (t.x + 1);
      div.style.gridRowStart = (t.y + 1);
    }

    /* click sequenziale */
    div.addEventListener('click', () => azioneTavolo(t));

    grid.appendChild(div);
  });
}

/* ===============================
   API ACTIONS
================================ */
async function apiPost(file, data) {
  const r = await fetch(`${API}/${file}`, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams(data)
  });

  if (!r.ok) throw 'HTTP ' + r.status;

  const res = await r.json();
  if (!res.ok) throw res.error || 'Errore';
}

async function azioneTavolo(t) {
  try {
    if (t.stato === 'libero') {
      await apiPost('tavoli_prenota.php', { id: t.id });
      msg('Tavolo prenotato');
    } 
    else if (t.stato === 'prenotato') {
      await apiPost('tavoli_arrivo.php', { id: t.id });
      msg('Cliente arrivato');
    } 
    else if (t.stato === 'occupato') {
      await apiPost('libera_tavolo.php', { id: t.id });
      msg('Tavolo liberato');
    }

    loadTavoli();

  } catch (e) {
    msg(e, false);
  }
}

/* ===============================
   AVVIO
================================ */
loadTavoli();
setInterval(loadTavoli, 10000);
</script>

<?php require __DIR__ . '/../../themes/semplice/footer.php'; ?>
