<?php

namespace App\Models;

use App\Libraries\Database;
use App\Libraries\Helper;
use App\Libraries\QueryBuilder;

class BaseModel
{
    protected $db;
    protected $tableName;
    protected $primaryKey;

    public function __construct($id = null)
    {
        $this->db = Database::getInstance();
        $this->tableName = $this->getShortClassName();

        $this->primaryKey = 'id_' . strtolower(Helper::getTablePrimaryKeyName($this->getShortClassName()));

        if ($id !== null) {
            $this->load([':id' => $id]);
        }
    }


    // Load by id
    public function load($params = [])
    {
        $qb = new QueryBuilder($this->db);
        $qb = $qb->setTable($this->tableName);
        $qb = $qb->select('*');
        $qb = $qb->where($this->primaryKey, $params[':id'], '=');
        $result = $qb->get();

        if (isset($result[0])) {
            $this->setProperties($result[0]);
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
        $qb = $qb->select('*');
        $qb = $qb->where($this->primaryKey, $id, '=');
        // check if the record exists
        $result = $qb->get();
        if (isset($result[0])) {
            $qb->reset();
            $qb->where($this->primaryKey, $id, '=');
            $qb->delete();
            return;
        }

        throw new \Exception('Record not found');
    }

    public function store(array $post): int|bool
    {
        $primaryKey = (new static())->getPrimaryKeyName();
        // Se è presente l'id_notifica, eseguire l'aggiornamento
        if (isset($post[$primaryKey])) {
            return self::update($post);
        } else {
            // Altrimenti esegui l'inserimento
            return self::create($post);
        }
    }



    public static function create(array $post)
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

    // setProperties
    public static function find($id)
    {
        $qb = new QueryBuilder(Database::getInstance());
        $tableName = (new \ReflectionClass(static::class))->getShortName();
        $primaryKeyName = (new static())->getPrimaryKeyName();
        $qb = $qb->setTable($tableName);
        $qb = $qb->select('*');
        $qb = $qb->where($primaryKeyName, $id, '=');
        $result = $qb->get();
        if (isset($result[0])) {
            $className = static::class;
            $id = $result[0][$primaryKeyName];
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

    private function getId()
    {
        $getter = 'getId' . ucfirst(Helper::getTablePrimaryKeyName($this->getShortClassName()));
        return $this->$getter();
    }

    private function setProperties(mixed $int)
    {
        foreach ($int as $key => $value) {
            // Formatta il nome della colonna per utilizzarlo come nome della proprietà
            $propertyName = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $setter = 'set' . ucfirst($propertyName);
            $this->$setter($value);
        }
    }

    public function getPrimaryKeyName()
    {
        return $this->primaryKey;
    }
}