<?php

namespace Applicazione\Modello;

use Applicazione\Nucleo\Database;

class ConfigurazioneSistema {

    public static function get($chiave) {

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

    public static function set($chiave, $valore) {

        $conn = Database::connessione();

        $stmt = $conn->prepare("
            UPDATE configurazioni
            SET valore = ?
            WHERE chiave = ?
        ");

        $stmt->bind_param("ss", $valore, $chiave);
        return $stmt->execute();
    }

    public static function tutte() {

        $conn = Database::connessione();

        $res = $conn->query("
            SELECT chiave, valore, descrizione
            FROM configurazioni
            ORDER BY chiave ASC
        ");

        return $res->fetch_all(MYSQLI_ASSOC);
    }
}
