<?php

namespace App\Model;

class LevelManager extends AbstractManager
{
    public const TABLE = 'level';
    public const CELL_WALL = 'wall';
    public const CELL_FLOOR = 'floor';

    public static function parseContent(string $content): array
    {
        $cells = [];
        foreach (explode(',', $content) as $row) {
            $cellRow = [];
            foreach (str_split($row) as $cell) {
                if ($cell === '1') {
                    $cellRow[] = self::CELL_WALL;
                } else {
                    $cellRow[] = self::CELL_FLOOR;
                }
            }
            $cells[] = $cellRow;
        }
        return $cells;
    }

    public static function createContent(array $cells): string
    {
        $rows = [];
        foreach ($cells as $row) {
            $rowText = '';
            foreach ($row as $cell) {
                if ($cell === self::CELL_WALL) {
                    $rowText .= '1';
                } else {
                    $rowText .= '0';
                }
            }
            $rows[] = $rowText;
        }
        return implode(',', $rows);
    }
    
    public static function resizeCells(array $cells, int $width, int $height): array
    {
        foreach ($cells as &$row) {
            if (count($row) >= $width) {
                $row = array_slice($row, 0, $width);
            } else {
                $difference = $width - count($row);
                $row = array_merge($row, array_fill(0, $difference, "floor"));
            }
        }
        if (count($cells) >= $height) {
            $cells = array_slice($cells, 0, $height);
        } else {
            $difference = $height - count($cells);
            $row = array_fill(0, $width, "floor");
            $cells = array_merge($cells, array_fill(0, $difference, $row));
        }
        return $cells;
    }

     /**
     * Update Level in database
     */
    public function update(array $level, array $cells): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE " . self::TABLE .
            " SET `name` = :name," .
            " `description` = :description," .
            " `content` = :content" .
            " WHERE id=:id"
        );
        $statement->bindValue('id', $level['id'], \PDO::PARAM_INT);
        $statement->bindValue('name', $level['name'], \PDO::PARAM_STR);
        $statement->bindValue('description', $level['description'], \PDO::PARAM_STR);
        $statement->bindValue('content', self::createContent($cells), \PDO::PARAM_STR);
        return $statement->execute();
    }

     /**
     * Create Level in database
     */
    public function create(array $level, array $cells): int
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO " . self::TABLE .
            " (name, description, content) VALUES (:name, :description, :content)"
        );
        $statement->bindValue('name', $level['name'], \PDO::PARAM_STR);
        $statement->bindValue('description', $level['description'], \PDO::PARAM_STR);
        $statement->bindValue('content', self::createContent($cells), \PDO::PARAM_STR);
        $statement->execute();
        return intval($this->pdo->lastInsertId());
    }

     /**
     * Delete row form an ID
     */
    public function delete(int $id): void
    {
        // prepared request
        $statement = $this->pdo->prepare("DELETE FROM " . static::TABLE . " WHERE id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
    }
}
