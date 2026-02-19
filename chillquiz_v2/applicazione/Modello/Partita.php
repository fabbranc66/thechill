<?php

namespace Applicazione\Modello;

use Applicazione\Nucleo\Database;

class Partita {

    private $conn;
    private $id;
    private $dati;

public function domandaCompleta() {

    if (!$this->dati) {
        return null;
    }

    $quiz_id = (int)$this->dati['quiz_id'];
    $numero = (int)$this->dati['domanda_corrente'];

    $offset = $numero - 1;

    // Recupera domanda
    $stmt = $this->conn->prepare("
        SELECT id, testo
        FROM domande
        WHERE quiz_id = ?
        ORDER BY ordine ASC
        LIMIT 1 OFFSET ?
    ");

    $stmt->bind_param("ii", $quiz_id, $offset);
    $stmt->execute();

    $domanda = $stmt->get_result()->fetch_assoc();

    if (!$domanda) {
        return null;
    }

    // Recupera opzioni
    $stmt = $this->conn->prepare("
        SELECT id, testo
        FROM opzioni
        WHERE domanda_id = ?
        ORDER BY id ASC
    ");

    $stmt->bind_param("i", $domanda['id']);
    $stmt->execute();

    $opzioni = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $domanda['opzioni'] = $opzioni;

    return $domanda;
}

    public function __construct($id) {

        $this->conn = Database::connessione();
        $this->id = (int)$id;

        $stmt = $this->conn->prepare("
            SELECT * FROM partite
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $this->dati = $stmt->get_result()->fetch_assoc();
    }

public function classifica() {

    $stmt = $this->conn->prepare("
        SELECT nome, punteggio
        FROM giocatori
        WHERE partita_id = ?
        ORDER BY punteggio DESC
    ");

    $stmt->bind_param("i", $this->id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


public function verificaScadenza() {

    if ($this->stato() !== 'domanda') {
        return;
    }

    if ($this->tempoRimanente() > 0) {
        return;
    }

    // Tempo scaduto → passa a risultati
    $stmt = $this->conn->prepare("
        UPDATE partite
        SET stato = 'risultati'
        WHERE id = ?
    ");

    $stmt->bind_param("i", $this->id);
    $stmt->execute();

    $this->dati['stato'] = 'risultati';
}
    
    public function esiste() {
        return !empty($this->dati);
    }

    public function stato() {
        return $this->dati['stato'] ?? null;
    }

    public function tempoRimanente() {
        return 0;
    }

    public function giocatori() {

        $stmt = $this->conn->prepare("
            SELECT id, nome, punteggio
            FROM giocatori
            WHERE partita_id = ?
            ORDER BY entrato_il ASC
        ");

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
