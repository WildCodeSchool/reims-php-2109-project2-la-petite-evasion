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

            if ($cellType === "1") {
                $cellDetails['linkX'] = $playerX;
                $cellDetails['linkY'] = $playerY;
                $cellDetails['classes'][] = "wall";
            } elseif ($cellType === "0") {
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
    private function generateViewpoint(array $level, int $playerX, int $playerY): array
    {
        $cells = array_map(fn ($playerX) => str_split($playerX), explode(',', $level['content']));
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
    public function index(): string
    {
        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById(1);
        $grid = $this->generateViewpoint($level, 0, 0);

        return $this->twig->render('Game/index.html.twig', ['level' => $level, 'grid' => $grid]);
    }
}
