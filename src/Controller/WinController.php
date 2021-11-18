<?php

namespace App\Controller;

class WinController extends AbstractController
{
    public function index(): string
    {
        session_start();
        $time = GameController::getFinishInterval();
        return $this->twig->render('Game/win-condition.html.twig', ['time' => $time,]);
    }
}
