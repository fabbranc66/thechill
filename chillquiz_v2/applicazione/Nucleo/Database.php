<?php

namespace Applicazione\Nucleo;

use mysqli;

/*
|--------------------------------------------------------------------------
| GESTIONE DATABASE (Singleton)
|--------------------------------------------------------------------------
| Responsabilità:
| - Creazione connessione MySQLi
| - Riutilizzo connessione unica
| - Configurazione charset
|
| Pattern utilizzato:
| Singleton
|--------------------------------------------------------------------------
*/

class Database
{
    /*
    |--------------------------------------------------------------------------
    | BLOCCO 1 — Proprietà statiche e connessione
    |--------------------------------------------------------------------------
    | $istanza     → mantiene unica istanza della classe
    | $connessione → oggetto mysqli attivo
    |--------------------------------------------------------------------------
    */
    private static $istanza = null;
    private $connessione;


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 2 — Costruttore privato
    |--------------------------------------------------------------------------
    | Crea la connessione al database.
    | È privato per impedire istanziazione diretta.
    |--------------------------------------------------------------------------
    */
    private function __construct()
    {
        // Recupero configurazione DB
        $config = Configurazione::database();

        // Creazione connessione MySQLi
        $this->connessione = new mysqli(
            $config['host'],
            $config['utente'],
            $config['password'],
            $config['database']
        );

        // Gestione errore connessione
        if ($this->connessione->connect_error) {
            die("Errore connessione database");
        }

        // Impostazione charset UTF-8
        $this->connessione->set_charset("utf8mb4");
    }


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 3 — Accesso pubblico alla connessione
    |--------------------------------------------------------------------------
    | Metodo: connessione()
    |
    | Logica:
    | - Se istanza non esiste → la crea
    | - Restituisce sempre la stessa connessione
    |--------------------------------------------------------------------------
    */
    public static function connessione()
    {
        if (self::$istanza === null) {
            self::$istanza = new self();
        }

        return self::$istanza->connessione;
    }
}