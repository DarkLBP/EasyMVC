<?php

namespace Core;

abstract class DB
{
    private $conection;

    public function __construct()
    {
        $this->conection = new \PDO('mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_DB,
            DATABASE_USER,
            DATABASE_PASSWORD
        );
    }

    protected function query(string $query, array $params = [])
    {
        $prepared = $this->conection->prepare($query);
        $action = strtolower(substr(rtrim($query), 0, 6));
        $result = $prepared->execute($params);
        if ($result === false) {
            return false;
        }
        switch ($action) {
            case 'select':
                return $prepared->fetchAll(\PDO::FETCH_ASSOC);
            case 'update':
            case 'delete':
                return $prepared->rowCount();
            case 'insert':
                return $this->conection->lastInsertId();
            default:
                return true;
        }
    }
}