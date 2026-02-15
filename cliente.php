<?php
declare(strict_types=1);

require __DIR__ . '/config/db.php';
require __DIR__ . '/moduli/clienti/query.php';

$token = $_GET['t'] ?? '';

if ($token === '') {
    die('Accesso non valido');
}

$cliente = clienti_by_token($pdo, $token);

if (!$cliente) {
    die('Cliente non trovato');
}

$punti = $cliente['punti'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Area Cliente</title>
</head>
<body>

<h1><?= htmlspecialchars($cliente['nome']) ?></h1>

<h2>Punti: <?= (int)$punti ?></h2>

<h3>Il tuo QR personale</h3>
<img src="<?= BASE_URL ?>/assets/qr/<?= htmlspecialchars($cliente['token_accesso']) ?>.png"
     width="200" alt="QR">

</body>
</html>
