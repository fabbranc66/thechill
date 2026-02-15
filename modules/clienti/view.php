<?php
declare(strict_types=1);

/* esegue sempre le azioni POST */
require __DIR__ . '/actions.php';

$azione = $_GET['azione'] ?? '';

/* se NON è pagina cliente, serve ruolo admin */
if ($azione !== 'cliente') {
    richiedi_ruolo('amministratore');
}

switch ($azione) {

    case 'cliente':
        require __DIR__ . '/cliente.php';
        break;

    case 'edit':
        require __DIR__ . '/edit.php';
        break;

    default:
        require __DIR__ . '/lista.php';
}
