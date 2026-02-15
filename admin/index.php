<?php
declare(strict_types=1);

/* ==========================================================
   DEBUG (SVILUPPO)
========================================================== */
ini_set('display_errors', '1');
error_reporting(E_ALL);

/* ==========================================================
   INIZIALIZZAZIONE
========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

/* ==========================================================
   MODULI
========================================================== */
require __DIR__ . '/../modules/clienti/query.php';
require __DIR__ . '/../modules/clienti/view.php';

require __DIR__ . '/../modules/scansioni/query.php';
require __DIR__ . '/../modules/scansioni/view.php';

require __DIR__ . '/../modules/carte/query.php';
require __DIR__ . '/../modules/carte/view.php';

require __DIR__ . '/../modules/riscatti/query.php';
require __DIR__ . '/../modules/riscatti/view.php';

require __DIR__ . '/../modules/gratta/query.php';
require __DIR__ . '/../modules/gratta/view.php';

/* ==========================================================
   SICUREZZA
========================================================== */
richiedi_ruolo('amministratore');

/* ==========================================================
   CONFIGURAZIONE
========================================================== */
$tab = $_GET['tab'] ?? 'scansioni';

$tab_validi = ['scansioni','carte','clienti','riscatti','gratta'];
if (!in_array($tab, $tab_validi, true)) {
    $tab = 'scansioni';
}

