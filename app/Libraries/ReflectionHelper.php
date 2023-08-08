<?php

namespace App\Libraries;

use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class ReflectionHelper
{
    public static function getColumnsType($tableName): array
    {
        $columnTypes = [];

        // Query per ottenere i dettagli delle colonne della tabella
        $sql = "DESCRIBE $tableName";

        $db = Database::getInstance();

        // Esegui la query
        $stmt = $db->prepare($sql);
        $stmt->execute();


        // Ottieni i dettagli delle colonne
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columnName = $row['Field'];
            $columnType = $row['Type'];

            // Aggiungi il tipo di colonna all'array $columnTypes
            $columnTypes[$columnName] = $columnType;
        }

        return $columnTypes;
    }

    public static function getRelatedData(string $relatedTableName): array
    {
        // Ottieni il nome del modello relazionale
        $relatedModelName = ucfirst($relatedTableName);

        // Ottieni il nome della classe del modello relazionale
        $relatedModelClassName = "App\\Models\\$relatedModelName";

        // Crea un'istanza del modello relazionale
        $relatedModel = new $relatedModelClassName();

        $keyField = $relatedModel->getPrimaryKeyName();
        $valueField = $relatedModel->getDisplayFieldName(); // Definisci il nome del campo da utilizzare come valore per l'opzione select
        $relatedData = $relatedModel->getAll(); // Implementa il metodo per ottenere tutti i record dalla tabella relazionale

        $options = [];
        foreach ($relatedData as $relatedRecord) {
            $id = $relatedRecord[$keyField];
            $options[$relatedRecord[$keyField]] = [
                'id' => $id,
                'value' => $relatedRecord[$valueField]
            ];
        }

        return $options;
    }

    /**
     * @throws ReflectionException
     */
    public static function getEntityFields($model, $relatedFields): array
    {
        $reflectionClass = new ReflectionClass($model);
        $shortClassName = $reflectionClass->getShortName();
        $columnTypes = self::getColumnsType($shortClassName);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $primaryKeyName = Helper::getTablePrimaryKeyName($reflectionClass);

        // get properties from model
        $properties = self::getPropertiesFromModel($reflectionClass);

        // $fields = self::extractFieldsFromMethods($methods, $columnTypes, $primaryKeyName);
        $relatedFields = self::addRelatedFields($columnTypes, $primaryKeyName, $relatedFields);

        return array_merge($properties, $relatedFields);
    }

    private static function getPropertiesFromModel($reflectionClass)
    {
        $primaryKeyName = Helper::getTablePrimaryKeyName($reflectionClass);
        $excludeList = [
            'id',
            'created_at',
            'updated_at',
            'id_' . strtolower($primaryKeyName),
        ];
        $properties = $reflectionClass->getProperties();
        $propertiesTypes = [];
        foreach ($properties as $property) {
            // get property's type declaration (if any)
            $type = $property->getType();
            if ($type !== null) {
                $propertiesTypes[$property->getName()] = $type->getName();
            }
        }
        return self::excludeProperties($propertiesTypes, $excludeList);
    }

    private static function excludeProperties($properties, $excludeList)
    {
        $filteredProperties = [];
        foreach ($properties as $name => $value) {
            if (!self::shouldBeExcluded($name, $excludeList)) {
                $filteredProperties[$name] = $value;
            }
        }
        return $filteredProperties;
    }

    private static function shouldBeExcluded($methodName, $excludeList): bool
    {
        return in_array($methodName, $excludeList);
    }

    private static function addRelatedFields($columnTypes, $primaryKeyName, $relatedFields): array
    {
        foreach ($columnTypes as $columnName => $columnType) {
            if ($columnName != 'id_' . strtolower($primaryKeyName) && str_starts_with($columnName, 'id_')) {
                $relatedTableName = ucfirst(Helper::getPluralName(substr($columnName, 3)));
                $relatedFields[$columnName] = ['type' => 'select', 'options' => self::getRelatedData($relatedTableName)];
            }
        }
        return $relatedFields;
    }

    /* TODO: per ora commento, verificare che serva o meno. È stata sostituita dalla funzione getPropertiesFromModel
    private static function extractFieldsFromMethods($methods, $columnTypes, $primaryKeyName): array
    {
        $exclude = [
            'getId',
            'getId' . $primaryKeyName,
            'getCreatedAt',
            'getUpdatedAt',
            'getPrimaryKeyName',
            'getAll',
            'getDisplayFieldName',
            'getColumnNames'
        ];
        $fields = [];
        foreach ($methods as $method) {
            if (!self::shouldBeExcluded($method->getName(), $exclude)) {
                $fields = array_merge($fields, self::getFieldFromMethod($method, $columnTypes));
            }
        }
        return $fields;
    }

    private static function getFieldFromMethod($method, $columnTypes): array
    {
        $fields = [];
        if (str_starts_with($method->getName(), 'get') && $method->getNumberOfParameters() === 0) {
            $propertyName = self::formatPropertyName($method->getName());
            if (isset($columnTypes[$propertyName])) {
                $fields[$propertyName] = self::mapColumnTypeToField($columnTypes[$propertyName]);
            } else {
                $fields[$propertyName] = 'text';
            }
        }
        return $fields;
    }
    private static function formatPropertyName($methodName): string
    {
        $propertyName = lcfirst(substr($methodName, 3));
        $propertyName = preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName);
        return strtolower($propertyName);
    }
    private static function mapColumnTypeToField($columnType): array|string
    {
        if (str_starts_with($columnType, 'enum')) {
            $options = explode(',', substr($columnType, 5, -1));
            return ['type' => 'select', 'options' => $options];
        }
        // Add more conditions here...
        // date
        // datetime

        if (str_starts_with($columnType, 'int')) {
            return 'number';
        }

        if (str_starts_with($columnType, 'varchar')) {
            return 'text';
        }

        if (str_starts_with($columnType, 'date')) {
            return 'date';
        }

        // time
        // timestamp
        if (str_starts_with($columnType, 'time')) {
            return 'time';
        }

        return 'text';  // default value
    }
    */
    public static function getEntityValues($model)
    {
        $reflectionClass = new ReflectionClass($model);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $values = [];
        foreach ($methods as $method) {
            if (str_starts_with($method->getName(), 'get') && $method->getNumberOfParameters() === 0) {
                $propertyName = self::formatPropertyName($method->getName());
                // skip if is id_* or _at
                if (str_starts_with($propertyName, 'id_') || str_ends_with($propertyName, '_at') || $propertyName == 'id') {
                    continue;
                } else{
                    $values[$propertyName] = $method->invoke($model);
                }
            }
        }
        return $values;
    }

    private static function formatPropertyName(string $getName)
    {
        $propertyName = lcfirst(substr($getName, 3));
        $propertyName = preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName);
        return strtolower($propertyName);
    }

}
