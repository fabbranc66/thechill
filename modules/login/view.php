<?php
declare(strict_types=1);

// esegue logica UNA SOLA VOLTA
require_once __DIR__ . '/actions.php';

$titolo = 'Login';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2>Accesso</h2>

<?php if (!empty($messaggio)): ?>
    <div style="color:green;margin-bottom:15px">
        <?= htmlspecialchars($messaggio) ?>
    </div>
<?php endif; ?>

<?php if (!empty($errore)): ?>
    <div style="color:#900;margin-bottom:15px">
        <?= htmlspecialchars($errore) ?>
    </div>
<?php endif; ?>

<form method="post" style="max-width:400px">

    <label>Email</label>
    <input name="email" type="email" required style="width:100%;padding:8px">

    <label>Password</label>
    <input name="password" type="password" required style="width:100%;padding:8px">

    <br><br>

    <button>Accedi</button>
</form>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
