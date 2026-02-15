<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

/* actions sempre caricato per gestire POST */
require __DIR__ . '/actions.php';

$azione = $_GET['azione'] ?? '';

switch ($azione) {

    case 'nuova':
        require __DIR__ . '/nuova.php';
        break;

    case 'edit':
        require __DIR__ . '/edit.php';
        break;

    case 'riscatta':
        require __DIR__ . '/riscatta.php';
        break;

    default:
        require __DIR__ . '/lista.php';
}
