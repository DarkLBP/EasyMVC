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
        for ($i = 0; $i < strlen($modelName); $i++) {
            if (ctype_upper($modelName[$i])) {
                $tableName .= '_' . strtolower($modelName[$i]);
            } else {
                $tableName .= $modelName[$i];
            }
        }
        $this->tableName = $tableName;
    }

    /**
     * Generates a JOIN query based of joined tables
     * @param bool $append Whether should include the base table before the JOIN keyword
     * @param array $processed List of processed tables
     * @return string The JOIN query
     */
    public function buildJoin(bool $append = false, array &$processed = []): string
    {
        /**
         * @var $model Model
         */
        if (empty($this->joins)) {
            return '';
        } else if (in_array($this->tableName, $processed)) {
            return '';
        } else {
            $processed[] = $this->tableName;
        }
        if (!$append) {
            $joinQuery = $this->tableName;
        } else {
            $joinQuery = '';
        }
        foreach ($this->joins as $type => $joins) {
            foreach ($joins as $join) {
                $model = $join['model'];
                $srcIndex = $join['srcIndex'];
                $targetIndex = $join['targetIndex'];
                $targetTable = $model->tableName;
                $joinQuery .= " $type JOIN $targetTable ON $this->tableName.$srcIndex = $targetTable.$targetIndex "
                    . $model->buildJoin(true, $processed) . ' ';
            }
        }
        return trim($joinQuery);
    }

    /**
     * Count the number of rows matching the given conditions
     * @param array $matches All the conditions that the rows must pass
     * @param int $limit An optional limit of returned rows
     * @param int $begin An optional starting mark where the returned rows should start from
     * @return int An array of rows or false in case of error
     */
    public function count(array $matches = [], int $limit = 0, int $begin = 0): int
    {
        $result = $this->find($matches, [["COUNT(*)", "count"]], $limit, $begin);
        if (isset($result[0])) {
            return intval($result[0]['count']);
        }
        return -1;
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
     * @param array $orderBy The order of the results before limiting
     * @return array|bool The first row that match or false in case of error
     */
    public function findOne($value, $field = 'id', array $cols = [], array $orderBy = [])
    {
        $result = $this->find([$field => $value], $cols, $orderBy, 1);
        if (!empty($result)) {
            return $result[0];
        }
        return $result;
    }

    /**
     * Seeks for one or more rows matching the conditions
     * @param array $matches All the conditions that the rows must pass
     * @param array $cols Columns to be retrieved
     * @param array $orderBy The order of the results before applying limits
     * @param int $limit An optional limit of returned rows
     * @param int $begin An optional starting mark where the returned rows should start from
     * @return array|bool An array of rows or false in case of error
     */
    public function find(array $matches = [], array $cols = [], array $orderBy = [], int $limit = 0, int $begin = 0)
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
        $orderStr = '';
        if (!empty($orderBy)) {
            $orderStr .= ' ORDER BY ';
            foreach ($orderBy as $column => $order) {
                $orderStr .= $column . ' ' . $order . ',';
            }
            $orderStr = rtrim($orderStr, ',');
            $query .= $orderStr;
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
     * Inserts a row to the database
     * @param array $row The row data
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

    /**
     * Adds a model dependency for a JOIN
     * @param string $type Type of join
     * @param Model $model The instance of the model to be joined with
     * @param string $srcIndex The column name of the current table
     * @param string $targetIndex The column name of the joined table
     */
    public function join(Model $model, string $srcIndex, string $targetIndex, string $type = '')
    {
        if ($type == '') {
            $type = 'INNER';
        }
        $type = strtoupper($type);
        $this->joins[$type][] = [
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