/* ==========================================================
   AZIONI POST
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* --- ELIMINA SCANSIONE --- */
    if (isset($_POST['del_scansione'])) {
        $id = (int)$_POST['del_scansione'];
        try {
            $pdo->beginTransaction();
            $s = $pdo->prepare('SELECT carta_id,punti FROM log_scansioni WHERE id=?');
            $s->execute([$id]);
            if ($row = $s->fetch()) {
                $pdo->prepare(
                    'UPDATE carte_fedelta SET punti = GREATEST(punti-?,0) WHERE id=?'
                )->execute([(int)$row['punti'], (int)$row['carta_id']]);
                $pdo->prepare('DELETE FROM log_scansioni WHERE id=?')->execute([$id]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
        }
        header('Location: index.php?tab=scansioni');
        exit;
    }

    /* --- ELIMINA CARTA --- */
    if (isset($_POST['del_carta'])) {
        $id = (int)$_POST['del_carta'];
        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM log_scansioni WHERE carta_id=?')->execute([$id]);
            $pdo->prepare('DELETE FROM carte_fedelta WHERE id=?')->execute([$id]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
        }
        header('Location: index.php?tab=carte');
        exit;
    }

    /* --- ELIMINA CLIENTE --- */
    if (isset($_POST['del_cliente'])) {
        $id = (int)$_POST['del_cliente'];
        try {
            $pdo->beginTransaction();
            $pdo->prepare(
                'DELETE l FROM log_scansioni l
                 JOIN carte_fedelta c ON c.id=l.carta_id
                 WHERE c.utente_id=?'
            )->execute([$id]);
            $pdo->prepare('DELETE FROM carte_fedelta WHERE utente_id=?')->execute([$id]);
            $pdo->prepare('DELETE FROM utenti WHERE id=?')->execute([$id]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
        }
        header('Location: index.php?tab=clienti');
        exit;
    }
}

/* ==========================================================
   STATISTICHE
========================================================== */
$clienti = (int)$pdo->query("SELECT COUNT(*) FROM utenti WHERE ruolo='cliente'")->fetchColumn();
$carte = (int)$pdo->query("SELECT COUNT(*) FROM carte_fedelta")->fetchColumn();
$punti = (int)$pdo->query("SELECT COALESCE(SUM(punti),0) FROM carte_fedelta")->fetchColumn();
$scansioni_totali = (int)$pdo->query("SELECT COUNT(*) FROM log_scansioni")->fetchColumn();
$regali_riscattati = (int)$pdo->query("SELECT COUNT(*) FROM riscatti_premi WHERE riscattato=1")->fetchColumn();
$gratta_riscattati = (int)$pdo->query("SELECT COUNT(*) FROM gratta_vinci WHERE riscattato=1")->fetchColumn();

/* ==========================================================
   FLAG PREMI
========================================================== */
$premi_riscattabili = (bool)$pdo->query(
    "SELECT 1
     FROM carte_fedelta c
     JOIN premi p ON p.attivo=1
     WHERE c.punti >= p.punti_necessari
     LIMIT 1"
)->fetchColumn();

/* ==========================================================
   DATI TAB
========================================================== */
$scansioni = $carte_list = $utenti = $riscatti = $gratta = [];

switch ($tab) {
    case 'scansioni':
        $scansioni = scansioni_lista($pdo);
        break;

    case 'carte':
        $carte_list = carte_lista($pdo);
        break;

    case 'clienti':
        $utenti = clienti_lista($pdo);
        break;

    case 'riscatti':
        $riscatti = riscatti_lista($pdo);
        break;

    case 'gratta':
        $gratta = gratta_lista($pdo);
        break;
}

/* ==========================================================
   TEMPLATE
========================================================== */
$titolo = 'Dashboard';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>Dashboard</h2>

<!-- STATISTICHE -->
<div class="stats">
  <div class="box">👥<br><?= $clienti ?><br>Clienti</div>
  <div class="box">💳<br><?= $carte ?><br>Carte</div>
  <div class="box">🏆<br><?= $punti ?><br>Punti</div>
  <div class="box">📷<br><?= $scansioni_totali ?><br>Scansioni</div>
  <div class="box">🎁<br><?= $regali_riscattati ?><br>Riscattati</div>
  <div class="box">🏁<br><?= $gratta_riscattati ?><br>Riscattati</div>
</div>

<?php if ($premi_riscattabili): ?>
  <div style="margin:20px 0;padding:14px;text-align:center;background:#fff8d6;border:2px solid #f1c40f;border-radius:8px;font-weight:600;">
    🎁 Sono presenti premi da riscattare —
    <a href="?tab=carte" style="text-decoration:underline">vai alle carte</a>
  </div>
<?php endif; ?>

<!-- AZIONI PRINCIPALI -->
<div class="main-actions">
  <a href="nuova_fidelity.php" class="btn-nuova-carta">➕ Nuova carta</a>
  <a href="premi.php" class="btn-nuova-carta">🎁 Gestione premi</a>
  <a href="gratta_impostazioni.php" class="btn-nuova-carta">🎁 Gratta e Vinci</a>
  <a href="gallery.php" class="btn-nuova-carta">🎨 Gestione gallery</a>
  <a href="impostazioni.php" class="btn-nuova-carta">⚙️ Impostazioni</a>

<?php if (is_mobile_device()): ?>
  <a href="scanner.php" class="btn-nuova-carta">📷 Scansiona</a>
<?php endif; ?>

  <a href="<?= BASE_URL ?>/modules/tables-v2/index.php" class="btn-nuova-carta">
    🪑 Gestione tavoli
  </a>
</div>

<!-- SWITCH TAB -->
<div class="switch" style="display:flex;gap:8px;flex-wrap:wrap;margin:15px 0">
  <a href="?tab=scansioni" class="<?= $tab==='scansioni'?'on':'' ?>">Scansioni</a>
  <a href="?tab=carte" class="<?= $tab==='carte'?'on':'' ?>">Carte</a>
  <a href="?tab=clienti" class="<?= $tab==='clienti'?'on':'' ?>">Clienti</a>
  <a href="?tab=riscatti" class="<?= $tab==='riscatti'?'on':'' ?>">🎁 Riscatti</a>
  <a href="?tab=gratta" class="<?= $tab==='gratta'?'on':'' ?>">🎁 Gratta</a>
</div>

<?php
switch ($tab) {
    case 'scansioni':
        scansioni_render_tabella($scansioni);
        break;

    case 'carte':
        carte_render_tabella($carte_list);
        break;

    case 'clienti':
        clienti_render_tabella($utenti);
        break;

    case 'riscatti':
        riscatti_render_tabella($riscatti);
        break;

    case 'gratta':
        gratta_render_tabella($gratta);
        break;
}

require __DIR__ . '/../themes/semplice/footer.php';
