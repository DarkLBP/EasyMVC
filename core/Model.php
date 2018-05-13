<?php

namespace Core;


abstract class Model extends DB
{
    private $joins = [];
    private $tableName = '';

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

    public function __toString(): string
    {
        return $this->tableName;
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
                $tableAlias = $join['tableAlias'];
                if (!empty($tableAlias)) {
                    $joinQuery .= " $type JOIN $targetTable AS $tableAlias ON $this->tableName.$srcIndex = $tableAlias.$targetIndex "
                        . $model->buildJoin(true, $processed) . ' ';
                } else {
                    $joinQuery .= " $type JOIN $targetTable ON $this->tableName.$srcIndex = $targetTable.$targetIndex "
                        . $model->buildJoin(true, $processed) . ' ';
                }
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
        $result = $this->find($matches, [["COUNT(*)" => "count"]], [], [], $limit, $begin);
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
        $query = "DELETE FROM " . $this->tableName;
        $values = [];
        if (!empty($matches)) {
            $where = '';
            foreach ($matches as $condition) {
                if (!is_array($condition)) {
                    //Found logical operator
                    if ($condition === '(' || $condition === ')') {
                        $where .= $condition;
                    }  else {
                        $where .= " $condition ";
                    }
                } else {
                    //Compute query
                    $column = $condition[0];
                    $operand = strtoupper($condition[1]);
                    $value = $condition[2];
                    $where .= $column;
                    if ($operand === 'BETWEEN') {
                        $values[] = $value[0];
                        $values[] = $value[1];
                        $where .= " BETWEEN ? AND ?";
                    } else if ($operand === 'IN') {
                        $where .= " IN (";
                        foreach ($value as $columnValue) {
                            $where .= '?, ';
                            $values[] = $columnValue;
                        }
                        $where = rtrim($where, ', ');
                        $where .= ")";
                    } else {
                        $where .= " $operand ?";
                        $values[] = $value;
                    }
                }
            }
            $query .= " WHERE $where";
        }
        $result = $this->query($query, $values);
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
        $result = $this->find([
            [$field, '=', $value]
        ], $cols, [], $orderBy, 1);
        if (!empty($result)) {
            return $result[0];
        }
        return $result;
    }

    /**
     * Seeks for one or more rows matching the conditions
     * @param array $matches All the conditions that the rows must pass
     * @param array $cols Columns to be retrieved
     * @param array $groupBy Columns to be grouped
     * @param array $orderBy The order of the results before applying limits
     * @param int $limit An optional limit of returned rows
     * @param int $begin An optional starting mark where the returned rows should start from
     * @return array|bool An array of rows or false in case of error
     */
    public function find(array $matches = [], array $cols = [], array $groupBy = [], array $orderBy = [], int $limit = 0, int $begin = 0)
    {
        $colsStr = '';
        if (!empty($cols)) {
            foreach ($cols as $col) {
                if (is_array($col)) {
                    foreach ($col as $name => $alias) {
                        $colsStr .= ", $name AS '$alias'";
                    }
                } else {
                    $colsStr .= ', ' . $col;
                }
            }
            $colsStr = ltrim($colsStr, ', ');
        } else {
            $colsStr = '*';
        }
        $joinQuery = $this->buildJoin();
        if (empty($joinQuery)) {
            $query = "SELECT $colsStr FROM $this->tableName";
        } else {
            $query = "SELECT $colsStr FROM $joinQuery";
        }
        $values = [];
        if (!empty($matches)) {
            $where = '';
            foreach ($matches as $condition) {
                if (!is_array($condition)) {
                    //Found logical operator
                    if ($condition === '(' || $condition === ')') {
                        $where .= $condition;
                    }  else {
                        $where .= " $condition ";
                    }
                } else {
                    //Compute query
                    $column = $condition[0];
                    $operand = strtoupper($condition[1]);
                    $value = $condition[2];
                    $where .= $column;
                    if ($operand === 'BETWEEN') {
                        $values[] = $value[0];
                        $values[] = $value[1];
                        $where .= " BETWEEN ? AND ?";
                    } else if ($operand === 'IN') {
                        $where .= " IN (";
                        foreach ($value as $columnValue) {
                            $where .= '?, ';
                            $values[] = $columnValue;
                        }
                        $where = rtrim($where, ', ');
                        $where .= ")";
                    } else {
                        $where .= " $operand ?";
                        $values[] = $value;
                    }
                }
            }
            $query .= " WHERE $where";
        }
        if (!empty($groupBy)) {
            $query .= ' GROUP BY ' . implode(', ', $groupBy);
        }
        if (!empty($orderBy)) {
            $orderStr = ' ORDER BY ';
            foreach ($orderBy as $column => $order) {
                $orderStr .= $column . ' ' . $order . ', ';
            }
            $orderStr = rtrim($orderStr, ', ');
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
     * @param Model $model The instance of the model to be joined with
     * @param string $srcIndex The column name of the current table
     * @param string $targetIndex The column name of the joined table
     * @param string $type Type of join
     * @param string $tableAlias Alias to be applied to the table
     */
    public function join(Model $model, string $srcIndex, string $targetIndex, string $type = '', string $tableAlias = ''): void
    {
        if ($type == '') {
            $type = 'INNER';
        }
        $type = strtoupper($type);
        $this->joins[$type][] = [
            'model' => $model,
            'srcIndex' => $srcIndex,
            'targetIndex' => $targetIndex,
            'tableAlias' => $tableAlias
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
            $where = '';
            foreach ($matches as $condition) {
                if (!is_array($condition)) {
                    //Found logical operator
                    if ($condition === '(' || $condition === ')') {
                        $where .= $condition;
                    }  else {
                        $where .= " $condition ";
                    }
                } else {
                    //Compute query
                    $column = $condition[0];
                    $operand = strtoupper($condition[1]);
                    $value = $condition[2];
                    $where .= $column;
                    if ($operand === 'BETWEEN') {
                        $values[] = $value[0];
                        $values[] = $value[1];
                        $where .= " BETWEEN ? AND ?";
                    } else if ($operand === 'IN') {
                        $where .= " IN (";
                        foreach ($value as $columnValue) {
                            $where .= '?, ';
                            $values[] = $columnValue;
                        }
                        $where = rtrim($where, ', ');
                        $where .= ")";
                    } else {
                        $where .= " $operand ?";
                        $values[] = $value;
                    }
                }
            }
            $query .= " WHERE $where";
        }
        return $this->query($query, $values);
    }
}