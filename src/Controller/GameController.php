<?php

namespace App\Controller;

use DateTime;
use App\Model\LevelManager;

class GameController extends AbstractController
{
    private const GAME_STATE_STOPPED = 0;
    private const GAME_STATE_STARTED = 1;
    private const GAME_STATE_FINISHED = 2;

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
            "action" => '',
            "linkText" => '',
        ];
        if ($cellType === '') {
            $cellDetails['classes'][] = "wall";
        } else {
            $deltaX = $cellX - $playerX;
            $deltaY = $cellY - $playerY;

            $actions = [
                -1 => ["text" => "↑", "action" => "up"],
                1 => ["text" => "↓", "action" => "down"],
                -10 => ["text" => "←", "action" => "left"],
                10 => ["text" => "→", "action" => "right"],
            ];

            $cellDetails['action'] = $actions[$deltaX * 10 + $deltaY]['action'] ?? '';
            $cellDetails['linkText'] = $actions[$deltaX * 10 + $deltaY]['text'] ?? '';
            $cellDetails['isLink'] = isset($actions[$deltaX * 10 + $deltaY]);

            if ($cellType === LevelManager::CELL_WALL) {
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

                if ($this->isFinish($cells, ['x' => $x, 'y' => $y])) {
                    $details['classes'][] = "finish";
                }

                $row[] = $details;
            }
            $grid[] = $row;
        }
        return $grid;
    }

    private function isFinish(array $cells, array $position): bool
    {
        $lastRow = end($cells);
        return $position['y'] === array_key_last($cells) &&
               $position['x'] === array_key_last($lastRow);
    }

    private function getGameState(): int
    {
        return $_SESSION['state'] ?? self::GAME_STATE_STOPPED;
    }

    private function startGame(int $levelId): void
    {
        $_SESSION['state'] = self::GAME_STATE_STARTED;
        $_SESSION['position'] = ['x' => 0, 'y' => 0];
        $_SESSION['levelId'] = $levelId;
        $_SESSION['startTime'] = new DateTime();
    }

    private function reset(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    private function getPosition(): array
    {
        return $_SESSION['position'];
    }

    private function move(array $cells, string $action): void
    {
        if ($action === 'reset') {
            $this->reset();
        }

        $position = $this->getPosition();

        if (isset(self::ACTION_OFFSETS[$action])) {
            $offsets = self::ACTION_OFFSETS[$action];
            $position['x'] += $offsets['x'];
            $position['y'] += $offsets['y'];

            if ($this->isFinish($cells, $position)) {
                header('Location: /win');
                $this->reset();
            } elseif ($cells[$position['y']][$position['x']] === LevelManager::CELL_WALL) {
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
        $levelId = 1;

        session_start();
        $state = $this->getGameState();
        if ($state !== self::GAME_STATE_STARTED) {
            $this->startGame($levelId);
        }

        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById($levelId);
        $cells = LevelManager::parseContent($level['content']);

        $this->move($cells, $action ?? "");
        $position = $this->getPosition();

        $grid = $this->generateViewpoint($cells, $position['x'], $position['y']);

        return $this->twig->render('Game/index.html.twig', ['level' => $level, 'grid' => $grid]);
    }
}
