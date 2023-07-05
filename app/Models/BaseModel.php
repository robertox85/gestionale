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
                /*if (property_exists($this, $property)) {
                    $this->$property = $value;
                }*/
                $setterMethod = 'set' . ucfirst(str_replace('_', '', $property));
                if (method_exists($this, $setterMethod)) {
                    $this->$setterMethod($value);
                } else {
                    // Deprecated: Creation of dynamic property App\Models\Permesso::$id is deprecated in /Users/robertodimarco/PhpstormProjects/GestLex/app/Models/BaseModel.php on line 41
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


    public static function getAll()
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $sql = "SELECT * FROM " . $tableName;
        $options = [];
        $options['query'] = $sql;

        $result = $db->query($options);

        return $result;
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
    private static function getPluralName(string $shortClassName)
    {
        // in Italiano
        if (substr($shortClassName, -2) === 'ca') {
            return substr($shortClassName, 0, -2) . 'che';
        }
        elseif (substr($shortClassName, -1) === 'o') {
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
        }
         else {
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
        if (array_key_exists('id_utente', $array)) {
            $array['id'] = $array['id_utente'];
            unset($array['id_utente']);
        }
        return $array;
    }
}