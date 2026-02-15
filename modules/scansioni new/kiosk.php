<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Scanner Kiosk</title>
<style>
body{margin:0;background:#000;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;flex-direction:column;font-family:sans-serif}
.msg{font-size:32px;margin-top:20px}
.ok{color:#2ecc71}
.err{color:#e74c3c}
</style>
</head>
<body>
<h1>Scanner attivo</h1>
<?php if (!empty($messaggio)): ?>
<div class="msg ok"><?= htmlspecialchars($messaggio) ?></div>
<?php endif; ?>
<?php if (!empty($errore)): ?>
<div class="msg err"><?= htmlspecialchars($errore) ?></div>
<?php endif; ?>
<form method="post">
<input name="codice" autofocus style="font-size:22px;padding:12px">
</form>
<script>
setTimeout(() => {
    location.href = "<?= BASE_URL ?>/?mod=scansioni";
}, 5000);
</script>
</body>
</html>
