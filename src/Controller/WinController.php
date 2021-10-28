<?php

namespace App\Controller;

class WinController extends AbstractController
{    
    public function index(): string
    {
        return $this->twig->render('Game/win-condition.html.twig');
    }
}
