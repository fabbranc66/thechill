<?php
require __DIR__ . '/lib/phpqrcode/qrlib.php';

$dir = __DIR__ . '/assets/qr/';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$file = $dir . 'test.png';

QRcode::png('test qr', $file, QR_ECLEVEL_L, 6);

echo 'QR creato: ' . $file;
