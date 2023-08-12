<?php

namespace App\Models;

use App\Attributes\ForeignKey;
use App\Attributes\LabelColumn;
use App\Attributes\PrimaryKey;
use App\Attributes\Required;
use App\Libraries\Database;

use App\Libraries\QueryBuilder;
use Exception;

class BaseModel
{
    private ?string $shortClassName = null;
    private Database $db;
    private string $primaryKey;

    private QueryBuilder $qb;

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new Exception("Proprietà {$name} non esiste.");
    }

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            throw new Exception("Proprietà {$name} non esiste.");
        }
    }

    public function __construct($id = null)
    {
        // Inizializza tutte le proprietà nelle classi figlie
        $this->initializeProperties();

        $this->db = Database::getInstance();
        $this->qb = $this->initQueryBuilder();
        $this->primaryKey = $this->getEntityProperty(PrimaryKey::class);

        if ($id !== null) {
            $this->load([':id' => $id]);
        }
    }

    private function initializeProperties()
    {
        $reflectionClass = new \ReflectionClass(static::class);
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $this->$propertyName = $this->getDefaultPropertyValue($property);
        }
    }

    private function initQueryBuilder(): QueryBuilder
    {
        $qb = new QueryBuilder($this->db);
        return $qb->setTable($this->getShortClassName());
    }

    public function getShortClassName(): string
    {
        if ($this->shortClassName === null) {
            $this->shortClassName = (new \ReflectionClass($this))->getShortName();
        }
        return $this->shortClassName;
    }

    public function getId()
    {
        return $this->{$this->primaryKey};
    }

    public function load(array $params)
    {
        $qb = $this->qb;
        $qb = $qb->select('*');
        $qb = $qb->where($this->primaryKey, $params[':id'], '=');
        $result = $qb->first();
        if ($result) {
            $this->setProperties($result);
        }
        return false;
    }

    public function delete(int $id = null): \PDOStatement|bool
    {
        if ($id === null) {
            $id = $this->getId();
        }
        $qb = $this->qb;
        $qb = $qb->where($this->primaryKey, $id, '=');
        return $qb->delete();
    }

    public function bulkDelete(array $ids): \PDOStatement|bool
    {
        $qb = $this->qb;
        $qb = $qb->whereIn($this->primaryKey, $ids);
        return $qb->delete();
    }

    public function store(): int|bool
    {
        $data = $this->getModelAttributes();
        $data = $this->formatAndCastData($data);

        $this->setProperties($data);
        if (isset($data[$this->primaryKey]) && $data[$this->primaryKey] !== 0) {
            return $this->update();
        } else {
            return $this->create();
        }
    }

    public function create(): bool|string
    {
        $post = $this->getModelAttributes();
        $qb = $this->qb;
        $qb->insert($post);
        return $this->db->lastInsertId();
    }

    public function update(): bool
    {
        $post = $this->getModelAttributes();
        $qb = $this->qb;
        $qb->where($this->primaryKey, $post[$this->primaryKey], '=');
        unset($post[$this->primaryKey]);
        return $qb->update($post);
    }

    public static function get($id): ?BaseModel
    {
        $instance = new static();
        $qb = new QueryBuilder(Database::getInstance());
        $tableName = $instance->getShortClassName();
        $qb = $qb->setTable($tableName);
        $qb = $qb->select('*');
        $qb = $qb->where($instance->primaryKey, $id, '=');
        $result = $qb->first();
        if ($result) {
            return new static($id);
        }
        return null;
    }

    public function setProperties(array $properties): void
    {
        $messages = [];
        foreach ($properties as $key => $value) {
            $key = strtolower($key);
            if (property_exists($this, $key)) {
                $reflectionProperty = new \ReflectionProperty($this, $key);
                $isRequired = $this->getPropertyAttribute($reflectionProperty, Required::class);
                if ($isRequired && empty($value)) {
                    $messages[] = "Required: Il campo {$key} è obbligatorio.";
                }
                // setter
                $setter = 'set' . ucfirst($key);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                } else {
                    $this->$key = $this->castValue($key, $value);
                }
            }
        }

        if (count($messages) > 0) {
            throw new Exception(implode('<br>', $messages));
        }
    }

    private function castValue(string $propertyName, mixed $value)
    {
        $type = $this->getPropertyType($propertyName);
        switch ($type) {
            case 'int':
                return (int)$value;
            case 'string':
                return (string)$value;
            case 'bool':
                return (bool)$value;
            case 'array':
                return (array)$value;
            case 'float':
                return (float)$value;
            case 'DateTime':
                if (is_string($value))
                    return new \DateTime($value);
                else
                    return $value->format('Y-m-d H:i:s');
            default:
                return $value;
        }
    }

    private function getPropertyType(string $propertyName): string
    {
        $reflection = new \ReflectionClass(static::class);
        $property = $reflection->getProperty($propertyName);
        $type = $property->getType();
        if ($type !== null) {
            $type = $type->getName();
        }
        return $type;
    }

    private function formatPropertyName(string $propertyName): string
    {
        // if is camelCase, convert to snake_case
        if (preg_match('/[A-Z]/', $propertyName)) {
            $propertyName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName));
        }

        return $propertyName;
    }

    // TODO: usare questa funzione per prendere gli attributi, al posto di getDateFormat e getDropDownProperty, ecc...
    public function getPropertyAttribute($reflectionProperty, $attributeClass)
    {
        $attributes = $reflectionProperty->getAttributes($attributeClass);
        if ($attributes && count($attributes) > 0) {
            return $attributes[0]->newInstance();
        }
        return null;
    }

    public function getEntityProperty($attributeClass, $reflectionClass = null)
    {
        if ($reflectionClass === null) {
            $reflectionClass = new \ReflectionClass(static::class);
        }
        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes($attributeClass);
            if ($attributes && count($attributes) > 0) {
                return $property->getName();
            }
        }
        return null;
    }

    public function getLabelColumn($reflectionClass = null): ?string
    {
        if ($reflectionClass === null) {
            $reflectionClass = new \ReflectionClass(static::class);
        }
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->getAttributes(LabelColumn::class)) {
                return $property->getName();
            }
        }
        return null;
    }

    public function getForeignKeys($reflectionClass = null): array
    {
        if ($reflectionClass === null) {
            $reflectionClass = new \ReflectionClass(static::class);
        }
        $foreignKeys = [];
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->getAttributes(ForeignKey::class)) {
                $foreignKeys[] = $property->getName();
            }
        }
        return $foreignKeys;
    }

    public function getVisibleProperties($reflectionClass = null): array
    {
        if ($reflectionClass === null) {
            $reflectionClass = new \ReflectionClass(static::class);
        }

        $properties = $reflectionClass->getProperties();

        $visibleProperties = [];
        foreach ($properties as $property) {
            if (!$property->getAttributes(\App\Attributes\Hidden::class)) {
                // get type of property
                $type = $property->getType();
                $typeName = $type ? $type->getName() : 'mixed';
                $visibleProperties[$property->getName()] = $typeName;
            }
        }
        return $visibleProperties;
    }

    public function getDateFormat(\ReflectionProperty $property): ?string
    {
        $attributes = $property->getAttributes(\App\Attributes\DateFormat::class);
        if ($attributes && count($attributes) > 0) {
            /** @var \App\Attributes\DateFormat $dateFormat */
            $dateFormat = $attributes[0]->newInstance();

            // return date, datetime or time based on format
            if ($dateFormat->format === 'd/m/Y') {
                return 'date';
            } else if ($dateFormat->format === 'd/m/Y H:i:s') {
                return 'datetime';
            } else if ($dateFormat->format === 'H:i:s') {
                return 'time';
            }

            // return default format
            return $dateFormat->format;


        }
        return null;
    }

    public function getDropDownProperty(\ReflectionProperty $property)
    {
        $attributes = $property->getAttributes(\App\Attributes\DropDown::class);
        if ($attributes && count($attributes) > 0) {
            /** @var \App\Attributes\DropDown $dropDown */
            $dropDown = $attributes[0]->newInstance();
            return $dropDown;
        }
        return null;
    }


    public function getRequiredProperty(\ReflectionProperty $property)
    {
        $attributes = $property->getAttributes(\App\Attributes\Required::class);
        if ($attributes && count($attributes) > 0) {
            /** @var \App\Attributes\Required $required */
            $required = $attributes[0]->newInstance();
            return $required;
        }
        return null;
    }

    public function getByField($field, $value)
    {
        $qb = $this->qb;
        $qb = $qb->select('*');
        $qb = $qb->where($field, $value, '=');
        $result = $qb->first();
        if ($result) {
            $className = static::class;
            $id = $result[$this->primaryKey];
            return new $className($id);
        }

        return null;
    }

    public function getModelAttributes(): array
    {
        $array = [];
        $reflectionClass = (new \ReflectionClass($this));
        $properties = $this->getVisibleProperties($reflectionClass);
        $primaryKey = $this->getEntityProperty(PrimaryKey::class, $reflectionClass);
        $array[$primaryKey] = $this->$primaryKey;
        foreach ($properties as $key => $value) {
            $array[$key] = $this->$key;
        }
        return $array;
    }

    public function getAll(): array
    {
        $qb = $this->qb;
        $qb = $qb->select('*');
        $qb = $qb->setAlias($this->primaryKey, 'id');
        return $qb->get();
    }


    protected function formatAndCastData(array $data): array
    {
        $formattedAndCastedPost = [];

        foreach ($data as $key => $value) {
            $formattedKey = $this->formatPropertyName($key);
            $formattedAndCastedPost[$formattedKey] = $this->castValue($key, $value);
        }

        return $formattedAndCastedPost;
    }

    protected function getDefaultPropertyValue(\ReflectionProperty $property)
    {
        if ($property->isInitialized($this)) {
            return $property->getValue($this);
        }

        return match ($this->getPropertyType($property->getName())) {
            'int' => 0,
            'string' => '',
            'bool' => false,
            'array' => [],
            'float' => 0.0,
            'DateTime' => null,
            default => null,
        };
    }

}