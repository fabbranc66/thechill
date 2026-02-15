<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/init.php';
require __DIR__ . '/query.php';

$token = $_GET['t'] ?? '';

if ($token === '') {
    die('Accesso non valido');
}

/* cliente + punti carta */
$stmt = $pdo->prepare(
    "SELECT 
        u.id,
        u.nome,
        u.token_accesso,
        c.punti
     FROM utenti u
     LEFT JOIN carte_fedelta c ON c.utente_id = u.id
     WHERE u.token_accesso = ?
     AND u.ruolo = 'cliente'
     LIMIT 1"
);
$stmt->execute([$token]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die('Cliente non trovato');
}

/* percorso tema */
$theme_path = realpath(__DIR__ . '/../../themes/semplice/') . DIRECTORY_SEPARATOR;

/* QR corretto */
$qr_url = BASE_URL . '/assets/qr/' . $cliente['token_accesso'] . '.png';

$titolo = 'Area cliente';
require $theme_path . 'header.php';
?>

<div class="container" style="max-width:480px;margin:40px auto;text-align:center">

    <h1 style="margin-bottom:5px">
        <?= htmlspecialchars($cliente['nome']) ?>
    </h1>

    <div style="
        background:#f5f5f5;
        padding:15px;
        border-radius:8px;
        margin:15px 0 25px 0;
    ">
        <div style="font-size:14px;color:#666">Punti disponibili</div>
        <div style="font-size:32px;font-weight:bold">
            <?= (int)($cliente['punti'] ?? 0) ?>
        </div>
    </div>

    <div style="margin:20px 0">
        <img
            src="<?= $qr_url ?>"
            width="240"
            alt="QR Code"
            style="display:block;margin:0 auto"
        >
    </div>

    <p style="font-size:14px;color:#666;margin-top:15px">
        Mostra questo QR alla cassa per accumulare o utilizzare i punti.
    </p>

</div>

<?php
require $theme_path . 'footer.php';
