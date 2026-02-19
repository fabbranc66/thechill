<?php

namespace Applicazione\Nucleo;

class Router {

    public function avvia() {

        $url = $_GET['url'] ?? '';
        $segmenti = explode('/', trim($url, '/'));

        $prima = $segmenti[0] ?? '';

        switch ($prima) {

            case 'giocatore':
                $controller = new \Applicazione\Controller\GiocatoreController();
                $controller->index();
                break;

            case 'schermo':
                $controller = new \Applicazione\Controller\SchermoController();
                $controller->index();
                break;

            case 'api':
                $controller = new \Applicazione\Controller\ApiController();
                $controller->gestisci($segmenti);
                break;

            case 'admin':
                $controller = new \Applicazione\Controller\AdminController();
                
                if (($segmenti[1] ?? '') === 'configurazioni') {
                    $controller->configurazioni();
                }
                break;

                default:
                $this->home();
        }
    }

    private function home() {
        echo "ChillQuiz v2 attivo";
    }
}
