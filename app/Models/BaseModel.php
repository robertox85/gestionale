<?php

namespace App\Models;

use App\Libraries\Database;

class BaseModel
{

    protected static $table;

    public function __construct($table = null)
    {
        if ($table !== null) {
            static::$table = $table;
        } else {
            static::$table = static::class;
        }
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

        // if is Ruolo, find permissions too
        if ($shortClassName === 'Ruolo') {
            $result = array_map(function ($role) {
                $permissions = Permesso::getByRole($role->id_ruolo);
                /*$permissions = array_map(function ($permission) {
                    return $permission->nome_permesso;
                }, $permissions);*/
                return new Ruolo($role->id_ruolo, $role->nome_ruolo, $permissions);
            }, $result);
        }

        // if is Gruppo, find SottoGruppi too
        if ($shortClassName === 'Gruppo') {
            $result = array_map(function ($gruppo) {

                $sottogruppi = SottoGruppo::getByGroup($gruppo->id_gruppo);

                return new Gruppo($gruppo->id_gruppo, $gruppo->nome_gruppo, $sottogruppi);

            }, $result);
        }

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

        if (substr($shortClassName, -1) === 'o') {
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
        $sql = "SELECT * FROM " . $tableName . " WHERE id_" . $singularLower . " = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id];

        $result = $db->query($options);


        // if is Ruolo, find permissions too
        if ($shortClassName === 'Ruolo') {
            if (count($result) === 1) {
                $role = $result[0];
                // convert stdclass to array
                $role = json_decode(json_encode($role), true);
                $permissions = Permesso::getByRole($role['id_ruolo']);
                /*$permissions = array_map(function ($permission) {
                    return $permission->nome_permesso;
                }, $permissions);*/

                return new Ruolo($role['id_ruolo'], $role['nome_ruolo'], $permissions);
            } else {
                return null;
            }
        }

        // if is Gruppo, find SottoGruppi too
        if ($shortClassName === 'Gruppo') {
            if (count($result) === 1) {
                $gruppo = $result[0];
                // convert stdclass to array
                $gruppo = json_decode(json_encode($gruppo), true);
                $sottogruppi = SottoGruppo::getByGroup($gruppo['id_gruppo']);

                return new Gruppo($gruppo['id_gruppo'], $gruppo['nome_gruppo'], $sottogruppi);
            } else {
                return null;
            }
        }

        if (count($result) === 1) {
            $object = $result[0];
            // convert stdclass to array
            $object = json_decode(json_encode($object), true);
            return new $className($object);
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


    // GetProperty
    public function __get($name)
    {
        $array = get_object_vars($this);
        if (array_key_exists($name, $array)) {
            return $array[$name];
        } else {
            return null;
        }
    }
}