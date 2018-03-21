<?php
namespace Core;


abstract class DB
{
    private $conection;
    public function __construct()
    {
        $this->conection = new \PDO('mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_DB,DATABASE_USER, DATABASE_PASSWORD);
    }



    protected function query(string $query, array $params = [], $limit = -1): array {
        if ($limit != -1) {
            $query .= " LIMIT $limit";
        }
        $prepared = $this->conection->prepare($query);
        $prepared->execute($params);
        return $prepared->fetchAll(\PDO::FETCH_ASSOC);
    }
}