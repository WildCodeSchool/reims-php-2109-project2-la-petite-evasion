<?php

namespace App\Controller;

use App\Model\LevelManager;
use App\Controller\GameController;

class LevelEditorController extends AbstractController
{
    public function edit(int $id) {
    $levelManager = new LevelManager();
    $level = $levelManager->selectOneById($id);
    $grid = LevelManager::parseContent($level['content']);
    return $this->twig->render('Editor/edit.html.twig', ['level' => $level, 'grid' => $grid]);
    }

}