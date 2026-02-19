<!DOCTYPE html>
<html>
<head>
    <title>Configurazioni Sistema</title>
</head>
<body>

<h2>Configurazioni Sistema</h2>

<form method="post">

<?php foreach ($config as $riga): ?>

    <div style="margin-bottom:15px;">
        <label>
            <strong><?= htmlspecialchars($riga['chiave']) ?></strong>
        </label><br>

        <input 
            type="text"
            name="<?= htmlspecialchars($riga['chiave']) ?>"
            value="<?= htmlspecialchars($riga['valore']) ?>"
            style="width:300px;"
        ><br>

        <small><?= htmlspecialchars($riga['descrizione']) ?></small>
    </div>

<?php endforeach; ?>

<button type="submit">Salva</button>

</form>

</body>
</html>
