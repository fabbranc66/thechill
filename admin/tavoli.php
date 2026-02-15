<?php
declare(strict_types=1);

/* ==========================================================
   INIT + SICUREZZA
========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');

/* ==========================================================
   TEMPLATE
========================================================== */
$titolo = 'Gestione Tavoli';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>🪑 Gestione Tavoli</h2>
<p style="opacity:.7">Vista amministrativa in tempo reale</p>

<div id="tavoli-grid" class="grid">
  <div style="opacity:.6">Caricamento tavoli…</div>
</div>

<script>
/* ==========================================================
   CONFIG
========================================================== */
const API_URL = '<?= BASE_URL ?>/modules/tables-v2/api/tavoli_list.php';

/* ==========================================================
   RENDER TAVOLI
========================================================== */
function renderTavoli(tavoli) {
  const grid = document.getElementById('tavoli-grid');
  grid.innerHTML = '';

  tavoli.forEach(t => {
    const div = document.createElement('div');
    div.className = 'tavolo ' + t.stato;

    div.innerHTML = `
      <h3>${t.nome}</h3>
      <div class="posti">${t.posti} posti</div>
      <div class="stato">${t.stato.toUpperCase()}</div>
    `;

    grid.appendChild(div);
  });
}

/* ==========================================================
   LOAD DA API
========================================================== */
function loadTavoli() {
  fetch(API_URL)
    .then(r => r.json())
    .then(res => {
      if (!res.ok) {
        alert('Errore API tavoli');
        return;
      }
      renderTavoli(res.tavoli);
    })
    .catch(() => {
      alert('Errore di rete');
    });
}

/* ==========================================================
   AVVIO
========================================================== */
loadTavoli();

// refresh automatico ogni 10s
setInterval(loadTavoli, 10000);
</script>

<?php
require __DIR__ . '/../themes/semplice/footer.php';