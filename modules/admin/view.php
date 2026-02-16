<?php
declare(strict_types=1);

/* ==========================================================
   MODULO ADMIN - VIEW
========================================================== */

richiedi_ruolo('amministratore');

/* ==========================================================
   AZIONI SPECIALI (es. impostazioni)
========================================================== */
$azione = $_GET['azione'] ?? null;

if ($azione === 'impostazioni') {
    require __DIR__ . '/impostazioni.php';
    return;
}

if ($azione === 'premi') {
    require ROOT_PATH . '/modules/premi/view.php';
    return;
}
/* ==========================================================
   LOGICA ADMIN
========================================================== */
require __DIR__ . '/actions.php';
require __DIR__ . '/query.php';

$titolo = 'Dashboard';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2>Dashboard</h2>

<div class="stats">
  <div class="box">👥<br><?= $clienti ?><br>Clienti</div>
  <div class="box">💳<br><?= $carte ?><br>Carte</div>
  <div class="box">🏆<br><?= $punti ?><br>Punti</div>
  <div class="box">📷<br><?= $scansioni_totali ?><br>Scansioni</div>
  <div class="box">🎁<br><?= $regali_riscattati ?><br>Riscattati</div>
  <div class="box">🏁<br><?= $gratta_riscattati ?><br>Riscattati</div>
</div>

<div class="main-actions">
  <a href="?mod=carte&azione=nuova" class="btn-nuova-carta">➕ Nuova carta</a>
  <a href="<?= BASE_URL ?>/?mod=clienti&azione=edit&id=0" class="btn-nuova-carta">
      ➕ Nuovo cliente
  </a>
    <a href="<?= BASE_URL ?>/?mod=tavoli" class="btn-nuova-carta">
    🪑 Tavoli
    </a>
  <?php
  $scanner_desktop = $SETTINGS['scanner_desktop'] ?? '0';

  $mostra_scanner =
      (
          is_mobile_device() ||
          $scanner_desktop === '1'
      );
  ?>

  <?php if ($mostra_scanner): ?>
    <a href="<?= BASE_URL ?>/?mod=scansioni&vista=cassa" class="btn-nuova-carta">
        📲 Scanner
    </a>
  <?php endif; ?>

  <a href="?mod=premi" class="btn-nuova-carta">🎁 Gestione premi</a>
  <a href="?mod=admin&azione=impostazioni" class="btn-nuova-carta">⚙️ Impostazioni</a>
</div>

<?php if ($premi_riscattabili): ?>
<div style="margin:20px 0;padding:14px;text-align:center;background:#fff8d6;border:2px solid #f1c40f;border-radius:8px;font-weight:600;">
  🎁 Sono presenti premi da riscattare —
  <a href="?mod=admin&tab=carte">vai alle carte</a>
</div>
<?php endif; ?>

<div class="switch" style="display:flex;gap:8px;flex-wrap:wrap;margin:15px 0">
  <a href="?mod=admin&tab=scansioni" class="<?= $tab==='scansioni'?'on':'' ?>">Scansioni</a>
  <a href="?mod=admin&tab=carte" class="<?= $tab==='carte'?'on':'' ?>">Carte</a>
  <a href="?mod=admin&tab=clienti" class="<?= $tab==='clienti'?'on':'' ?>">Clienti</a>
  <a href="?mod=admin&tab=riscatti" class="<?= $tab==='riscatti'?'on':'' ?>">🎁 Riscatti</a>
  <a href="?mod=admin&tab=gratta" class="<?= $tab==='gratta'?'on':'' ?>">🎁 Gratta</a>
</div>

<?php
switch ($tab) {
    case 'scansioni':
        require ROOT_PATH . '/modules/scansioni/lista.php';
        break;

    case 'carte':
        require ROOT_PATH . '/modules/carte/view.php';
        break;

    case 'clienti':
        require ROOT_PATH . '/modules/clienti/view.php';
        break;

    case 'riscatti':
        require ROOT_PATH . '/modules/riscatti/view.php';
        break;

    case 'gratta':
        require ROOT_PATH . '/modules/gratta/view.php';
        break;
}

require ROOT_PATH . '/themes/semplice/footer.php';
