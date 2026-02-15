<?php
declare(strict_types=1);

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
    if (devices && devices.length) {
        html5QrCode.start(
            devices[0].id,
            {
                fps: 10,
                qrbox: 220
            },
            onScanSuccess
        );
    }
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
