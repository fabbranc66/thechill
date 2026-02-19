<?php
include "../config.php";

/* HEADER NO CACHE */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


if (!isset($_SESSION["admin_id"])) {
    die("Non autorizzato");
}

$partita_id = $_GET["partita_id"] ?? 0;
$azione = $_GET["azione"] ?? "";

if (!$partita_id) {
    die("Partita non valida");
}

// durata domanda in secondi
$tempo_domanda = 15;

if ($azione == "start" || $azione == "next") {

    $fine = time() + $tempo_domanda;

    if ($azione == "next") {
        $conn->query("
        UPDATE partite
        SET domanda_corrente = domanda_corrente + 1
        WHERE id=$partita_id
        ");
    }

    $conn->query("
    UPDATE partite
    SET stato='domanda',
        tempo_fine=$fine
    WHERE id=$partita_id
    ");
}

if ($azione == "classifica") {
    $conn->query("
    UPDATE partite
    SET stato='classifica'
    WHERE id=$partita_id
    ");
}

if ($azione == "end") {
    $conn->query("
    UPDATE partite
    SET stato='finita'
    WHERE id=$partita_id
    ");
}

header("Location: partita.php?partita_id=" . $partita_id);
exit;
