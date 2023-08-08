<?php

namespace App\Models;

use App\Libraries\Database;
use App\Libraries\Helper;
use App\Libraries\QueryBuilder;
use Exception;

class BaseModel
{
    protected $db;
    protected $tableName;
    protected $primaryKey;

    public function __get($name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new Exception("Proprietà {$name} non esiste.");
    }

    public function __set($name, $value) {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            throw new Exception("Proprietà {$name} non esiste.");
        }
    }

    public function __construct($id = null)
    {
        $this->db = Database::getInstance();
        $this->tableName = $this->getShortClassName();

        $this->primaryKey = 'id_' . strtolower(Helper::getTablePrimaryKeyName($this->getShortClassName()));

        if ($id !== null) {
            $this->load([':id' => $id]);
        }
    }

    public function getId()
    {
        $getter = 'getId' . ucfirst(Helper::getTablePrimaryKeyName($this->getShortClassName()));
        return $this->$getter();
    }

    // Load by id
    public function load($params = [])
    {
        $qb = new QueryBuilder($this->db);
        $qb = $qb->setTable($this->tableName);
        $qb = $qb->select('*');
        $qb = $qb->where($this->primaryKey, $params[':id'], '=');
        $result = $qb->first();

        if ($result) {
            $this->setProperties($result);
        }

        return false;
    }

    public function delete(int $id = null): void
    {
        if ($id === null) {
            $id = $this->getId();
        }
        $qb = new QueryBuilder($this->db);
        $tableName = $this->getShortClassName();
        $qb = $qb->setTable($tableName);
        $qb = $qb->where($this->primaryKey, $id, '=');
        $qb->delete();
    }

    public function store(array $post): int|bool
    {
        $primaryKey = (new static())->getPrimaryKeyName();
        // Se è presente l'id_notifica, eseguire l'aggiornamento, altrimenti esegui l'inserimento
        return isset($post[$primaryKey]) ? self::update($post) : self::create($post);
    }


    public static function create(array $post): bool|string
    {
        $db = Database::getInstance();
        $qb = new QueryBuilder($db);
        $tableName = (new \ReflectionClass(static::class))->getShortName();
        $qb->setTable($tableName);
        $qb->insert($post);
        return $db->lastInsertId();
    }

    public static function update(array $post)
    {
        $qb = new QueryBuilder(Database::getInstance());
        $tableName = (new \ReflectionClass(static::class))->getShortName();
        $qb->setTable($tableName);
        $primaryKey = (new static())->getPrimaryKeyName();
        $qb->where($primaryKey, $post[$primaryKey], '=');
        unset($post[$primaryKey]);
        return $qb->update($post);

    }

    public static function findById($id): ?BaseModel
    {
        $qb = new QueryBuilder(Database::getInstance());
        $tableName = (new \ReflectionClass(static::class))->getShortName();
        $primaryKeyName = (new static())->getPrimaryKeyName();
        $qb = $qb->setTable($tableName);
        $qb = $qb->select('*');
        $qb = $qb->where($primaryKeyName, $id, '=');
        $result = $qb->first();
        if ($result) {
            $className = static::class;
            $id = $result[$primaryKeyName];
            return new $className($id);
        } else {
            return null;
        }
    }

    private function getShortClassName()
    {
        $className = static::class;
        return (new \ReflectionClass($className))->getShortName();
    }

    private function setProperties(mixed $int)
    {
        foreach ($int as $key => $value) {
            // Formatta il nome della colonna per utilizzarlo come nome della proprietà
            $propertyName = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $setter = 'set' . ucfirst($propertyName);
            $value = $this->castValue($propertyName, $value);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                $this->$propertyName = $value;
            }
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function getPropertyType(string $propertyName): string
    {
        $propertyName = $this->formatPropertyName($propertyName);
        $reflection = new \ReflectionClass(static::class);
        $property = $reflection->getProperty($propertyName);
        $type = $property->getType();
        if ($type !== null) {
            $type = $type->getName();
        }
        return $type;
    }

    private function castValue(string $propertyName, mixed $value)
    {
        $type = $this->getPropertyType($propertyName);
        if ($type === 'int') {
            return (int)$value;
        } elseif ($type === 'string') {
            return (string)$value;
        } elseif ($type === 'bool') {
            return (bool)$value;
        } elseif ($type === 'array') {
            return (array)$value;
        } elseif ($type === 'float') {
            return (float)$value;
        } elseif ($type === 'object') {
            return (object)$value;
        } elseif ($type === 'null') {
            return null;
        } else {
            return $value;
        }
    }


    public function getAll()
    {
        $qb = new QueryBuilder(Database::getInstance());
        $tableName = (new \ReflectionClass(static::class))->getShortName();
        $qb = $qb->setTable($tableName);
        $qb = $qb->select('*');
        return $qb->get();
    }

    public function getPrimaryKeyName()
    {
        return $this->primaryKey;
    }

    public function getDisplayFieldName(): string
    {
        // Puoi definire qui la logica per ottenere il nome del campo appropriato.
        // Ad esempio, se vuoi il primo campo dopo l'id (primary key), puoi ottenere l'elenco dei nomi delle colonne della tabella
        // e selezionare il secondo elemento dell'array (il primo dopo l'id).
        $columnNames = $this->getColumnNames();
        if (empty($columnNames)) {
            return '';
        }
        return $columnNames[1]; // Restituisce il nome del campo appropriato
    }

    public function getColumnNames(): array
    {
        $qb = new QueryBuilder(Database::getInstance());
        $tableName = (new \ReflectionClass(static::class))->getShortName();
        $qb = $qb->setTable($tableName);
        $qb = $qb->select('*');
        $result = $qb->get();
        if (empty($result)) {
            return [];
        }
        return array_keys($result[0]);
    }

    // getByField
    public function getByField($field, $value)
    {
        $qb = new QueryBuilder(Database::getInstance());
        $tableName = (new \ReflectionClass(static::class))->getShortName();
        $qb = $qb->setTable($tableName);
        $qb = $qb->select('*');
        $qb = $qb->where($field, $value, '=');
        $result = $qb->first();
        if ($result) {
            $className = static::class;
            $id = $result[$this->primaryKey];
            return new $className($id);
        } else {
            return null;
        }
    }

    //toArray
    public function toArray()
    {
        $array = [];
        // Ottieni tutti i nomi delle proprietà
        $properties = (new \ReflectionClass($this))->getProperties();
        foreach ($properties as $property) {
            // Formatta il nome della colonna per utilizzarlo come nome della proprietà
            $propertyName = $this->formatPropertyName($property->getName());
            $_propertyName = str_replace('_', '', ucwords($propertyName, '_'));
            $getter = 'get' . ucfirst($_propertyName);
            if (method_exists($this, $getter)) {
                $array[$propertyName] = $this->$getter();
            } else {
                $array[$propertyName] = $this->$propertyName;
            }
        }
        return $array;
    }

    // format property name
    private function formatPropertyName(string $propertyName): string
    {
        // if is camelCase, convert to snake_case
        if (preg_match('/[A-Z]/', $propertyName)) {
            $propertyName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName));
        }

        return $propertyName;
    }


}