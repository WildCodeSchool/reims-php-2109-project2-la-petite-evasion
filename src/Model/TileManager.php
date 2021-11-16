<?php

namespace App\Model;

class TileManager extends AbstractManager
{
    public const TABLE = 'tile';

    public function insert(int $levelId, array $tiles): void
    {
        $query =
            "DELETE FROM " . self::TABLE . "WHERE level_id = :level_id;" .
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
        $statement->bindValue('level_id', $levelId, \PDO::PARAM_INT);
        $index = 0;
        foreach ($tiles as $y => $row) {
            foreach ($row as $x => $type) {
                $statement->bindValue("x_$index", $x, \PDO::PARAM_INT);
                $statement->bindValue("y_$index", $y, \PDO::PARAM_INT);
                $statement->bindValue("type_$index", $tiles[$y][$x], \PDO::PARAM_STR);
                ++$index;
            }
        }

        $statement->execute();
    }

    public function selectAllByLevelId(int $levelId): array
    {
        $statement = $this->pdo->prepare("SELECT * FROM " . static::TABLE . " WHERE level_id=:level_id");
        $statement->bindValue('level_id', $levelId, \PDO::PARAM_INT);
        $statement->execute();
        $tiles = [];
        foreach ($statement->fetchAll() as $tile) {
            $tiles[$tile['y']][$tile['x']] = $tile['type'];
        }
        return $tiles;
    }
}
