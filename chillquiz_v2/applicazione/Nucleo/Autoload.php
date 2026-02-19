<?php

spl_autoload_register(function ($classe) {

    $base = dirname(__DIR__, 2); 
    // sale fino a chillquiz_v2

    $classe = str_replace("Applicazione\\", "applicazione\\", $classe);

    $percorso = $base . DIRECTORY_SEPARATOR .
        str_replace("\\", DIRECTORY_SEPARATOR, $classe) . ".php";

    if (file_exists($percorso)) {
        require $percorso;
    }
});
