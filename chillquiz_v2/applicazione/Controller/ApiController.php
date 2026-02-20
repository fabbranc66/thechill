<?php

namespace Applicazione\Controller;

use Applicazione\Modello\Partita;

/*
|--------------------------------------------------------------------------
| API CONTROLLER
|--------------------------------------------------------------------------
| Responsabilità:
| - Gestione endpoint API
| - Costruzione risposta JSON
| - Coordinamento tra modello Partita e output
|--------------------------------------------------------------------------
|
| Endpoint attuale:
|   api/stato
|--------------------------------------------------------------------------
*/

class ApiController
{
    /*
    |--------------------------------------------------------------------------
    | BLOCCO 1 — Dispatcher API
    |--------------------------------------------------------------------------
    | Metodo: gestisci($segmenti)
    |
    | - Riceve segmenti dal Router
    | - Determina l'azione richiesta
    | - Instrada verso metodo corretto
    |--------------------------------------------------------------------------
    */
public function gestisci($segmenti) {

    $azione = $segmenti[1] ?? '';

    switch ($azione) {

        case 'stato':
            $this->stato();
            break;

        case 'rispondi':
            $this->rispondi();
            break;

        case 'join':
            $this->join();
            break;

        case 'admin_control':
            $this->adminControl();
            break;

            default:
            header('Content-Type: application/json');
            echo json_encode(['errore' => 'Endpoint non valido']);
            exit;
    }
}
/*
|--------------------------------------------------------------------------
| BLOCCO — Endpoint API rispondi
|--------------------------------------------------------------------------
| Endpoint: api/rispondi
| Metodo HTTP: POST
|
| Responsabilità:
| - Validare parametri in ingresso
| - Verificare esistenza partita
| - Verificare stato attivo della domanda
| - Verificare tempo non scaduto
| - Delegare al modello Partita la registrazione risposta
| - Restituire JSON esito
|
| Parametri POST attesi:
|   partita   → ID partita
|   giocatore → ID giocatore
|   opzione   → ID opzione scelta
|
| Output JSON:
|   Successo:
|     { corretta: bool, punti: int }
|
|   Errore:
|     { errore: "messaggio" }
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| BLOCCO — Controllo Regia Admin
|--------------------------------------------------------------------------
*/
private function adminControl()
{
    header('Content-Type: application/json');

    $azione = $_POST['azione'] ?? null;
    $partita_id = $_POST['partita'] ?? null;

    if (!$azione || !$partita_id) {
        echo json_encode(['errore' => 'Dati mancanti']);
        exit;
    }

    $partita = new \Applicazione\Modello\Partita($partita_id);

    if (!$partita->esiste()) {
        echo json_encode(['errore' => 'Partita non trovata']);
        exit;
    }

    $conn = \Applicazione\Nucleo\Database::connessione();

    switch ($azione) {

        case 'avvia_domanda':
            $partita->avviaDomanda();
            break;

        case 'mostra_risultati':
            $conn->query("
                UPDATE partite
                SET stato = 'risultati'
                WHERE id = " . (int)$partita_id
            );
            break;

        case 'prossima_domanda':
            $conn->query("
                UPDATE partite
                SET domanda_corrente = domanda_corrente + 1,
                    stato = 'attesa'
                WHERE id = " . (int)$partita_id
            );
            break;

case 'reset':

    // Reset stato partita
    $conn->query("
        UPDATE partite
        SET stato = 'attesa',
            domanda_corrente = 1,
            inizio_domanda = NULL
        WHERE id = " . (int)$partita_id
    );

    // Cancella tutte le risposte
    $conn->query("
        DELETE FROM risposte
        WHERE partita_id = " . (int)$partita_id
    );

    // Cancella tutti i giocatori
    $conn->query("
        DELETE FROM giocatori
        WHERE partita_id = " . (int)$partita_id
    );

    break;
            $conn->query("
                DELETE FROM risposte WHERE partita_id = " . (int)$partita_id
            );

            $conn->query("
                UPDATE giocatori
                SET punteggio = 0
                WHERE partita_id = " . (int)$partita_id
            );
            break;
    }

    echo json_encode(['successo' => true]);
    exit;
}
private function rispondi()
{
    header('Content-Type: application/json');

    /*
    |--------------------------------------------------------------------------
    | 1 — Validazione parametri input
    |--------------------------------------------------------------------------
    */
    $partita_id   = $_POST['partita'] ?? null;
    $giocatore_id = $_POST['giocatore'] ?? null;
    $opzione_id   = $_POST['opzione'] ?? null;

    if (!$partita_id || !$giocatore_id || !$opzione_id) {
        echo json_encode(['errore' => 'Parametri mancanti']);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | 2 — Caricamento partita
    |--------------------------------------------------------------------------
    */
    $partita = new Partita($partita_id);

    if (!$partita->esiste()) {
        echo json_encode(['errore' => 'Partita non trovata']);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | 3 — Verifica stato domanda
    |--------------------------------------------------------------------------
    */
    if ($partita->stato() !== 'domanda') {
        echo json_encode(['errore' => 'Domanda non attiva']);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | 4 — Verifica tempo non scaduto
    |--------------------------------------------------------------------------
    */
    if ($partita->tempoRimanente() <= 0) {
        echo json_encode(['errore' => 'Tempo scaduto']);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | 5 — Delegazione al modello Partita
    |--------------------------------------------------------------------------
    */
    $esito = $partita->registraRisposta($giocatore_id, $opzione_id);

    /*
    |--------------------------------------------------------------------------
    | 6 — Output JSON finale
    |--------------------------------------------------------------------------
    */
    echo json_encode($esito);
    exit;
}    

/*
|--------------------------------------------------------------------------
| BLOCCO — Join partita via PIN
|--------------------------------------------------------------------------
*/
private function join()
{
    header('Content-Type: application/json');

    $alias = trim($_POST['alias'] ?? '');
    $pin   = trim($_POST['pin'] ?? '');

    if (!$alias || !$pin) {
        echo json_encode(['errore' => 'Dati mancanti']);
        exit;
    }

    $conn = \Applicazione\Nucleo\Database::connessione();

    // Trova partita tramite PIN
    $stmt = $conn->prepare("
        SELECT id FROM partite
        WHERE pin = ?
        AND stato = 'attesa'
        LIMIT 1
    ");

    $stmt->bind_param("s", $pin);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (!$res) {
        echo json_encode(['errore' => 'Partita non trovata o già avviata']);
        exit;
    }

    $partita_id = $res['id'];

    // Inserisce giocatore
    $stmt = $conn->prepare("
        INSERT INTO giocatori (nome, partita_id, punteggio, entrato_il)
        VALUES (?, ?, 0, NOW())
    ");

    $stmt->bind_param("si", $alias, $partita_id);
    $stmt->execute();

    $giocatore_id = $stmt->insert_id;

    echo json_encode([
        'successo' => true,
        'giocatore_id' => $giocatore_id,
        'partita_id' => $partita_id
    ]);

    exit;
}
/*
    |--------------------------------------------------------------------------
    | BLOCCO 2 — Endpoint stato partita
    |--------------------------------------------------------------------------
    | Metodo: stato()
    |
    | Responsabilità:
    | - Validare input
    | - Caricare partita
    | - Verificare scadenza timer
    | - Costruire JSON in base allo stato
    |--------------------------------------------------------------------------
    */
    private function stato()
    {
        header('Content-Type: application/json');

        /*
        |--------------------------------------------------------------------------
        | 2A — Validazione parametro partita
        |--------------------------------------------------------------------------
        */
        $partita_id = $_GET['partita'] ?? null;

        if (!$partita_id) {
            echo json_encode(['errore' => 'Partita mancante']);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | 2B — Caricamento modello Partita
        |--------------------------------------------------------------------------
        */
        $partita = new Partita($partita_id);

        if (!$partita->esiste()) {
            echo json_encode(['errore' => 'Partita non trovata']);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | 2C — Verifica scadenza timer
        |--------------------------------------------------------------------------
        | Se il tempo è scaduto → aggiorna stato a "risultati"
        |--------------------------------------------------------------------------
        */
        $partita->verificaScadenza();

        /*
        |--------------------------------------------------------------------------
        | 2D — Costruzione struttura base risposta
        |--------------------------------------------------------------------------
        */
        $stato = $partita->stato();

        $risposta = [
            'stato' => $stato,
            'tempo' => $partita->tempoRimanente()
        ];

        /*
        |--------------------------------------------------------------------------
        | 2E — Arricchimento risposta in base allo stato
        |--------------------------------------------------------------------------
        */

        // Stato "attesa" → elenco giocatori
        if ($stato === 'attesa') {
            $risposta['giocatori'] = $partita->giocatori();
        }

        // Stato "domanda" → domanda corrente + opzioni
        if ($stato === 'domanda') {
            $risposta['domanda'] = $partita->domandaCompleta();
        }

        // Stato "risultati" → classifica
        if ($stato === 'risultati') {
            $risposta['classifica'] = $partita->classifica();
        }

        /*
        |--------------------------------------------------------------------------
        | 2F — Output finale JSON
        |--------------------------------------------------------------------------
        */
        echo json_encode($risposta);
        exit;
    }
}