<?php

namespace App\Model;

class TileManager extends AbstractManager
{
    public const TABLE = 'tile';

    public const TYPE_WALL = 'wall';
    public const TYPE_FLOOR = 'floor';
    public const TYPE_START = 'start';
    public const TYPE_FINISH = 'finish';

    public const TYPES = [
        self::TYPE_WALL,
        self::TYPE_FLOOR,
        self::TYPE_START,
        self::TYPE_FINISH,
    ];

    private int $levelId;

    public function __construct(int $levelId)
    {
        parent::__construct();
        $this->levelId = $levelId;
    }

    public function insert(array $tiles): void
    {
        $query =
            "DELETE FROM " . self::TABLE . " WHERE level_id = :level_id;" .
            "INSERT INTO " . self::TABLE . " (level_id, x, y, type) VALUES ";
        $queryPlaceholders = [];
        $index = 0;
        foreach ($tiles as $row) {
            foreach ($row as $type) {
                $queryPlaceholders[] = "(:level_id, :x_$index, :y_$index, :type_$index)";
                ++$index;
            }
        }
        $query .= implode(', ', $queryPlaceholders) . ";";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue('level_id', $this->levelId, \PDO::PARAM_INT);
        $index = 0;
        foreach ($tiles as $y => $row) {
            foreach ($row as $x => $type) {
                $statement->bindValue("x_$index", $x, \PDO::PARAM_INT);
                $statement->bindValue("y_$index", $y, \PDO::PARAM_INT);
                $statement->bindValue("type_$index", $type, \PDO::PARAM_STR);
                ++$index;
            }
        }

        $statement->execute();
    }

    public function selectAllTiles(): array
    {
        $query = "SELECT * FROM " . static::TABLE . " WHERE level_id=:level_id ORDER BY y ASC, x ASC";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue('level_id', $this->levelId, \PDO::PARAM_INT);
        $statement->execute();
        $tiles = [];
        foreach ($statement->fetchAll() as $tile) {
            $tiles[$tile['y']][$tile['x']] = $tile['type'];
        }
        return $tiles;
    }

    /*
     * input: $area: array containing 'x' and 'y' position as well as 'width' and 'height' dimensions
     */
    public function selectByArea(array $area): array
    {
        $query =
            "SELECT * FROM " . static::TABLE .
            " WHERE level_id=:level_id AND x >= :min_x AND x < :max_x AND y >= :min_y AND y < :max_y" .
            " ORDER BY y ASC, x ASC";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue('level_id', $this->levelId, \PDO::PARAM_INT);
        $statement->bindValue('min_x', $area['x'], \PDO::PARAM_INT);
        $statement->bindValue('max_x', $area['x'] + $area['width'], \PDO::PARAM_INT);
        $statement->bindValue('min_y', $area['y'], \PDO::PARAM_INT);
        $statement->bindValue('max_y', $area['y'] + $area['height'], \PDO::PARAM_INT);
        $statement->execute();
        $tiles = [];
        foreach ($statement->fetchAll() as $tile) {
            $tiles[$tile['y']][$tile['x']] = $tile['type'];
        }
        return $tiles;
    }

    /**
     * Get the value of levelId
     *
     * @return int
     */
    public function getLevelId(): int
    {
        return $this->levelId;
    }
}
