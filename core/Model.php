<?php

namespace Core;

use Core\Utils\Naming;

abstract class Model extends DB
{
    public function delete($matches)
    {
        $keys = array_keys($matches);
        $values = array_values($matches);
        $preparedChunks = [];
        foreach ($keys as $key) {
            $preparedChunks[] = $key . ' = ?';
        }
        $result = $this->query("DELETE FROM " . $this->getTable() . " WHERE " . implode(' AND ', $preparedChunks), $values);
        return $result;
    }

    public function findOne($value, string $field = 'id')
    {
        $result = $this->query("SELECT * FROM " . $this->getTable() . " WHERE $field = ? LIMIT 1", [$value]);
        if (!empty($result)) {
            return $result[0];
        }
        return $result;
    }

    public function find(array $matches, int $limit = 0, int $begin = 0)
    {
        $keys = array_keys($matches);
        $values = array_values($matches);
        $preparedChunks = [];
        foreach ($keys as $key) {
            $preparedChunks[] = $key . ' = ?';
        }
        $query = "SELECT * FROM " . $this->getTable() . " WHERE " . implode(' AND ', $preparedChunks);
        if ($limit != 0) {
            $query .= " LIMIT $limit";
            if ($begin != 0) {
                $query .= " OFFSET $begin";
            }
        }
        return $this->query($query, $values);
    }

    public function findAll(int $limit = 0, int $begin = 0)
    {
        $query = "SELECT * FROM " . $this->getTable();
        if ($limit != 0) {
            $query .= " LIMIT $limit";
            if ($begin != 0) {
                $query .= " OFFSET $begin";
            }
        }
        return $this->query($query);
    }

    public function update($updates, $matches = [])
    {
        $updateKeys = array_keys($updates);
        $values = array_values($updates);
        $updateChunks = [];
        foreach ($updateKeys as $key) {
            $updateChunks[] = $key . ' = ?';
        }
        $query = "UPDATE " . $this->getTable() . " SET " . implode(", ", $updateChunks);
        if (!empty($matches)) {
            $whereKeys = array_keys($matches);
            $values = array_merge($values, array_values($matches));
            $whereChunks = [];
            foreach ($whereKeys as $key) {
                $whereChunks[] = $key . ' = ?';
            }
            $query .= " WHERE " . implode(' AND ', $whereChunks);
        }
        return $this->query($query, $values);
    }

    private function getTable()
    {
        return Naming::getModelPseudo(get_called_class());
    }
}