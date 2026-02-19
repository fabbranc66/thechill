<?php

namespace Applicazione\Controller;

use Applicazione\Modello\ConfigurazioneSistema;

class AdminController {

    public function configurazioni() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            foreach ($_POST as $chiave => $valore) {
                ConfigurazioneSistema::set($chiave, $valore);
            }

            header("Location: ?url=admin/configurazioni");
            exit;
        }

        $config = ConfigurazioneSistema::tutte();

        require __DIR__ . "/../Vista/admin_configurazioni.php";
    }
}
