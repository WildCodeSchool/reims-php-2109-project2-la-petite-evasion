<?php

namespace App\Controller;

use App\Model\LevelManager;

class GameController extends AbstractController
{
    private const VIEWPOINT_RADIUS = 2;

    private function generateCellDetails(string $cellType, int $cellX, int $cellY, int $playerX, int $playerY): array
    {
        $cellDetails = [
            "classes" => [],
            "isLink" => false,
            "linkX" => $playerX,
            "linkY" => $playerY,
            "linkText" => '',
        ];
        if ($cellType === '') {
            $cellDetails['classes'][] = "wall";
        } else {
            $deltaX = $cellX - $playerX;
            $deltaY = $cellY - $playerY;

            $cellDetails['linkX'] += $deltaX;
            $cellDetails['linkY'] += $deltaY;

            $arrows = [
                -1 => "↑",
                1 => "↓",
                -10 => "←",
                10 => "→",
            ];
            $cellDetails['linkText'] = $arrows[$deltaX * 10 + $deltaY] ?? '';
            $cellDetails['isLink'] = isset($arrows[$deltaX * 10 + $deltaY]);

            if ($cellType === LevelManager::CELL_WALL) {
                $cellDetails['linkX'] = $playerX;
                $cellDetails['linkY'] = $playerY;
                $cellDetails['classes'][] = "wall";
            } elseif ($cellType === LevelManager::CELL_FLOOR) {
                $cellDetails['classes'][] = "floor";
            }
            if ($playerX === $cellX && $playerY === $cellY) {
                $cellDetails['classes'][] = "player";
            }
        }
        return $cellDetails;
    }

    /**
     * Generate a grid of tiles visible from specified coordinates
     */
    private function generateViewpoint(array $cells, int $playerX, int $playerY): array
    {
        $grid = [];
        for ($y = $playerY - self::VIEWPOINT_RADIUS; $y <= $playerY + self::VIEWPOINT_RADIUS; ++$y) {
            $row = [];
            for ($x = $playerX - self::VIEWPOINT_RADIUS; $x <= $playerX + self::VIEWPOINT_RADIUS; ++$x) {
                $cell = '';
                if (isset($cells[$y][$x])) {
                    $cell = $cells[$y][$x];
                }

                $details = $this->generateCellDetails($cell, $x, $y, $playerX, $playerY);

                if ($y === 0 && $x === 0) {
                    $details['classes'][] = "start";
                }

                if ($y === count($cells) - 1 && $x === count($cells[$y]) - 1) {
                    $details['classes'][] = "finish";
                }

                $row[] = $details;
            }
            $grid[] = $row;
        }
        return $grid;
    }


    /**
     * Show first level
     */
    public function index(?int $playerX, ?int $playerY): string
    {
        $playerX = $playerX ?? 0;
        $playerY = $playerY ?? 0;

        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById(1);
        $cells = LevelManager::parseContent($level['content']);
        $grid = $this->generateViewpoint($cells, $playerX, $playerY);

        return $this->twig->render('Game/index.html.twig', ['level' => $level, 'grid' => $grid]);
    }

}
