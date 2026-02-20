<?php

namespace Applicazione\Controller;

use Applicazione\Modello\Partita;

/*
|--------------------------------------------------------------------------
| CONTROLLER SCHERMO PUBBLICO
|--------------------------------------------------------------------------
*/

class SchermoController
{
    public function index()
    {
        $partita = Partita::attiva();

        if (!$partita) {
            die("Nessuna partita attiva");
        }

        $pin = $partita->pin();
        $partita_id = $partita->id();

        require __DIR__ . '/../Vista/schermo.php';
    }
}