<?php

namespace App\Models;

use App\Libraries\Database;

class BaseModel
{
    protected $db;
    protected $tableName;
    protected $primaryKey;

    public function __construct($id = null)
    {
        $this->db = Database::getInstance();
        $this->tableName = $this->getPluralName($this->getShortClassName());
        $this->primaryKey = 'ID_' . ucfirst($this->getShortClassName());

        if ($id !== null) {
            $this->load([':id' => $id]);
        }
    }


    // Load by id
    public function load($params = [])
    {

        $tableName = $this->getTableName();

        $sql = "SELECT * FROM " . $tableName . " WHERE {$this->primaryKey} = :id";
        $options = [
            'query' => $sql,
            'params' => $params
        ];
        $result = $this->db->query($options);
        if (isset($result[0])) {
            foreach ($result[0] as $property => $value) {
                $setterMethod = 'set' . ucfirst(str_replace('_', '', $property));
                if (method_exists($this, $setterMethod)) {
                    $this->$setterMethod($value);
                } else {
                    $this->$property = $value;
                }
            }
        }
    }

    // delete
    public function delete()
    {

        $tableName = self::getPluralName($this->getShortClassName());
        $sql = "DELETE FROM " . $tableName . " WHERE {$this->primaryKey} = :id";
        $options = [];
        $options['query'] = $sql;
        $getter = 'get' . ucfirst($this->getShortClassName());
        $options['params'] = [':id' => $this->$getter()];
        $result = $this->db->query($options);
        return $result;
    }

    // save
    public function save()
    {
        $tableName = self::getPluralName($this->getShortClassName());
        $sql = "INSERT INTO " . $tableName . " (";
        $sql .= implode(',', array_keys($this->getProperties()));
        $sql .= ") VALUES (";
        $sql .= implode(',', array_map(fn($key) => ':' . $key, array_keys($this->getProperties())));
        $sql .= ")";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = $this->getProperties();
        $this->db->query($options);
        $this->setId($this->db->lastInsertId());
        return $this->getId();
    }

    // update
    public function update()
    {
        $tableName = self::getPluralName($this->getShortClassName());
        $sql = "UPDATE " . $tableName . " SET ";
        $sql .= implode(',', array_map(fn($key) => $key . ' = :' . $key, array_keys($this->getProperties())));
        $sql .= " WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = $this->getProperties();
        $this->db->query($options);
        return $this;
    }

    // generate Form
    public function getTableName($entity = '')
    {
        return self::getPluralName($this->getShortClassName());
    }

    public function getFields($tableName = '')
    {
        $sql = "SHOW COLUMNS FROM " . $this->getTableName();
        $options = [];
        $options['query'] = $sql;

        $result = $this->db->query($options);
        // convert to array
        $result = json_decode(json_encode($result), true);
        $array = [];
        $not_allowed = ['id', 'created_at', 'updated_at', 'nr_pratica'];
        foreach ($result as $key => $value) {
            if (in_array($value['Field'], $not_allowed)) {
                continue; // skip the id field
            }

            if (strpos($value['Field'], 'id_') !== false) {
                $relatedTable = str_replace('id_', '', $value['Field']);  // e.g. "other_table"
                $sql = "SELECT * FROM " . self::getPluralName(ucfirst($relatedTable));
                $options = [];
                $options['query'] = $sql;
                $relatedRecords = $this->db->query($options);
                $options = [];
                foreach ($relatedRecords as $record) {
                    $options[$record->id] = $record->nome;  // Assuming 'name' is a suitable display field
                }
                $array[$value['Field']] = [
                    'type' => 'select',
                    'options' => $options
                ];

                continue;
            }

            if (preg_match("/^enum\(\'(.*)\'\)$/", $value['Type'], $matches)) {
                $enum_values = explode("','", $matches[1]);
                $array[$value['Field']] = [
                    'type' => 'select',
                    'options' => $enum_values
                ];
            } elseif (strpos($value['Type'], 'int') !== false) {
                $array[$value['Field']] = ['type' => 'number'];
            } elseif (strpos($value['Type'], 'varchar') !== false) {
                $array[$value['Field']] = ['type' => 'text'];
            } elseif (strpos($value['Type'], 'text') !== false) {
                $array[$value['Field']] = ['type' => 'textarea'];
            } elseif (strpos($value['Type'], 'date') !== false) {
                $array[$value['Field']] = ['type' => 'date'];
            }
        }
        return $array;
    }

