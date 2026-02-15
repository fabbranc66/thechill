<?php
declare(strict_types=1);

/* ==========================================================
   MODULO CARTE
   VISUALIZZAZIONE TESSERA
========================================================== */

$token = $_GET['t'] ?? '';

if ($token === '') {
    exit('Token mancante');
}

$stmt = $pdo->prepare(
    "SELECT u.nome, c.punti
     FROM utenti u
     JOIN carte_fedelta c ON c.utente_id = u.id
     WHERE u.token_accesso = ?
     LIMIT 1"
);
$stmt->execute([$token]);

$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    exit('Tessera non trovata');
}

$nome_cliente = $cliente['nome'];
$punti = (int)$cliente['punti'];
$tessera_url = BASE_URL . '/public/upload/tessere/' . $token . '.png';
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($nome_cliente) ?> - Tessera fedeltà</title>

<style>
body {
    font-family: Arial, sans-serif;
    text-align: center;
    background: #111;
    color: white;
    padding: 30px;
}
.card {
    max-width: 420px;
    margin: auto;
}
img {
    width: 100%;
    border-radius: 12px;
    margin-top: 20px;
}
.error {
    color: #ff8080;
}
</style>
</head>
<body>

<div class="card">
    <h2><?= htmlspecialchars($nome_cliente) ?></h2>
    <p>Punti: <strong><?= $punti ?></strong></p>

    <img src="<?= htmlspecialchars($tessera_url) ?>" alt="Tessera fedeltà">
</div>

</body>
</html>
