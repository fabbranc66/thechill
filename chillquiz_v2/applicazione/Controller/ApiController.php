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