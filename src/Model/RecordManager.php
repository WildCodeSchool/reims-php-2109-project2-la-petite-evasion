<?php

namespace App\Model;

class RecordManager extends AbstractManager
{
    public const TABLE = 'record';

    public function insert(array $record)
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO " . self::TABLE .
            " (name, time, levelid) VALUES (:name, :time, :levelid)"
        );
        $statement->bindValue('name', $record['name'], \PDO::PARAM_STR);
        $statement->bindValue('time', $record['time'], \PDO::PARAM_STR);

        $statement->execute();
        return (int)$this->pdo->lastInsertId();
    }
}
