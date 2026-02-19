<?php

namespace Applicazione\Nucleo;

/*
|--------------------------------------------------------------------------
| ROUTER PRINCIPALE
|--------------------------------------------------------------------------
| Responsabilità:
| - Interpretare il parametro GET "url"
| - Suddividere i segmenti
| - Instradare verso il controller corretto
|
| Formato URL atteso:
| index.php?url=segmento1/segmento2/...
|--------------------------------------------------------------------------
*/

class Router
{
    /*
    |--------------------------------------------------------------------------
    | BLOCCO 1 — Metodo principale di avvio
    |--------------------------------------------------------------------------
    | Metodo: avvia()
    |
    | Logica:
    | - Legge $_GET['url']
    | - Divide in segmenti
    | - Seleziona controller in base al primo segmento
    |--------------------------------------------------------------------------
    */
    public function avvia()
    {
        // Recupero URL (es: "api/stato")
        $url = $_GET['url'] ?? '';

        // Suddivisione in segmenti
        $segmenti = explode('/', trim($url, '/'));

        // Primo segmento (root routing)
        $prima = $segmenti[0] ?? '';

        /*
        |--------------------------------------------------------------------------
        | BLOCCO 2 — Switch di instradamento
        |--------------------------------------------------------------------------
        */
        switch ($prima) {

            /*
            |--------------------------------------------------------------------------
            | ROUTE: giocatore
            |--------------------------------------------------------------------------
            | Controller: GiocatoreController
            | Metodo chiamato: index()
            |--------------------------------------------------------------------------
            */
            case 'giocatore':
                $controller = new \Applicazione\Controller\GiocatoreController();
                $controller->index();
                break;


            /*
            |--------------------------------------------------------------------------
            | ROUTE: schermo
            |--------------------------------------------------------------------------
            | Controller: SchermoController
            | Metodo chiamato: index()
            |--------------------------------------------------------------------------
            */
            case 'schermo':
                $controller = new \Applicazione\Controller\SchermoController();
                $controller->index();
                break;


            /*
            |--------------------------------------------------------------------------
            | ROUTE: api
            |--------------------------------------------------------------------------
            | Controller: ApiController
            | Metodo chiamato: gestisci($segmenti)
            |
            | Esempio:
            | url=api/stato
            |--------------------------------------------------------------------------
            */
            case 'api':
                $controller = new \Applicazione\Controller\ApiController();
                $controller->gestisci($segmenti);
                break;


            /*
            |--------------------------------------------------------------------------
            | ROUTE: admin
            |--------------------------------------------------------------------------
            | Controller: AdminController
            | Sotto-rotta gestita: configurazioni
            |--------------------------------------------------------------------------
            */
            case 'admin':
                $controller = new \Applicazione\Controller\AdminController();

                if (($segmenti[1] ?? '') === 'configurazioni') {
                    $controller->configurazioni();
                }
                break;


            /*
            |--------------------------------------------------------------------------
            | DEFAULT ROUTE
            |--------------------------------------------------------------------------
            | Nessun segmento riconosciuto
            |--------------------------------------------------------------------------
            */
            default:
                $this->home();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BLOCCO 3 — Homepage fallback
    |--------------------------------------------------------------------------
    | Mostrata quando nessuna route viene riconosciuta.
    |--------------------------------------------------------------------------
    */
    private function home()
    {
        echo "ChillQuiz v2 attivo";
    }
}