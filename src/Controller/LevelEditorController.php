<?php

namespace App\Controller;

use App\Model\LevelManager;

class LevelEditorController extends AbstractController
{
    public function edit(int $id)
    {
        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById($id);
        $grid = LevelManager::parseContent($level['content']);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->parsePost($level, $grid, $errors);
            if (!$errors) {
                $levelManager = new LevelManager();
                $levelManager->update($level, $grid);
            }
        }
        return $this->twig->render('Editor/edit.html.twig', ['level' => $level, 'grid' => $grid]);
    }

    private function parsePost(array &$level, array &$cells, array &$errors): void
    {
        if (empty($_POST['name']) || strlen($_POST['name']) > 30) {
            $errors[] = 'Le nom est invalide';
        }
        if (!$errors) {
            $name = $_POST['name'];
            $description = $_POST['description'] ?? '';
            $level['name'] = $name;
            $level['description'] = $description;
            array_map('trim', $level);
        }

        foreach ($_POST as $entry => $value) {
            $capture = [];
            if (preg_match("/^cell-(?<x>[0-9]+)-(?<y>[0-9]+)$/", $entry, $capture)) {
                if (!in_array($value, [LevelManager::CELL_FLOOR, LevelManager::CELL_WALL])) {
                    $errors[] = 'Type de cellule invalide';
                    continue;
                }
                $posX = intval($capture['x']);
                $posY = intval($capture['y']);
                if (isset($cells[$posY][$posX])) {
                    $cells[$posY][$posX] = $value;
                } else {
                    $errors[] = 'Position invalide';
                }
            }
        }
    }

    public function createLevel(): void
    {
        $levelManager = new LevelManager();
        $level = ['name' => 'Nouveau niveau', 'description' => ''];
        $cells = array_fill(0, 15, array_fill(0, 15, "floor"));
        $levelManager->create($level, $cells);
    }
}
