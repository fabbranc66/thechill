<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

/* recupero setting scanner desktop */
$stmt = $pdo->prepare(
    "SELECT valore FROM settings WHERE nome = 'scanner_desktop' LIMIT 1"
);
$stmt->execute();
$scanner_desktop = (string)($stmt->fetchColumn() ?? '0');

/* se abilitato, consenti sempre accesso */
if ($scanner_desktop !== '1') {
    if (!is_mobile_device()) {
        echo '<h2>Scanner nn disponibile</h2>';
        echo '<p>Lo scanner è utilizzabile solo da dispositivo mobile.</p>';
        exit;
    }
}

$titolo = 'Scanner';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2>Scanner QR</h2>

<?php if (!empty($_GET['ok'])): ?>
<div id="msg" style="color:green">
    <?= htmlspecialchars($_GET['ok']) ?>
</div>
<?php endif; ?>

<?php if (!empty($_GET['err'])): ?>
<div id="msg" style="color:red">
    <?= htmlspecialchars($_GET['err']) ?>
</div>
<?php endif; ?>

<div id="scanner-wrapper">
    <div id="qr-reader"></div>
</div>

<form id="scan-form" method="post">
    <input type="hidden" name="codice" id="codice">
</form>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
function onScanSuccess(decodedText) {
    document.getElementById('codice').value = decodedText;
    document.getElementById('scan-form').submit();
}

const html5QrCode = new Html5Qrcode("qr-reader");

Html5Qrcode.getCameras().then(devices => {
    if (!devices || !devices.length) return;

    let cameraId = devices[0].id;

    // cerca camera posteriore
    for (let device of devices) {
        const label = device.label.toLowerCase();
        if (
            label.includes('back') ||
            label.includes('rear') ||
            label.includes('environment')
        ) {
            cameraId = device.id;
            break;
        }
    }

    html5QrCode.start(
        cameraId,
        {
            fps: 10,
            qrbox: 220
        },
        onScanSuccess
    );
});

</script>

<script>
/* nasconde messaggio dopo 5 secondi */
setTimeout(() => {
    const msg = document.getElementById('msg');
    if (msg) msg.style.display = 'none';
}, 5000);
</script>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
