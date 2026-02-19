<?php

namespace Applicazione\Nucleo;

class Configurazione {

    public static function database() {

        $server = $_SERVER['SERVER_ADDR'] ?? '';

        // === LOCALE ===
        if (
            $server === '127.0.0.1' ||
            $server === '::1' ||
            $server === '192.168.1.20'
        ) {
            return [
                'host' => 'localhost',
                'utente' => 'root',
                'password' => '',
                'database' => 'sql1874742_3'
            ];
        }

        // === ARUBA ===
        return [
            'host' => 'localhost',
            'utente' => 'sql1874742',
            'password' => 'METTI_LA_PASSWORD_REALE',
            'database' => 'sql1874742_3'
        ];
    }
}
