<?php
include "config.php";

/* HEADER NO CACHE */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["giocatore_id"]) || !isset($_SESSION["partita_id"])) {
    die("Sessione mancante");
}

$risposta_id = $_POST["risposta"] ?? 0;
$giocatore_id = (int)$_SESSION["giocatore_id"];
$partita_id = (int)$_SESSION["partita_id"];

if (!$risposta_id) {
    die("Risposta non ricevuta");
}

/* recupera risposta e domanda */
$res = $conn->query("
SELECT r.corretta, r.domanda_id
FROM risposte r
WHERE r.id=$risposta_id
");

if (!$res || $res->num_rows == 0) {
    die("Risposta non trovata");
}

$row = $res->fetch_assoc();
$corretta = (int)$row["corretta"];
$domanda_id = (int)$row["domanda_id"];

/* recupera tempo partita */
$partita = $conn->query("
SELECT tempo_fine
FROM partite
WHERE id=$partita_id
")->fetch_assoc();

$punti = 0;

if ($corretta == 1) {

    $adesso = time();
    $tempo_fine = (int)$partita["tempo_fine"];

    $tempo_restante = $tempo_fine - $adesso;
    if ($tempo_restante < 0) $tempo_restante = 0;

    $tempo_totale = 15;

    $percentuale = $tempo_restante / $tempo_totale;
    if ($percentuale < 0) $percentuale = 0;
    if ($percentuale > 1) $percentuale = 1;

    $punti_base = 100;
    $bonus = intval(100 * $percentuale);

    $punti = $punti_base + $bonus;

    /* aggiorna punteggio giocatore */
    $ok = $conn->query("
    UPDATE giocatori
    SET punteggio = punteggio + $punti
    WHERE id=$giocatore_id
    ");

    if (!$ok) {
        die("Errore update punteggio: " . $conn->error);
    }
}

/* salva risposta giocatore */
$ok = $conn->query("
INSERT INTO risposte_giocatori
(giocatore_id, domanda_id, risposta_id, punti)
VALUES ($giocatore_id, $domanda_id, $risposta_id, $punti)
");

if (!$ok) {
    die("Errore insert risposta: " . $conn->error);
}

/* salva ultimo punteggio */
$_SESSION["ultimo_punteggio"] = $punti;

header("Location: gioco.php");
exit;
