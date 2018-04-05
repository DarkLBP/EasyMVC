<?php

namespace Core;

abstract class Model extends DB
{
    /**
     * Removes all rows from the database that matches the conditions
     * @param array $matches The conditions the rows have to pass
     * @return int|bool Number of rows affected or false in case of error
     */
    public function delete(array $matches)
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

    /**
     * Retrieves a row from the database matching one condition
     * @param mixed $value The value requested
     * @param string $field The field where that value should match. Defaults to 'id'
     * @return array|bool The first row that match or false in case of error
     */
    public function findOne($value, string $field = 'id')
    {
        $result = $this->find([$field => $value], 1);
        if (!empty($result)) {
            return $result[0];
        }
        return $result;
    }

    /**
     * Seeks for one or more rows matching the conditions
     * @param array $matches All the conditions that the rows must pass
     * @param int $limit An optional limit of returned rows
     * @param int $begin An optional starting mark where the returned rows should start from
     * @return array|bool An array of rows or false in case of error
     */
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

    /**
     * Returns all rows from a table with an optional limit
     * @param int $limit The number of rows to be retrieved. Default 0.
     * @param int $begin From which row should start to be retrieved. Default 0.
     * @return array|bool All matches or false in case of error
     */
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

    /**
     * Insert a row to the database
     * @param array $row
     * @return bool|string The id of the of the last insert or false in case of error
     */
    public function insert(array $row)
    {
        $values = array_values($row);
        $keys = array_keys($row);
        $query = 'INSERT INTO ' . $this->getTable() . ' (`' . implode('`,`', $keys) . '`) 
                    VALUES (' . implode(', ', array_fill(0, count($values), '?')) . ')';
        return $this->query($query, $values);
    }

    /**
     * Performs an update to the database
     * @param array $updates The changed to be made
     * @param array $matches The conditions that should match
     * @return bool|int The number of affected rows or false if error happened
     */
    public function update(array $updates, array $matches = [])
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

    /**
     * Returns the name of the table using the model context
     * @return string The name of the table
     */
    private function getTable()
    {
        $modelName = Naming::getModelPseudo(get_called_class());
        $tableName = '';
        for ($i = 0; $i < strlen($modelName); $i++){
            if (ctype_upper($modelName[$i])) {
                $tableName .= '_' . strtolower($modelName[$i]);
            } else {
                $tableName .= $modelName[$i];
            }
        }
        return $tableName;
    }
}