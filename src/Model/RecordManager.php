<?php

namespace App\Model;

class RecordManager extends AbstractManager
{
    public const TABLE = 'record';

    public function insert(array $record)
    {
        $time = $record['time']->h . ':' . $record['time']->i . ':' . $record['time']->s;
        $statement = $this->pdo->prepare(
            "INSERT INTO " . self::TABLE .
                " (name, time, level_id) VALUES (:name, :time, :level_id)"
        );
        $statement->bindValue('name', $record['name'], \PDO::PARAM_STR);
        $statement->bindValue('level_id', $record['level_id'], \PDO::PARAM_STR);
        $statement->bindValue('time', $time, \PDO::PARAM_STR);

        $statement->execute();
        return (int)$this->pdo->lastInsertId();
    }

    public function selectRecords()
    {
        $query = "SELECT level_id, record.name, level.name as level_name," .
        "DATE_FORMAT(time, '%i:%s') as time FROM " . static::TABLE .
        " INNER JOIN level ON record.level_id =" .
        " level.id ORDER BY TIME ASC";

        return $this->pdo->query($query)->fetchAll(\PDO::FETCH_GROUP);
    }
}
