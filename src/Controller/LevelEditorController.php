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
            // clean $_POST data
            $cells = [];

            if (empty($_POST['name']) || $_POST['name'] > 30) {
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
                $matched = preg_match("/^cell-(?<x>[0-9]+)-(?<y>[0-9]+)$/", $entry, $capture);
                if ($matched === 1) {
                    if (array_key_exists('x', $capture) && array_key_exists('y', $capture)) {
                        $posX = intval($capture['x']);
                        $posY = intval($capture['y']);
                        if (!array_key_exists($posY, $cells)) {
                            $cells[$posY] = [];
                        }
                        $cells[$posY][$posX] = $value;
                    }
                }
            }
            foreach ($cells as $row) {
                ksort($row);
                if (count($row) - 1 === array_key_last($row)) {
                    $errors[] = 'Cellules invalides';
                }
            }
            ksort($cells);
            if (count($cells) - 1 === array_key_last($cells)) {
                $errors[] = 'Lignes invalides';
            }
            if (!$errors) {
                $levelManager = new LevelManager();
                $levelManager->update($level, $cells);
            }
        }
        return $this->twig->render('Editor/edit.html.twig', ['level' => $level, 'grid' => $grid]);
    }
}
