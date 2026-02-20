<?php

namespace Applicazione\Modello;

use Applicazione\Nucleo\Database;
use Applicazione\Modello\ConfigurazioneSistema;

/*
|--------------------------------------------------------------------------
| MODELLO PARTITA
|--------------------------------------------------------------------------
| Responsabilità:
| - Caricamento dati partita
| - Gestione stato
| - Recupero contenuti (domanda / giocatori / classifica)
| - Gestione timer server-side
|--------------------------------------------------------------------------
*/

class Partita
{
    /*
    |--------------------------------------------------------------------------
    | BLOCCO 1 — Proprietà interne
    |--------------------------------------------------------------------------
    | $conn  → connessione MySQLi
    | $id    → identificativo partita
    | $dati  → record completo caricato dal database
    |--------------------------------------------------------------------------
    */
    private $conn;
    private $id;
    private $dati;


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 2 — Costruttore / Caricamento partita
    |--------------------------------------------------------------------------
    */
    public function __construct($id)
    {
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

/*
|--------------------------------------------------------------------------
| BLOCCO — Recupera partita attiva
|--------------------------------------------------------------------------
| Ritorna oggetto Partita con stato attesa/domanda/risultati
|--------------------------------------------------------------------------
*/
public static function attiva()
{
    $conn = \Applicazione\Nucleo\Database::connessione();

    $res = $conn->query("
        SELECT id
        FROM partite
        WHERE stato IN ('attesa','domanda','risultati')
        ORDER BY id DESC
        LIMIT 1
    ");

    $row = $res->fetch_assoc();

    if (!$row) {
        return null;
    }

    return new self($row['id']);
}
    
/*
|--------------------------------------------------------------------------
| BLOCCO — Getter ID partita
|--------------------------------------------------------------------------
*/
public function id()
{
    return $this->id;
}

/*
|--------------------------------------------------------------------------
| BLOCCO — Getter PIN partita
|--------------------------------------------------------------------------
*/
public function pin()
{
    return $this->dati['pin'] ?? null;
}
/*
    |--------------------------------------------------------------------------
    | BLOCCO 3 — Registrazione risposta giocatore
    |--------------------------------------------------------------------------
    | - Verifica doppia risposta
    | - Verifica correttezza opzione
    | - Inserisce risposta
    | - Aggiorna punteggio
    |--------------------------------------------------------------------------
    */
    public function registraRisposta($giocatore_id, $opzione_id)
    {
        $domanda = $this->domandaCompleta();

        if (!$domanda) {
            return ['errore' => 'Domanda non valida'];
        }

        $domanda_id = $domanda['id'];

        // Verifica risposta già data
        $stmt = $this->conn->prepare("
            SELECT id FROM risposte
            WHERE partita_id = ?
            AND giocatore_id = ?
            AND domanda_id = ?
            LIMIT 1
        ");

        $stmt->bind_param("iii", $this->id, $giocatore_id, $domanda_id);
        $stmt->execute();
        $gia = $stmt->get_result()->fetch_assoc();

        if ($gia) {
            return ['errore' => 'Risposta già inviata'];
        }

        // Verifica opzione corretta
        $stmt = $this->conn->prepare("
            SELECT corretta FROM opzioni
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $opzione_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if (!$res) {
            return ['errore' => 'Opzione non valida'];
        }

/*
|--------------------------------------------------------------------------
| BLOCCO — Calcolo punteggio avanzato
|--------------------------------------------------------------------------
| Formula:
| base × difficolta
| + bonus tempo (se attivo)
| + bonus primo (se primo)
|--------------------------------------------------------------------------
*/

$corretta = (int)$res['corretta'];

/* --- Recupero difficoltà domanda --- */
$stmt = $this->conn->prepare("
    SELECT difficolta FROM domande
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $domanda_id);
$stmt->execute();
$d = $stmt->get_result()->fetch_assoc();

$difficolta = isset($d['difficolta']) ? (float)$d['difficolta'] : 1.0;

/* --- Recupero configurazioni --- */
$base = (int) ConfigurazioneSistema::get('punteggio_base');
$bonus_primo_config = (int) ConfigurazioneSistema::get('bonus_primo');
$bonus_tempo_attivo = (int) ConfigurazioneSistema::get('bonus_tempo_attivo');
$durata = (int) ConfigurazioneSistema::get('durata_domanda_default');

$tempo = $this->tempoRimanente();
$percentuale_tempo = ($durata > 0) ? ($tempo / $durata) : 0;

/* --- Calcolo base domanda --- */
$punteggio_domanda = $base * $difficolta;

/* --- Bonus tempo --- */
$punteggio_tempo = 0;
if ($bonus_tempo_attivo === 1) {
    $punteggio_tempo = $punteggio_domanda * $percentuale_tempo;
}

/* --- Verifica primo che risponde --- */
$stmt = $this->conn->prepare("
    SELECT COUNT(*) as totale
    FROM risposte
    WHERE partita_id = ?
    AND domanda_id = ?
");

$stmt->bind_param("ii", $this->id, $domanda_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc();

$bonus_primo = ((int)$count['totale'] === 0)
    ? $bonus_primo_config
    : 0;

/* --- Calcolo finale --- */
$punti = $corretta
    ? round($punteggio_domanda + $punteggio_tempo + $bonus_primo)
    : 0;
        // Inserimento risposta
        $stmt = $this->conn->prepare("
            INSERT INTO risposte
            (partita_id, giocatore_id, domanda_id, corretta, punti, data_risposta)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            "iiiii",
            $this->id,
            $giocatore_id,
            $domanda_id,
            $corretta,
            $punti
        );

        $stmt->execute();

        // Aggiorna punteggio giocatore
        if ($punti > 0) {
            $stmt = $this->conn->prepare("
                UPDATE giocatori
                SET punteggio = punteggio + ?
                WHERE id = ?
                AND partita_id = ?
            ");

            $stmt->bind_param("iii", $punti, $giocatore_id, $this->id);
            $stmt->execute();
        }

        return [
            'corretta' => (bool)$corretta,
            'punti' => $punti
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 4 — Verifica esistenza partita
    |--------------------------------------------------------------------------
    */
    public function esiste()
    {
        return !empty($this->dati);
    }


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 5 — Lettura stato partita
    |--------------------------------------------------------------------------
    */
    public function stato()
    {
        return $this->dati['stato'] ?? null;
    }


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 6 — Recupero domanda completa
    |--------------------------------------------------------------------------
    */
    public function domandaCompleta()
    {
        if (!$this->dati) {
            return null;
        }

        $quiz_id = (int)$this->dati['quiz_id'];
        $numero  = (int)$this->dati['domanda_corrente'];
        $offset  = $numero - 1;

        // Recupero domanda
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

        // Recupero opzioni
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


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 7 — Elenco giocatori
    |--------------------------------------------------------------------------
    */
    public function giocatori()
    {
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


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 8 — Classifica finale
    |--------------------------------------------------------------------------
    */
    public function classifica()
    {
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


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 9 — Avvio domanda
    |--------------------------------------------------------------------------
    */
    public function avviaDomanda(): void
    {
        $inizio = time();

        $stmt = $this->conn->prepare("
            UPDATE partite
            SET stato = 'domanda',
                inizio_domanda = ?
            WHERE id = ?
        ");

        $stmt->bind_param("ii", $inizio, $this->id);
        $stmt->execute();

        $this->dati['stato'] = 'domanda';
        $this->dati['inizio_domanda'] = $inizio;
    }


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 10 — Calcolo tempo rimanente
    |--------------------------------------------------------------------------
    */
    public function tempoRimanente(): int
    {
        if ($this->stato() !== 'domanda') {
            return 0;
        }

        if (empty($this->dati['inizio_domanda'])) {
            return 0;
        }

        $inizio = (int)$this->dati['inizio_domanda'];
        $durata = (int) ConfigurazioneSistema::get('durata_domanda_default');

        $fine = $inizio + $durata;
        $resto = $fine - time();

        return max(0, $resto);
    }


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 11 — Verifica scadenza automatica
    |--------------------------------------------------------------------------
    */
    public function verificaScadenza(): void
    {
        if ($this->stato() !== 'domanda') {
            return;
        }

        if ($this->tempoRimanente() > 0) {
            return;
        }

        $stmt = $this->conn->prepare("
            UPDATE partite
            SET stato = 'risultati'
            WHERE id = ?
        ");

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $this->dati['stato'] = 'risultati';
    }
}