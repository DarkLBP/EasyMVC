<?php
namespace Core;

use Core\Utils\Naming;

abstract class Model extends DB
{
    public function findOne($value, string $field = 'id'): array {
        $result = $this->query("SELECT * FROM " . $this->getTable() . " WHERE $field = ?", [$value], 1);
        if (!empty($result)) {
            return $result[0];
        }
        return $result;
    }

    public function findAll(array $fields, array $values): array {
        $preparedChunks = [];
        foreach ($fields as $field) {
            $preparedChunks[] = $field . ' = ?';
        }
        $result = $this->query("SELECT * FROM " . $this->getTable() . " WHERE " . implode(' AND ', $preparedChunks), $values);
        return $result;
    }

    public function getAll() {
        $result = $this->query("SELECT * FROM " . $this->getTable());
        return $result;
    }

    private function getTable() {
        return Naming::getModelPseudo(get_called_class());
    }
}