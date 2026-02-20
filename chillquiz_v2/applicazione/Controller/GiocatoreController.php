<?php

namespace Applicazione\Controller;

class GiocatoreController
{
    public function index()
    {
        require __DIR__ . '/../Vista/giocatore.php';
    }

    public function game()
    {
        require __DIR__ . '/../Vista/giocatore_game.php';
    }
}