    public static function getAll(array $args = [])
    {

        $tableName = self::getPluralName((new \ReflectionClass(static::class))->getShortName());

        if (isset($args['query']) && !empty($args['query'])) {
            $sql = $args['query'];
        } else {
            $sql = "SELECT * FROM " . $tableName;
        }

        // Aggiunge le clausole JOIN se presenti
        if (isset($args['joins'])) {
            foreach ($args['joins'] as $join) {
                $sql .= " " . $join['type'] . " " . $join['table'] . " ON " . $join['condition'];
            }
        }

        $options = [];

        // Set options for limit, offset, order and sort
        if (isset($args['limit'])) {
            $options['limit'] = $args['limit'];
        }

        if (isset($args['currentPage'])) {
            $options['offset'] = ($args['currentPage'] - 1) * $options['limit'];
        }

        if (isset($args['order_by'])) {
            $options['order_by'] = $args['order_by'];
        }

        if (isset($args['order'])) {
            $options['order_dir'] = $args['order'];
        }

        if (!empty($args['where'])) {
            $sql .= " WHERE ";
            $whereClauses = [];
            foreach ($args['where'] as $field => $values) {
                if (isset($values['value']) && isset($values['operator'])) {
                    $operator = $values['operator'];
                    $value = $values['value'];
                    if (is_array($value)) {
                        $placeholders = implode(',', array_fill(0, count($value), '?'));
                        $whereClauses[] = "$tableName.$field $operator ($placeholders)";
                        foreach ($value as $val) {
                            $options['params'][] = $val;
                        }
                    } else {
                        $whereClauses[] = "$tableName.$field $operator ?";
                        $options['params'][] = $value;
                    }
                }
            }
            $sql .= implode(' AND ', $whereClauses);
        }

        $options['query'] = $sql;



        // return instance of the class
        $result = Database::getInstance()->query($options);
        $array = [];
        foreach ($result as $record) {
            $className = static::class  ;
            $array[] = new $className($record->id);
        }
        return $array;
    }

    public static function get_TotalCount($args = [])
    {
        $tableName = self::getPluralName((new \ReflectionClass(static::class))->getShortName());

        $sql = "SELECT COUNT(*) FROM " . $tableName;

        $options = [];

        if (isset($args['where']) && is_array($args['where'])) {
            $sql .= " WHERE ";
            $whereClauses = [];

            foreach ($args['where'] as $column => $condition) {
                $operator = $condition['operator'] ?? '=';
                $value = $condition['value'];

                if (is_array($value)) {
                    $placeholders = implode(',', array_fill(0, count($value), '?'));
                    $whereClauses[] = "$tableName.$column IN ($placeholders)";
                    foreach ($value as $v) {
                        $options['params'][] = $v;
                    }
                } else {
                    $whereClauses[] = "$tableName.$column $operator ?";
                    $options['params'][] = $value;
                }
            }

            $sql .= implode(' AND ', $whereClauses);
        }

        $options['query'] = $sql;

        $result = Database::getInstance()->query($options);
        return $result[0]->{'COUNT(*)'};
    }

    // delete by id
    public static function deleteById($id)
    {
        $tableName = self::getPluralName((new \ReflectionClass(static::class))->getShortName());
        $sql = "DELETE FROM " . $tableName . " WHERE id_" . $tableName . " = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id];

        return Database::getInstance()->query($options);
    }

    // to Array
    public static function getPluralName(string $shortClassName)
    {
        // in Italiano
        if (substr($shortClassName, -2) === 'ca') {
            return substr($shortClassName, 0, -2) . 'che';
        } elseif (substr($shortClassName, -1) === 'o') {
            return substr($shortClassName, 0, -1) . 'i';
        } elseif (substr($shortClassName, -1) === 'e') {
            return substr($shortClassName, 0, -1) . 'i';
        } elseif (substr($shortClassName, -1) === 'a') {
            return substr($shortClassName, 0, -1) . 'e';
        } elseif (substr($shortClassName, -1) === 'i') {
            return substr($shortClassName, 0, -1) . 'i';
        } elseif (substr($shortClassName, -1) === 'u') {
            return substr($shortClassName, 0, -1) . 'i';
        } elseif (substr($shortClassName, -1) === 's') {
            return $shortClassName;
        } else {
            return $shortClassName . 's';
        }
    }

    // get by id
    public static function getById($id)
    {
        $tableName = self::getPluralName((new \ReflectionClass(static::class))->getShortName());

        $sql = "SELECT * FROM " . $tableName . " WHERE id_" . $tableName . " = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id];

        $result = Database::getInstance()->query($options);

        if (isset($result[0])) {
            $className = static::class;
            return new $className($result[0]->{'id_' . $tableName});
        } else {
            return null;
        }
    }

    // getByPropertyName
    public static function getByPropertyName($property, $value)
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $singularLower = strtolower($shortClassName);
        $sql = "SELECT * FROM " . $tableName . " WHERE " . $property . " = :" . $property;
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':' . $property => $value];

        $result = $db->query($options);

        if (isset($result[0])) {
            $id = 'id_' . $singularLower;
            $id_utente = $result[0]->{'id'};
            return new $className($id_utente);
        } else {
            return null;
        }
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->getProperties() as $key => $value) {
            $getter = 'get' . ucfirst($key);
            $array[$key] = $this->$getter();
        }
        return $array;
    }

    private function getProperties()
    {
        $array = get_object_vars($this);
        return $array;
    }

    // setProperties
    public function setFieldIfExistInPost($field)
    {
        $setField = str_replace('_', '', $field);
        $setter = 'set' . ucfirst($setField);
        $data = $this->sanificaInput($_POST);
        if (isset($_POST[$field])) {
            $this->$setter($data[$field]);
        }
    }

    public function sanificaInput(array|null $dati = null, array $ignore_keys = [])
    {
        if (is_null($dati)) {
            return [];
        }
        $sanitized = [];
        foreach ($dati as $key => $value) {
            if (in_array($key, $ignore_keys)) {
                $sanitized[$key] = $value;
            } else if (is_array($value)) {
                $sanitized[$key] = $this->sanificaInput($value);
            } else {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }
        return $sanitized;
    }

    private function getShortClassName()
    {
        $className = static::class;
        return (new \ReflectionClass($className))->getShortName();
    }
}