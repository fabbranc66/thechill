<?php
declare(strict_types=1);

/* ==========================================================
   MODULO SCANSIONI
   VIEW KIOSK (solo operatori – modalità cellulare)
========================================================== */

richiedi_ruolo('amministratore');

/* esegue eventuale logica scansione */
require __DIR__ . '/actions.php';

$titolo = 'Scanner';
?>

<h2 style="text-align:center">Scanner QR</h2>

<!-- ======================================================
     MESSAGGIO RISULTATO SCANSIONE
====================================================== -->
<div id="msg" style="text-align:center;
                     font-size:18px;
                     font-weight:bold;
                     min-height:40px;
                     margin:15px 0;">
<?php
if (!empty($_GET['ok'])) {
    echo '<span style="color:green">' . htmlspecialchars($_GET['ok']) . '</span>';
}
if (!empty($_GET['err'])) {
    echo '<span style="color:red">' . htmlspecialchars($_GET['err']) . '</span>';
}
?>
</div>

<!-- ======================================================
     AREA SCANNER
====================================================== -->
<div id="scanner-wrapper">
    <div id="qr-reader"></div>
</div>

<!-- ======================================================
     LIBRERIA SCANNER
====================================================== -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
/* ======================================================
   UTILITY MESSAGGI
====================================================== */
function showMessage(text, success = true) {
    const el = document.getElementById('msg');
    el.textContent = text;
    el.style.color = success ? 'green' : 'red';
}

/* ======================================================
   CALLBACK SCANSIONE
====================================================== */
function onScanSuccess(decodedText) {

    showMessage("Scansione in corso...", true);

    // redirect al modulo azioni scansione
    window.location.href =
        "<?= BASE_URL ?>/?mod=scansioni&codice=" +
        encodeURIComponent(decodedText);
}

/* ======================================================
   AVVIO SCANNER CON CAMERA POSTERIORE
====================================================== */
const qr = new Html5Qrcode("qr-reader");

Html5Qrcode.getCameras().then(cameras => {

    if (!cameras || cameras.length === 0) {
        showMessage("Nessuna fotocamera trovata", false);
        return;
    }

    /* cerca camera posteriore */
    let backCamera = cameras.find(cam =>
        cam.label.toLowerCase().includes('back') ||
        cam.label.toLowerCase().includes('rear') ||
        cam.label.toLowerCase().includes('environment')
    );

    const cameraId = backCamera ? backCamera.id : cameras[0].id;

    qr.start(
        cameraId,
        {
            fps: 10,
            qrbox: 240
        },
        onScanSuccess
    );

}).catch(err => {
    showMessage("Errore accesso fotocamera", false);
});

/* ======================================================
   RITORNO AUTOMATICO DOPO ESITO
====================================================== */
<?php if (!empty($_GET['ok']) || !empty($_GET['err'])): ?>
setTimeout(() => {
    window.location.href = "<?= BASE_URL ?>/?mod=admin";
}, 5000);
<?php endif; ?>
</script>

<?php
?>
