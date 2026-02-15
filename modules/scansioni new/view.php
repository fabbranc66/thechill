<?php
declare(strict_types=1);
require __DIR__ . '/actions.php';
$modo = $SETTINGS['scanner_mode'] ?? 'cassa';
if ($modo === 'kiosk') require __DIR__ . '/kiosk.php';
else require __DIR__ . '/cassa.php';
