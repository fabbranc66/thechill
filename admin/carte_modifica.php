<?php
declare(strict_types=1);

/* ==========================================================
   INIZIALIZZAZIONE
========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');

/* ==========================================================
   CONTROLLO ID
========================================================== */
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID non valido');
}

$carta_id = (int)$_GET['id'];

/* ==========================================================
   LETTURA CARTA + CLIENTE
========================================================== */
$stmt = $pdo->prepare("
    SELECT
        c.id,
        c.codice_carta,
        c.punti,
        u.id AS utente_id,
        u.nome,
        u.token_accesso
    FROM carte_fedelta c
    JOIN utenti u ON u.id = c.utente_id
    WHERE c.id = ?
    LIMIT 1
");
$stmt->execute([$carta_id]);
$carta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carta) {
    die('Carta non trovata');
}

/* ==========================================================
   LINK CLIENTE E QR DINAMICO
========================================================== */
$clienteLink = BASE_URL_FULL . '/cliente.php?t=' . $carta['token_accesso'];
$qrUrl       = BASE_URL_FULL . '/qr.php?t=' . $carta['token_accesso'];

/* ==========================================================
   TEMPLATE
========================================================== */
$titolo = 'Modifica carta';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>Modifica carta</h2>

<p><strong>Cliente:</strong> <?= htmlspecialchars($carta['nome']) ?></p>
<p><strong>Codice carta:</strong> <?= htmlspecialchars($carta['codice_carta']) ?></p>
<p><strong>Punti:</strong> <?= (int)$carta['punti'] ?></p>

<hr>

<h3>QR Code</h3>

<img src="<?= $qrUrl ?>" alt="QR Code">

<p>
    <a href="<?= $qrUrl ?>" target="_blank">
        Apri QR a dimensione reale
    </a>
</p>

<hr>

<p>
    <strong>Link cliente:</strong><br>
    <a href="<?= $clienteLink ?>" target="_blank">
        <?= $clienteLink ?>
    </a>
</p>

<hr>

<a href="index.php?tab=carte">⬅️ Torna alle carte</a>

<?php require __DIR__ . '/../themes/semplice/footer.php'; ?>
