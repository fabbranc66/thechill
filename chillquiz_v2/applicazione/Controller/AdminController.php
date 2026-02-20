<?php

namespace Applicazione\Controller;

use Applicazione\Modello\ConfigurazioneSistema;

/*
|--------------------------------------------------------------------------
| CONTROLLER ADMIN
|--------------------------------------------------------------------------
| - Gestione configurazioni sistema
| - Regia controllo partita
|--------------------------------------------------------------------------
*/

class AdminController
{
    /*
    |--------------------------------------------------------------------------
    | BLOCCO 1 — Configurazioni sistema
    |--------------------------------------------------------------------------
    */
    public function configurazioni()
    {
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

    /*
    |--------------------------------------------------------------------------
    | BLOCCO 2 — Regia partita (Game Control)
    |--------------------------------------------------------------------------
    */
    public function game()
    {
        require __DIR__ . "/../Vista/admin_game.php";
    }
}