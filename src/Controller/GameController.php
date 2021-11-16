<?php

namespace App\Controller;

use DateInterval;
use DateTime;
use App\Model\LevelManager;

class GameController extends AbstractController
{
    public const GAME_STATE_STOPPED = 0;
    public const GAME_STATE_STARTED = 1;
    public const GAME_STATE_FINISHED = 2;

    private const VIEWPOINT_RADIUS = 2;
    private const ACTION_OFFSETS = [
        "up" => ['x' => 0, 'y' => -1],
        "down" => ['x' => 0, 'y' => 1],
        "left" => ['x' => -1, 'y' => 0],
        "right" => ['x' => 1, 'y' => 0],
    ];

    private const ACTION_PLAYER_CLASS = [
        "up" => 'player-up',
        "down" => 'player-down',
        "left" => 'player-left',
        "right" => 'player-right',
    ];

    private string $playerClass;

    /**
     * Show first level
     */
    public function index(?string $action): string
    {
        $levelId = 1;
        session_start();

        $state = self::getGameState();
        if ($action === 'reset' || $state !== self::GAME_STATE_STARTED) {
            $this->startGame($levelId);
        }

        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById($levelId);
        $cells = LevelManager::parseContent($level['content']);

        $this->move($cells, $action ?? "");
        $position = self::getPosition();

        $this->playerClass = self::ACTION_PLAYER_CLASS[$action] ?? "player";
        $grid = $this->generateViewpoint($cells, $position['x'], $position['y']);

        return $this->twig->render('Game/index.html.twig', ['level' => $level, 'grid' => $grid]);
    }

    public static function getGameState(): int
    {
        return $_SESSION['state'] ?? self::GAME_STATE_STOPPED;
    }

    public static function getPosition(): array
    {
        return $_SESSION['position'];
    }

    public static function getFinishInterval(): DateInterval
    {
        return $_SESSION['finishTime']->diff($_SESSION['startTime']);
    }

    public static function getGameLevelId(): int
    {
        return $_SESSION['levelId'];
    }

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
                $cellDetails['classes'][] = $this->playerClass;
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

    private function startGame(int $levelId): void
    {
        $_SESSION = [
            'state' => self::GAME_STATE_STARTED,
            'position' => ['x' => 0, 'y' => 0],
            'levelId' => $levelId,
            'startTime' => new DateTime(),
        ];
    }

    private function finishGame(): void
    {
        $_SESSION['state'] = self::GAME_STATE_FINISHED;
        $_SESSION['finishTime'] = new DateTime();
    }

    private function move(array $cells, string $action): void
    {
        $position = self::getPosition();

        if (isset(self::ACTION_OFFSETS[$action])) {
            $offsets = self::ACTION_OFFSETS[$action];
            $position['x'] += $offsets['x'];
            $position['y'] += $offsets['y'];

            if ($this->isFinish($cells, $position)) {
                header('Location: /win');
                $this->finishGame();
            } elseif ($cells[$position['y']][$position['x']] === LevelManager::CELL_WALL) {
                $position = self::getPosition();
            }
        }

        $_SESSION['position'] = $position;
    }
}
