<?php
// admin/carta_ok.php
// MODE: CODEX — FIX DEFINITIVO (FILE IN /admin)

// ✔ il file È in /admin
// ✔ path corretti
// ✔ accesso pubblico (NO auth admin)

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID CARTA NON VALIDO');
}

$carta_id = (int)$_GET['id'];

/* =========================
   LETTURA CARTA
========================= */
$stmt = $pdo->prepare("
    SELECT c.id,
           c.codice_carta,
           c.punti,
           u.nome
    FROM carte_fedelta c
    JOIN utenti u ON u.id = c.utente_id
    WHERE c.id = ?
    LIMIT 1
");
$stmt->execute([$carta_id]);
$carta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carta) {
    die('CARTA NON TROVATA');
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Carta valida</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body{font-family:sans-serif;background:#f6f6f6;padding:20px}
        .card{background:#fff;padding:20px;border-radius:10px;max-width:420px;margin:auto}
        h1{color:#0b3d2e;text-align:center}
    </style>
</head>
<body>

<div class="card">
    <h1>✅ Carta valida</h1>

    <p><strong>Cliente:</strong> <?= htmlspecialchars($carta['nome']) ?></p>
    <p><strong>Codice carta:</strong> <?= htmlspecialchars($carta['codice_carta']) ?></p>
    <p><strong>Punti:</strong> <?= (int)$carta['punti'] ?></p>
</div>

</body>
</html>