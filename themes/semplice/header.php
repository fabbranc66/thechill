<?php
declare(strict_types=1);

/* ==========================================================
   TEMA – HEADER
   Compatibile con struttura modulare THECHILL
   ========================================================== */
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <!-- ======================================================
       META BASE
       ====================================================== -->
  <meta charset="utf-8">

<?php if (!empty($SETTINGS['favicon'])): ?>
  <link rel="icon"
        type="image/x-icon"
        href="<?= BASE_URL ?>/assets/img/<?= htmlspecialchars($SETTINGS['favicon']) ?>">
<?php endif; ?>

  <title><?= htmlspecialchars($titolo ?? ($SETTINGS['site_name'] ?? 'THECHILL')) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- ======================================================
       CSS TEMA
       ====================================================== -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/themes/semplice/style.css">
</head>
<body>

<!-- ========================================================
     HEADER SITO
     ======================================================== -->
<header class="site-header">
  <div class="container">

    <!-- LOGO / BRAND -->
    <div class="logo">
      <a href="<?= BASE_URL ?>/">

        <?php if (!empty($SETTINGS['logo'])): ?>
          <img
            src="<?= BASE_URL ?>/assets/img/<?= htmlspecialchars($SETTINGS['logo']) ?>"
            alt="<?= htmlspecialchars($SETTINGS['site_name'] ?? 'THECHILL') ?>"
            class="site-logo"
          >
        <?php else: ?>
          <?= htmlspecialchars($SETTINGS['site_name'] ?? 'THECHILL') ?>
        <?php endif; ?>

      </a>

      <!-- ===== TIMER SESSIONE ===== -->
<?php if (isset($_SESSION['utente'], $_SESSION['EXPIRE_AT'])): ?>
        <div id="session-timer"
             style="font-size:12px;opacity:.75;margin-top:2px">
          Sessione: --
        </div>

        <script>
        (function () {
          const expireAt = <?= (int)$_SESSION['EXPIRE_AT'] ?> * 1000;
          const el = document.getElementById('session-timer');

          function update() {
            const now = Date.now();
            let remaining = Math.floor((expireAt - now) / 1000);

            if (remaining <= 0) {
              el.textContent = 'Sessione scaduta';
              return;
            }

            const h = Math.floor(remaining / 3600);
            const m = Math.floor((remaining % 3600) / 60);
            const s = remaining % 60;

            el.textContent =
              'Sessione: ' +
              String(h).padStart(2, '0') + ':' +
              String(m).padStart(2, '0') + ':' +
              String(s).padStart(2, '0');
          }

          update();
          setInterval(update, 1000);
        })();
        </script>
<?php endif; ?>
      <!-- ===== FINE TIMER ===== -->

    </div>

    <!-- ====================================================
         NAVIGAZIONE
         ==================================================== -->
<?php
$mod = $_GET['mod'] ?? '';
$azione = $_GET['azione'] ?? '';
$pagina_cliente_pubblica = ($mod === 'clienti' && $azione === 'cliente');
?>
<nav class="nav">
  <?php if (empty($_SESSION['utente']) && !$pagina_cliente_pubblica): ?>

    <a href="<?= BASE_URL ?>/?mod=login">Accedi</a>

  <?php elseif (!empty($_SESSION['utente'])): ?>

    <?php if ($_SESSION['utente']['ruolo'] === 'amministratore'): ?>
      <a href="<?= BASE_URL ?>/?mod=admin">Dashboard</a>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/?mod=logout">Logout</a>

  <?php endif; ?>
</nav>
  </div>
</header>

<!-- ========================================================
     CONTENUTO PRINCIPALE
     ======================================================== -->
<main class="content">
