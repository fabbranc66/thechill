<?php
declare(strict_types=1);

require __DIR__ . '/actions.php';
require __DIR__ . '/query.php';

$carta_id = (int)($_GET['carta_id'] ?? 0);

if ($carta_id <= 0) {
    header('Location: ' . BASE_URL . '/?mod=admin&tab=carte');
    exit;
}

$carta = premi_carica_carta($pdo, $carta_id);
if (!$carta) {
    exit('Carta non trovata');
}

$premi = premi_riscattabili($pdo, (int)$carta['punti']);

$titolo = 'Riscatto premio';
require ROOT_PATH . '/themes/semplice/header.php';
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
<p>❌ Nessun premio riscattabile.</p>
<a href="<?= BASE_URL ?>/?mod=admin&tab=carte">⬅ Torna</a>
<?php else: ?>

<table>
<tr><th>Premio</th><th>Punti</th><th></th></tr>
<?php foreach ($premi as $p): ?>
<tr>
<td><?= htmlspecialchars($p['nome']) ?></td>
<td><?= (int)$p['punti_necessari'] ?></td>
<td>
<form method="post" style="display:inline">
    <input type="hidden" name="carta_id" value="<?= $carta_id ?>">
    <input type="hidden" name="premio_id" value="<?= $p['id'] ?>">
    <button type="submit" name="riscatta_premio"
            onclick="return confirm('Confermare riscatto premio?')">
        🎁 Riscatta
    </button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>

<?php endif; ?>

<?php require ROOT_PATH . '/themes/semplice/footer.php'; ?>
