<?php
declare(strict_types=1);
$titolo = 'Scanner';
require ROOT_PATH . '/themes/semplice/header.php';
?>
<h2>Scanner cassa</h2>
<?php if (!empty($messaggio)): ?>
<div class="alert-premio"><?= htmlspecialchars($messaggio) ?></div>
<?php endif; ?>
<?php if (!empty($errore)): ?>
<div style="color:red"><?= htmlspecialchars($errore) ?></div>
<?php endif; ?>
<form method="post">
<input name="codice" autofocus style="font-size:18px;padding:10px">
</form>
<script>
setTimeout(() => {
    location.href = "<?= BASE_URL ?>/?mod=scansioni";
}, 5000);
</script>
<?php require ROOT_PATH . '/themes/semplice/footer.php'; ?>
