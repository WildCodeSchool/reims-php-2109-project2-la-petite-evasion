<?php

namespace App\Controller;

use App\Model\LevelManager;

class GameController extends AbstractController
{
    private const VIEWPOINT_RADIUS = 2;

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
                $classes = [];
                if (!isset($cells[$y][$x])) {
                    $classes[] = "wall";
                } else {
                    $cell = $cells[$y][$x];
                    if ($cell === "1") {
                        $classes[] = "wall";
                    } elseif ($cell === "0") {
                        $classes[] = "floor";
                    }
                    if ($y === $playerY && $playerX === $x) {
                        $classes[] = "player";
                    }
                }
                $row[] = $classes;
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
