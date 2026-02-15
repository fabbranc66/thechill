<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

$azione = $_GET['azione'] ?? '';

if ($azione === 'nuovo') {
    require __DIR__ . '/nuovo.php';
} elseif ($azione === 'cliente') {
    require __DIR__ . '/cliente.php';
} else {
    require __DIR__ . '/lista.php';
}
