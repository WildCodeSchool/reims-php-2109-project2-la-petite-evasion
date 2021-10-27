<?php

namespace App\Controller;

use App\Model\LevelManager;

class GameController extends AbstractController
{
    /**
     * Show first level
     */
    public function index(): string
    {
        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById(1);

        return $this->twig->render('Game/index.html.twig', ['game' => $level]);
    }
}
