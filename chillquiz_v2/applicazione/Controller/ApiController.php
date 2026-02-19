<?php

namespace Applicazione\Controller;

use Applicazione\Modello\Partita;

class ApiController {

    public function gestisci($segmenti) {

        $azione = $segmenti[1] ?? '';

        switch ($azione) {

            case 'stato':
                $this->stato();
                break;

            default:
                header('Content-Type: application/json');
                echo json_encode(['errore' => 'Endpoint non valido']);
                exit;
        }
    }

    private function stato() {

        header('Content-Type: application/json');

        $partita_id = $_GET['partita'] ?? null;

        if (!$partita_id) {
            echo json_encode(['errore' => 'Partita mancante']);
            exit;
        }

        $partita = new Partita($partita_id);

        if (!$partita->esiste()) {
            echo json_encode(['errore' => 'Partita non trovata']);
            exit;
        }

        // 🔥 Controlla se il timer è scaduto
        $partita->verificaScadenza();

        $stato = $partita->stato();

        $risposta = [
            'stato' => $stato,
            'tempo' => $partita->tempoRimanente()
        ];

        // Stato attesa → elenco giocatori
        if ($stato === 'attesa') {
            $risposta['giocatori'] = $partita->giocatori();
        }

        // Stato domanda → domanda + opzioni
        if ($stato === 'domanda') {
            $risposta['domanda'] = $partita->domandaCompleta();
        }

        // Stato risultati → classifica
        if ($stato === 'risultati') {
            $risposta['classifica'] = $partita->classifica();
        }

        echo json_encode($risposta);
        exit;
    }
}
