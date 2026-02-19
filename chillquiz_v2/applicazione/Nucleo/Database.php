<?php

namespace Applicazione\Nucleo;

use mysqli;

class Database {

    private static $istanza = null;
    private $connessione;

    private function __construct() {

        $config = Configurazione::database();

        $this->connessione = new mysqli(
            $config['host'],
            $config['utente'],
            $config['password'],
            $config['database']
        );

        if ($this->connessione->connect_error) {
            die("Errore connessione database");
        }

        $this->connessione->set_charset("utf8mb4");
    }

    public static function connessione() {

        if (self::$istanza === null) {
            self::$istanza = new self();
        }

        return self::$istanza->connessione;
    }
}
