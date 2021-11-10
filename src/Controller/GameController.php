<?php

namespace App\Controller;

use App\Model\LevelManager;

class GameController extends AbstractController
{
    private const VIEWPOINT_RADIUS = 2;
    private const ACTION_OFFSETS = [
        "up" => ['x' => 0, 'y' => -1],
        "down" => ['x' => 0, 'y' => 1],
        "left" => ['x' => -1, 'y' => 0],
        "right" => ['x' => 1, 'y' => 0],
    ];

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

    private function getPosition(): array
    {
        return $_SESSION['position'] ?? ['x' => 0, 'y' => 0];
    }

    private function move(array $cells, string $action): void
    {
        $position = $this->getPosition();

        if (isset(self::ACTION_OFFSETS[$action])) {
            $offsets = self::ACTION_OFFSETS[$action];
            $position['x'] += $offsets['x'];
            $position['y'] += $offsets['y'];

            if ($cells[$position['y']][$position['x']] === LevelManager::CELL_WALL) {
                $position = $this->getPosition();
            }
        }

        $_SESSION['position'] = $position;
    }

    /**
     * Show first level
     */
    public function index(?string $action): string
    {
        session_start();

        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById(1);
        $cells = LevelManager::parseContent($level['content']);

        $this->move($cells, $action ?? "");
        $position = $this->getPosition();

        $grid = $this->generateViewpoint($cells, $position['x'], $position['y']);

        return $this->twig->render('Game/index.html.twig', ['level' => $level, 'grid' => $grid]);
    }
}
