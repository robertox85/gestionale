<?php

namespace App\Models;

use App\Libraries\Database;

class BaseModel
{
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->load([':id' => $id]);
        }
    }


    // Load by id
    public function load($params = [])
    {

        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);

        $sql = "SELECT * FROM " . $tableName . " WHERE id = :id";
        $options = [
            'query' => $sql,
            'params' => $params
        ];
        $result = $db->query($options);
        if (isset($result[0])) {
            foreach ($result[0] as $property => $value) {
                // skip id, created_at, updated_at
                if ($property === 'created_at' || $property === 'updated_at') {
                    continue;
                }
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
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $sql = "DELETE FROM " . $tableName . " WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $this->getId()];
        $result = $db->query($options);
        return $result;
    }

    // save
    public function save()
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $sql = "INSERT INTO " . $tableName . " (";
        $sql .= implode(',', array_keys($this->getProperties()));
        $sql .= ") VALUES (";
        $sql .= implode(',', array_map(fn($key) => ':' . $key, array_keys($this->getProperties())));
        $sql .= ")";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = $this->getProperties();
        $db->query($options);
        $this->setId($db->lastInsertId());
        return $this->getId();
    }

    // update
    public function update()
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $sql = "UPDATE " . $tableName . " SET ";
        $sql .= implode(',', array_map(fn($key) => $key . ' = :' . $key, array_keys($this->getProperties())));
        $sql .= " WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = $this->getProperties();
        $db->query($options);
        return $this;
    }

    // generate Form
    public function getTableName()
    {
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        return $tableName;
    }

    public function getFields()
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);

        $sql = "SHOW COLUMNS FROM " . $tableName;
        $options = [];
        $options['query'] = $sql;

        $result = $db->query($options);
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
                $relatedRecords = $db->query($options);
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
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);

        $sql = "SELECT * FROM " . $tableName;

        $options = [];
        if (!empty($args)) {
            $options['limit'] = $args['limit'];
            $options['offset'] = ($args['currentPage'] - 1) * $args['limit'];
            $options['order_dir'] = $args['order'];
            $options['order_by'] = $args['sort'];
        }

        $options['query'] = $sql;

        return $db->query($options);
    }

    // delete by id
    public static function deleteById($id)
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $sql = "DELETE FROM " . $tableName . " WHERE id_" . $tableName . " = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id];

        $result = $db->query($options);

        return $result;
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
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $singularLower = strtolower($shortClassName);
        $sql = "SELECT * FROM " . $tableName . " WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id];

        $result = $db->query($options);

        if (isset($result[0])) {
            return new $className($result[0]->{'id'});
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
        $array = get_object_vars($this);
        if (array_key_exists('id_utente', $array)) {
            $array['id'] = $array['id_utente'];
            unset($array['id_utente']);
        }
        return $array;
    }

    private function getProperties()
    {
        $array = get_object_vars($this);
        /*if (array_key_exists('id_utente', $array)) {
            $array['id'] = $array['id_utente'];
            unset($array['id_utente']);
        }*/
        return $array;
    }

    // getTableName

}