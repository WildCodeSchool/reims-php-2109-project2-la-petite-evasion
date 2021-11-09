<?php

namespace App\Controller;

use App\Model\LevelManager;

class LevelEditorController extends AbstractController
{
    public function list()
    {
        $levelManager = new LevelManager();
        $levels = $levelManager->selectAll('id');
        return $this->twig->render('Editor/index.html.twig', ['levels' => $levels]);
    }

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
        $level['height'] = count($grid);
        $level['width'] = count($grid[0]);
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
        $this->parsePostDimensions($cells);

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
                }
            }
        }
    }

    private function parsePostDimensions(array &$cells): void
    {
        if (!empty($_POST["width"]) && !empty($_POST["height"])) {
            $width = filter_var($_POST['width'], FILTER_VALIDATE_INT);
            $height = filter_var($_POST['height'], FILTER_VALIDATE_INT);
            if ($width !== false && $height !== false && $width > 2 && $height > 2) {
                $cells = LevelManager::resizeCells($cells, $width, $height);
            }
        }
    }

    public function createLevel(): void
    {
        $levelManager = new LevelManager();
        $level = ['name' => 'Nouveau niveau', 'description' => ''];
        $cells = array_fill(0, 15, array_fill(0, 15, "floor"));
        $id = $levelManager->create($level, $cells);
        header('Location: /editor/edit?id=' . $id);
    }

    public function delete(): void
    {
        $levelManager = new LevelManager();
        $levels = $levelManager->selectAll('id');
        $levelsId = array_column($levels, 'id');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $id => $value) {
                $id = filter_var($id, FILTER_VALIDATE_INT);
                if ($id !== false && in_array($id, $levelsId) && $value === 'delete') {
                    $levelManager->delete($id);
                }
            }
        }
        header('Location: /editor');
    }
}
