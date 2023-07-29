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

    public static function getEntityFields($model, $relatedFields): array
    {
        $reflectionClass = new ReflectionClass($model);
        $shortClassName = (new ReflectionClass($model))->getShortName();
        $columnTypes = self::getColumnsType($shortClassName);


        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        $primaryKeyName = Helper::getTablePrimaryKeyName($reflectionClass);

        // Escludi i metodi che non devono essere considerati come campi, getIdEntity ad esempio

        $exclude = [
            'getId' . $primaryKeyName,
            'getCreatedAt',
            'getUpdatedAt',
            'getPrimaryKeyName',
            'getAll',
            'getDisplayFieldName',
            'getColumnNames'
        ];
        $fields = [];
        $values = [];
        foreach ($methods as $method) {
            $methodName = $method->getName();

            if (in_array($methodName, $exclude)) {
                continue;
            }

            // Verifica che il metodo sia un getter e non richieda parametri
            if (str_starts_with($methodName, 'get') && $method->getNumberOfParameters() === 0) {
                $propertyName = lcfirst(substr($methodName, 3));
                // add _ to property name if property name is composed by dataOraInizio
                $propertyName = preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName);
                // lowercase property name
                $propertyName = strtolower($propertyName);
                // Controlla se il tipo di colonna è definito nel database
                if (isset($columnTypes[$propertyName])) {
                    // Utilizza il tipo di colonna per determinare il tipo di campo
                    // Puoi definire una mappatura dei tipi di colonna a tipi di campo appropriati
                    $columnType = $columnTypes[$propertyName];

                    // Esempio: se hai una colonna ENUM, crei un campo select con le opzioni possibili
                    if (str_starts_with($columnType, 'enum')) {
                        $options = explode(',', substr($columnType, 5, -1));
                        $fields[$propertyName] = ['type' => 'select', 'options' => $options];
                    } // Esempio: se hai una colonna di tipo datetime, crei un campo di tipo data
                    elseif (str_starts_with($columnType, 'datetime')) {
                        $fields[$propertyName] = 'datetime';
                    } // Esempio: se hai una colonna di tipo date, crei un campo di tipo data
                    elseif (str_starts_with($columnType, 'date')) {
                        $fields[$propertyName] = 'date';
                    } // Esempio: se hai una colonna di tipo time, crei un campo di tipo data
                    elseif (str_starts_with($columnType, 'time')) {
                        $fields[$propertyName] = 'time';
                    } // Esempio: se hai una colonna di tipo boolean, crei un campo di tipo checkbox
                    elseif (str_starts_with($columnType, 'tinyint(1)')) {
                        $fields[$propertyName] = 'boolean';
                    } // Esempio: se hai una colonna di tipo int, crei un campo di tipo number
                    elseif (str_starts_with($columnType, 'int')) {
                        $fields[$propertyName] = 'number';
                    } else {
                        // Altrimenti, imposta il tipo di campo a "text" (puoi personalizzare come desideri)
                        $fields[$propertyName] = 'text';
                    }
                } else {
                    // Se il tipo di colonna non è definito nel database, imposta il tipo di campo a "text" di default
                    $fields[$propertyName] = 'text';
                }
            }
        }

        // Aggiungi qui la gestione dei campi relazionali in modo dinamico

        foreach ($columnTypes as $columnName => $columnType) {
            // Verifica se il nome della colonna termina con '_id' per individuare i campi relazionali
            if ($columnName != 'id_' . strtolower($primaryKeyName)) {
                if (str_starts_with($columnName, 'id_')) {
                    $relatedTableName = ucfirst(Helper::getPluralName(substr($columnName, 3)));
                    $relatedFields[$columnName] = ['type' => 'select', 'options' => self::getRelatedData($relatedTableName)];
                }
            }
        }

        // Unisci i campi relativi alle colonne e i campi relativi ai campi relazionali
        return array_merge($fields, $relatedFields);
    }
}
