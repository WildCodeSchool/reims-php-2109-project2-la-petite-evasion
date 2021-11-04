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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // clean $_POST data
            $cells = [];

            if (!empty($_POST['name'])) {
                $name = $_POST['name'];
                $description = $_POST['description'];
                $level['name'] = $name;
                $level['description'] = $description;

                foreach ($_POST as $entry => $value) {
                    $capture = [];
                    $matched = preg_match("/^cell-(?<x>[0-9]+)-(?<y>[0-9]+)$/", $entry, $capture);
                    if ($matched === 1) {
                        if (array_key_exists('x', $capture) && array_key_exists('y', $capture)) {
                            $x = intval($capture['x']);
                            $y = intval($capture['y']);
                            if (!array_key_exists($y, $cells)) {
                                $cells[$y] = [];
                            }
                            $cells[$y][$x] = $value;
                        }
                    }
                }
                foreach ($cells as $row) {
                    ksort($row);
                }
                ksort($cells);
                var_dump($cells);
                $levelManager = new LevelManager();
                $levelManager->update($level, $cells);
            }
        }
        return $this->twig->render('Editor/edit.html.twig', ['level' => $level, 'grid' => $grid]);
    }
}
