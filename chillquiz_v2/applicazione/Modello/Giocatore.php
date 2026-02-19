<?php

namespace Applicazione\Modello;

use Applicazione\Nucleo\Database;

/*
|--------------------------------------------------------------------------
| MODELLO CONFIGURAZIONE SISTEMA
|--------------------------------------------------------------------------
| Responsabilità:
| - Lettura configurazioni globali
| - Aggiornamento configurazioni
| - Recupero elenco completo configurazioni
|
| Tabella: configurazioni
| Campi: chiave, valore, descrizione
|--------------------------------------------------------------------------
*/

class ConfigurazioneSistema
{
    /*
    |--------------------------------------------------------------------------
    | BLOCCO 1 — Recupero singola configurazione
    |--------------------------------------------------------------------------
    | Metodo: get($chiave)
    |
    | Descrizione:
    | Recupera il valore associato a una chiave.
    | Se la chiave non esiste → ritorna null.
    |--------------------------------------------------------------------------
    */
    public static function get($chiave)
    {
        $conn = Database::connessione();

        $stmt = $conn->prepare("
            SELECT valore
            FROM configurazioni
            WHERE chiave = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $chiave);
        $stmt->execute();

        $res = $stmt->get_result()->fetch_assoc();

        return $res ? $res['valore'] : null;
    }


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 2 — Aggiornamento configurazione
    |--------------------------------------------------------------------------
    | Metodo: set($chiave, $valore)
    |
    | Descrizione:
    | Aggiorna il valore di una chiave già esistente.
    | Ritorna true/false in base all’esito dell’execute().
    |--------------------------------------------------------------------------
    */
    public static function set($chiave, $valore)
    {
        $conn = Database::connessione();

        $stmt = $conn->prepare("
            UPDATE configurazioni
            SET valore = ?
            WHERE chiave = ?
        ");

        $stmt->bind_param("ss", $valore, $chiave);

        return $stmt->execute();
    }


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 3 — Recupero tutte le configurazioni
    |--------------------------------------------------------------------------
    | Metodo: tutte()
    |
    | Descrizione:
    | Restituisce l’elenco completo delle configurazioni
    | con struttura:
    | [
    |   { chiave, valore, descrizione },
    |   ...
    | ]
    |--------------------------------------------------------------------------
    */
    public static function tutte()
    {
        $conn = Database::connessione();

        $res = $conn->query("
            SELECT chiave, valore, descrizione
            FROM configurazioni
            ORDER BY chiave ASC
        ");

        return $res->fetch_all(MYSQLI_ASSOC);
    }
}