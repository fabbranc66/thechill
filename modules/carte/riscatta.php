<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

$id = (int)($_GET['id'] ?? 0);

/* recupera carta e cliente */
$stmt = $pdo->prepare(
    "SELECT c.id, c.codice_carta, c.punti, u.nome
     FROM carte_fedelta c
     JOIN utenti u ON u.id = c.utente_id
     WHERE c.id = ?"
);
$stmt->execute([$id]);
$carta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carta) {
    die('Carta non trovata');
}

/* ==========================================================
   GESTIONE RISCATTO PREMIO
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['premio_id'])) {

    $premio_id = (int)$_POST['premio_id'];

    $stmt = $pdo->prepare(
        "SELECT id, punti_necessari
         FROM premi
         WHERE id = ?"
    );
    $stmt->execute([$premio_id]);
    $premio = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($premio && $premio['punti_necessari'] <= $carta['punti']) {

        $punti_scalati = (int)$premio['punti_necessari'];
        $nuovi_punti = $carta['punti'] - $punti_scalati;

        $admin_id = $_SESSION['utente']['id'] ?? null;

        $pdo->beginTransaction();

        /* scala punti */
        $stmt = $pdo->prepare(
            "UPDATE carte_fedelta
             SET punti = ?
             WHERE id = ?"
        );
        $stmt->execute([$nuovi_punti, $id]);

        /* registra riscatto */
        $stmt = $pdo->prepare(
            "INSERT INTO riscatti_premi
             (carta_id, premio_id, admin_id, punti_scalati, riscattato)
             VALUES (?, ?, ?, ?, 1)"
        );
        $stmt->execute([
            $id,
            $premio_id,
            $admin_id,
            $punti_scalati
        ]);

        $pdo->commit();
    }

    header('Location: ' . BASE_URL . '/?mod=admin&tab=carte');
    exit;
}

/* ==========================================================
   PREMI DISPONIBILI
========================================================== */
$stmt = $pdo->prepare(
    "SELECT id, nome, punti_necessari
     FROM premi
     WHERE punti_necessari <= ?
     ORDER BY punti_necessari"
);
$stmt->execute([$carta['punti']]);
$premi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titolo = 'Riscatta premio';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2>Riscatta premio</h2>

<p>
    <strong>Cliente:</strong> <?= htmlspecialchars($carta['nome']) ?><br>
    <strong>Carta:</strong> <?= htmlspecialchars($carta['codice_carta']) ?><br>
    <strong>Punti disponibili:</strong> <?= (int)$carta['punti'] ?>
</p>

<hr>

<?php if (!$premi): ?>
    <p>Nessun premio disponibile con i punti attuali.</p>
<?php else: ?>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Premio</th>
        <th>Punti richiesti</th>
        <th>Azione</th>
    </tr>

    <?php foreach ($premi as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['nome']) ?></td>
        <td><?= (int)$p['punti_necessari'] ?></td>
        <td>
            <form method="post" style="display:inline">
                <input type="hidden" name="premio_id" value="<?= $p['id'] ?>">
                <button>🎁 Riscatta</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php endif; ?>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
