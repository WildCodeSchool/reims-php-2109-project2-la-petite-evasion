<?php

namespace App\Controller;

use App\Model\LevelManager;
use App\Model\TileManager;

class LevelEditorController extends AbstractController
{
    private const DEFAULT_LEVEL_SIZE = 15;

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
        $tileManager = new TileManager($id);
        $tiles = $tileManager->selectAllTiles();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->parsePost($level, $tiles);
            if (!$errors) {
                // TODO: dont hardcode finish position
                $maxY = max(array_keys($tiles));
                $maxX = max(array_keys($tiles[$maxY]));
                $tiles[$maxY][$maxX] = 'finish';

                $levelManager->update($level);
                $tileManager->insert($tiles);
            }
        }
        return $this->twig->render('Editor/edit.html.twig', [
            'level' => $level,
            'grid' => $tiles,
            'errors' => $errors
        ]);
    }

    private function parsePost(array &$level, array &$tiles): array
    {
        $errors = [];
        if (!$this->parsePostNameDescription($level)) {
            $errors[] = 'Le nom est invalide';
        }
        if (!$errors && !$this->parsePostDimensions($level)) {
            $errors[] = 'Les dimensions sont invalides';
        }
        if (!$errors && !$this->parsePostTiles($level, $tiles)) {
            $errors[] = 'Les tuiles sont invalides';
        }
        return $errors;
    }

    private function parsePostNameDescription(&$level): bool
    {
        if (empty($_POST['name']) || strlen($_POST['name']) > 30) {
            return false;
        }
        $level['name'] = trim($_POST['name']);
        $level['description'] = trim($_POST['description'] ?? '');
        return true;
    }

    private function parsePostDimensions(array &$level): bool
    {
        if (!empty($_POST["width"]) && !empty($_POST["height"])) {
            $width = filter_var($_POST['width'], FILTER_VALIDATE_INT);
            $height = filter_var($_POST['height'], FILTER_VALIDATE_INT);
            if ($width === false || $height === false || $width <= 2 || $height <= 2) {
                return false;
            }
            $level['width'] = $width;
            $level['height'] = $height;
        }
        return true;
    }

    private function parsePostTiles(array &$level, array &$tiles): bool
    {
        $row = array_fill(0, $level['width'], TileManager::TYPE_FLOOR);
        $tiles = array_fill(0, $level['height'], $row);

        foreach ($_POST as $entry => $value) {
            $capture = [];
            if (preg_match("/^cell-(?<x>[0-9]+)-(?<y>[0-9]+)$/", $entry, $capture)) {
                if (!in_array($value, TileManager::TYPES)) {
                    return false;
                }
                $posX = intval($capture['x']);
                $posY = intval($capture['y']);
                if (isset($tiles[$posY][$posX])) {
                    $tiles[$posY][$posX] = $value;
                }
            }
        }
        return true;
    }

    public function createLevel(): void
    {
        $levelManager = new LevelManager();
        $level = [
            'name' => 'Nouveau niveau',
            'description' => '',
            'width' => self::DEFAULT_LEVEL_SIZE,
            'height' => self::DEFAULT_LEVEL_SIZE,
            'start_x' => 0,
            'start_y' => 0,
        ];
        $id = $levelManager->create($level);

        $tileManager = new TileManager($id);
        $row = array_fill(0, self::DEFAULT_LEVEL_SIZE, TileManager::TYPE_FLOOR);
        $tiles = array_fill(0, self::DEFAULT_LEVEL_SIZE, $row);
        $tileManager->insert($tiles);

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
