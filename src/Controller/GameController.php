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

    /**
     * Show first level
     */
    public function index(?string $action): string
    {
        $levelId = 1;
        session_start();

        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById($levelId);

        $state = self::getGameState();
        if ($action === 'reset' || $state !== self::GAME_STATE_STARTED) {
            $this->startGame($level);
        }

        $tileManager = new TileManager($levelId);
        $position = $this->getMovePosition($action ?? '');
        $tiles = $this->getViewpointTiles($tileManager, $position);

        if ($this->canMove($tiles, $position)) {
            $this->move($tiles, $position);
        } else {
            $position = self::getPosition();
            $tiles = $this->getViewpointTiles($tileManager, $position);
        }

        return $this->twig->render('Game/index.html.twig', [
            'level' => $level,
            'tiles' => $tiles,
            'action' => $action ?? '',
            'position' => $position,
            'radius' => self::VIEWPOINT_RADIUS,
        ]);
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

    private function isFinish(array $tiles, array $position): bool
    {
        if (!isset($tiles[$position['y']][$position['x']])) {
            return false;
        }
        return $tiles[$position['y']][$position['x']] === TileManager::TYPE_FINISH;
    }

    private function startGame(array $level): void
    {
        $_SESSION = [
            'state' => self::GAME_STATE_STARTED,
            'position' => ['x' => $level['start_x'], 'y' => $level['start_y']],
            'levelId' => $level['id'],
            'startTime' => new DateTime(),
        ];
    }

    private function finishGame(): bool
    {
        $finishTime = new DateTime();
        $timeDiff = $finishTime->diff($_SESSION['startTime']);
        if ($timeDiff->y || $timeDiff->m || $timeDiff->d || $timeDiff->h) {
            $_SESSION['state'] = self::GAME_STATE_STOPPED;
            return false;
        }
        $_SESSION['state'] = self::GAME_STATE_FINISHED;
        $_SESSION['finishTime'] = $finishTime;
        return true;
    }

    private function move(array $tiles, array $position): void
    {
        if ($this->isFinish($tiles, $position)) {
            if ($this->finishGame()) {
                header('Location: /win');
            } else {
                header('Location: /');
            }
        }
        $_SESSION['position'] = $position;
    }
}
