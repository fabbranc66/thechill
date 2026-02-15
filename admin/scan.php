<?php
declare(strict_types=1);

/* ==========================================================
   BOOTSTRAP
========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

/* ==========================================================
   MODULO CARTE
========================================================== */
require __DIR__ . '/../modules/carte/actions.php';

/* ==========================================================
   SICUREZZA
========================================================== */
richiedi_ruolo('amministratore');

/* ==========================================================
   INPUT TOKEN
========================================================== */
$token = $_GET['t'] ?? '';

if ($token === '') {
    exit('Token non valido');
}

/* ==========================================================
   CONFIGURAZIONE PUNTI
========================================================== */
$punti_scan = 1;

/* ==========================================================
   INCREMENTO PUNTI
========================================================== */
$ok = carte_incrementa_punti($pdo, $token, $punti_scan, 'qr');

if (!$ok) {
    exit('Errore durante la scansione');
}

/* ==========================================================
   TEMPLATE
========================================================== */
$titolo = 'Scansione completata';
require __DIR__ . '/../themes/semplice/header.php';
?>

<div style="max-width:420px;margin:60px auto;text-align:center;">

    <h2>✅ Punto assegnato</h2>

    <p style="font-size:18px">
        Token cliente:<br>
        <strong><?= htmlspecialchars($token) ?></strong>
    </p>

    <p style="font-size:22px;margin:20px 0">
        ➕ <?= $punti_scan ?> punto
    </p>

    <p style="opacity:.7">
        Scansiona subito un’altra carta
    </p>

    <form action="<?= BASE_URL ?>/admin/scan.php" method="get">
        <input
            type="file"
            name="t"
            accept="image/*"
            capture="environment"
            style="display:none"
            onchange="this.form.submit()"
        >

        <button
            type="button"
            onclick="this.previousElementSibling.click()"
            style="font-size:16px;padding:12px 18px;cursor:pointer;margin-top:10px;"
        >
            📷 Scansiona un’altra carta
        </button>
    </form>

    <p style="margin-top:24px">
        <a href="<?= BASE_URL ?>/admin/index.php"
           style="font-size:14px;opacity:.7">
            Vai alla dashboard
        </a>
    </p>

</div>

<?php require __DIR__ . '/../themes/semplice/footer.php'; ?>
