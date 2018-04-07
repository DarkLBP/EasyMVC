<?php

namespace Core;


abstract class Model extends DB
{
    private $joins = [];
    public $tableName = '';

    public function __construct()
    {
        parent::__construct();
        $modelName = Naming::getModelPseudo(get_called_class());
        $tableName = '';
        for ($i = 0; $i < strlen($modelName); $i++){
            if (ctype_upper($modelName[$i])) {
                $tableName .= '_' . strtolower($modelName[$i]);
            } else {
                $tableName .= $modelName[$i];
            }
        }
        $this->tableName = $tableName;
    }

    public function buildJoin(bool $append = false): string
    {
        /**
         * @var $model Model
         */
        if (empty($this->joins)) {
            return '';
        }
        if (!$append) {
            $joinQuery = $this->tableName;
        } else {
            $joinQuery = '';
        }
        foreach ($this->joins as $join) {
            $model =  $join['model'];
            $srcIndex = $join['srcIndex'];
            $targetIndex = $join['targetIndex'];
            $targetTable = $model->tableName;
            $joinQuery .= " INNER JOIN $targetTable ON $this->tableName.$srcIndex = $targetTable.$targetIndex "
                . $model->buildJoin(true) . ' ';
        }
        return trim($joinQuery);
    }

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
        $result = $this->query("DELETE FROM " . $this->tableName . " WHERE " . implode(' AND ', $preparedChunks), $values);
        return $result;
    }

    /**
     * Retrieves a row from the database matching one condition
     * @param mixed $value The value requested
     * @param string $field The field where that value should match. Defaults to 'id'
     * @param array $cols Columns to be retrieved
     * @return array|bool The first row that match or false in case of error
     */
    public function findOne($value, $field = 'id', array $cols = [])
    {
        $result = $this->find([$field => $value], $cols, 1);
        if (!empty($result)) {
            return $result[0];
        }
        return $result;
    }

    /**
     * Seeks for one or more rows matching the conditions
     * @param array $matches All the conditions that the rows must pass
     * @param array $cols Columns to be retrieved
     * @param int $limit An optional limit of returned rows
     * @param int $begin An optional starting mark where the returned rows should start from
     * @return array|bool An array of rows or false in case of error
     */
    public function find(array $matches = [], array $cols = [], int $limit = 0, int $begin = 0)
    {
        $colsStr = '';
        if (!empty($cols)) {
            foreach ($cols as $col) {
                $colsStr .= ', ';
                if (is_array($col)) {
                    $colsStr .= "$col[0] AS '$col[1]'";
                } else {
                    $colsStr .= $col;
                }
            }
            $colsStr = ltrim($colsStr, ', ');
        } else {
            $colsStr = '*';
        }
        $keys = array_keys($matches);
        $values = array_values($matches);
        $preparedChunks = [];
        foreach ($keys as $key) {
            $preparedChunks[] = $key . ' = ?';
        }
        $joinQuery = $this->buildJoin();
        if (empty($joinQuery)) {
            $query = "SELECT $colsStr FROM $this->tableName";
        } else {
            $query = "SELECT $colsStr FROM $joinQuery";
        }
        if (!empty($matches)) {
            $query .= ' WHERE ' . implode(' AND ', $preparedChunks);
        }
        if ($limit != 0) {
            $query .= " LIMIT $limit";
            if ($begin != 0) {
                $query .= " OFFSET $begin";
            }
        }
        return $this->query($query, $values);
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
        $query = 'INSERT INTO ' . $this->tableName . ' (`' . implode('`,`', $keys) . '`) 
                    VALUES (' . implode(', ', array_fill(0, count($values), '?')) . ')';
        return $this->query($query, $values);
    }

    public function join(Model $model, string $srcIndex, string $targetIndex)
    {
        $this->joins[] = [
            'model' => $model,
            'srcIndex' => $srcIndex,
            'targetIndex' => $targetIndex
        ];
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
        $query = "UPDATE " . $this->tableName . " SET " . implode(", ", $updateChunks);
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
}