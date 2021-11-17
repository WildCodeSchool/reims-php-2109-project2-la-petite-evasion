<?php

namespace App\Controller;

use DateInterval;
use DateTime;
use App\Model\LevelManager;
use App\Model\TileManager;

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

        $tileManager = new TileManager($levelId);
        $position = $this->getMovePosition($action ?? '');
        $tiles = $this->getViewpointTiles($tileManager, $position);

        if ($this->canMove($tiles, $position)) {
            $this->move($tiles, $position);
        } else {
            $position = self::getPosition();
            $tiles = $this->getViewpointTiles($tileManager, $position);
        }

        $this->playerClass = self::ACTION_PLAYER_CLASS[$action] ?? "player";
        $grid = $this->generateViewpoint($tiles, $position['x'], $position['y']);

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

    private function getViewpointTiles(TileManager $tileManager, array $position): array
    {
        return $tileManager->selectByArea([
            'x' => $position['x'] - self::VIEWPOINT_RADIUS,
            'y' => $position['y'] - self::VIEWPOINT_RADIUS,
            'width' => (self::VIEWPOINT_RADIUS * 2) + 1,
            'height' => (self::VIEWPOINT_RADIUS * 2) + 1,
        ]);
    }

    private function getMovePosition(string $action): array
    {
        $position = self::getPosition();
        if (isset(self::ACTION_OFFSETS[$action])) {
            $offsets = self::ACTION_OFFSETS[$action];
            $position['x'] += $offsets['x'];
            $position['y'] += $offsets['y'];
        }
        return $position;
    }

    private function canMove(array $tiles, array $position): bool
    {
        if (!isset($tiles[$position['y']][$position['x']])) {
            return false;
        }
        return $tiles[$position['y']][$position['x']] !== TileManager::TYPE_WALL;
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

            if ($cellType === TileManager::TYPE_WALL) {
                $cellDetails['classes'][] = "wall";
            } elseif ($cellType === TileManager::TYPE_FLOOR) {
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
    private function generateViewpoint(array $tiles, int $playerX, int $playerY): array
    {
        $grid = [];
        for ($y = $playerY - self::VIEWPOINT_RADIUS; $y <= $playerY + self::VIEWPOINT_RADIUS; ++$y) {
            $row = [];
            for ($x = $playerX - self::VIEWPOINT_RADIUS; $x <= $playerX + self::VIEWPOINT_RADIUS; ++$x) {
                $cell = '';
                if (isset($tiles[$y][$x])) {
                    $cell = $tiles[$y][$x];
                }

                $details = $this->generateCellDetails($cell, $x, $y, $playerX, $playerY);

                if ($y === 0 && $x === 0) {
                    $details['classes'][] = "start";
                }

                if ($this->isFinish($tiles, ['x' => $x, 'y' => $y])) {
                    $details['classes'][] = "finish";
                }

                $row[] = $details;
            }
            $grid[] = $row;
        }
        return $grid;
    }

    private function isFinish(array $tiles, array $position): bool
    {
        $lastRow = end($tiles);
        return $position['y'] === array_key_last($tiles) &&
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

    private function move(array $tiles, array $position): void
    {
        if ($this->isFinish($tiles, $position)) {
            header('Location: /win');
            $this->finishGame();
        }
        $_SESSION['position'] = $position;
    }
}
