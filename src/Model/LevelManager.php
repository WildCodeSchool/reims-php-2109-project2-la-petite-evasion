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
}
