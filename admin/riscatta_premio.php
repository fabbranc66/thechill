<?php
declare(strict_types=1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');

/* =========================
   INPUT
========================= */
$carta_id = (int)($_GET['carta_id'] ?? 0);
if ($carta_id <= 0) {
    header('Location: index.php?tab=carte');
    exit;
}

/* =========================
   CARICA CARTA
========================= */
$stmt = $pdo->prepare(
    "SELECT c.id, c.codice_carta, c.punti, u.nome
     FROM carte_fedelta c
     JOIN utenti u ON u.id=c.utente_id
     WHERE c.id=?"
);
$stmt->execute([$carta_id]);
$carta = $stmt->fetch();

if (!$carta) {
    header('Location: index.php?tab=carte');
    exit;
}

/* =========================
   PREMI RISCATTABILI
========================= */
$stmt = $pdo->prepare(
    "SELECT id, nome, punti_necessari
     FROM premi
     WHERE attivo=1 AND punti_necessari <= ?
     ORDER BY punti_necessari ASC"
);
$stmt->execute([(int)$carta['punti']]);
$premi = $stmt->fetchAll();

/* =========================
   RISCATTO (POST) + DEBUG
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['premio_id'])) {

  $premio_id = (int) $_POST['premio_id'];

  try {

    $pdo->beginTransaction();

    /* DEBUG */
    if ($premio_id <= 0) {
      throw new RuntimeException('ID premio non valido');
    }

    /* =========================
       LOCK PREMIO
    ========================= */
    $stmt = $pdo->prepare(
      "SELECT id, punti_necessari
       FROM premi
       WHERE id = ? AND attivo = 1
       FOR UPDATE"
    );
    $stmt->execute([$premio_id]);
    $premio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$premio) {
      throw new RuntimeException('Premio non trovato o non attivo');
    }

    if ((int)$premio['punti_necessari'] > (int)$carta['punti']) {
      throw new RuntimeException('Punti insufficienti');
    }

    /* =========================
       SCALA PUNTI
    ========================= */
    $stmt = $pdo->prepare(
      "UPDATE carte_fedelta
       SET punti = punti - ?
       WHERE id = ?"
    );
    $stmt->execute([
      (int)$premio['punti_necessari'],
      (int)$carta_id
    ]);

    if ($stmt->rowCount() !== 1) {
      throw new RuntimeException('Aggiornamento punti fallito');
    }

    /* =========================
       REGISTRA RISCATTO
    ========================= */

   
$stmt = $pdo->prepare(
  "INSERT INTO riscatti_premi
   (carta_id, premio_id, punti_scalati, admin_id, riscattato)
   VALUES (?,?,?,?,1)"
);
    $stmt->execute([
      (int)$carta_id,
      (int)$premio_id,
      (int)$premio['punti_necessari'],
      (int)$_SESSION['utente']['id']
    ]);

    $pdo->commit();

    header('Location: index.php?tab=carte');
    exit;

  } catch (Throwable $e) {

    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }

    /* DEBUG VISIBILE */
    $errore = '❌ ERRORE RISCATTO: ' . $e->getMessage();
  }
}


$titolo = 'Riscatto premio';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>Riscatto premio</h2>

<p>
<strong>Cliente:</strong> <?= htmlspecialchars($carta['nome']) ?><br>
<strong>Codice carta:</strong> <?= htmlspecialchars($carta['codice_carta']) ?><br>
<strong>Punti disponibili:</strong> <?= (int)$carta['punti'] ?>
</p>

<?php if (!empty($errore)): ?>
<p style="color:red"><?= htmlspecialchars($errore) ?></p>
<?php endif; ?>

<?php if (!$premi): ?>
<p>❌ Nessun premio riscattabile con i punti attuali.</p>
<a href="index.php?tab=carte">⬅ Torna</a>
<?php else: ?>
<form method="post">
<table>
<tr><th>Premio</th><th>Punti</th><th></th></tr>
<?php foreach ($premi as $p): ?>
<tr>
<td><?= htmlspecialchars($p['nome']) ?></td>
<td><?= (int)$p['punti_necessari'] ?></td>
<td>
<button type="submit" name="premio_id"
        value="<?= $p['id'] ?>"
        onclick="return confirm('Confermare riscatto premio?')">
🎁 Riscatta
</button>
</td>
</tr>
<?php endforeach; ?>
</table>
</form>
<?php endif; ?>

<style>
table{width:100%;border-collapse:collapse}
td,th{border:1px solid #ccc;padding:6px}
button{cursor:pointer}
</style>

<?php require __DIR__ . '/../themes/semplice/footer.php'; ?>