<?php
include "../config.php";

if (!isset($_SESSION["admin_id"])) {
    die("Non autorizzato");
}

$partita_id = $_GET["partita_id"];
$azione = $_GET["azione"];

if ($azione == "start") {
    $conn->query("
    UPDATE partite
    SET stato='domanda'
    WHERE id=$partita_id
    ");
}

if ($azione == "next") {
    $conn->query("
    UPDATE partite
    SET domanda_corrente = domanda_corrente + 1,
        stato='domanda'
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

header("Location: controllo.php?partita_id=" . $partita_id);
exit;
