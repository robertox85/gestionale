<?php

namespace App\Libraries;

use App\Attributes\PrimaryKey;
use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use stdClass;

class ReflectionHelper
{

    public static function getRelatedData(string $relatedTableName): array
    {
        // Ottieni il nome del modello relazionale
        $relatedModelName = ucfirst($relatedTableName);

        // Ottieni il nome della classe del modello relazionale
        $relatedModelClassName = "App\\Models\\$relatedModelName";

        // Crea un'istanza del modello relazionale. Svuoto il costruttore per evitare errori
        $relatedModel = new $relatedModelClassName();

        //$keyField = $relatedModel->getEntityPrimaryKey();
        $keyField = $relatedModel->getEntityProperty(PrimaryKey::class); // Definisci il nome del campo da utilizzare come chiave per l'opzione select
        $valueField = $relatedModel->getLabelColumn(); // Definisci il nome del campo da utilizzare come valore per l'opzione select
        $relatedData = $relatedModel->getAll(); // Implementa il metodo per ottenere tutti i record dalla tabella relazionale

        $options = [];
        foreach ($relatedData as $relatedRecord) {

            if (!isset($relatedRecord[$keyField])) continue; // Se il campo chiave non è presente, salta il record

            $id = $relatedRecord[$keyField];
            $options[$relatedRecord[$keyField]] = [
                'id' => $id,
                'value' => $relatedRecord[$valueField]
            ];
        }

        return $options;
    }

    public static function getEntities($model)
    {
        $properties = $model->getVisibleProperties();
        $foreignKeysValues = (new ReflectionHelper)->getForeignKeysValue($model);
        $propertiesValues = (new ReflectionHelper)->getPropertiesValues($model, $properties);

        return array_merge($foreignKeysValues, $propertiesValues);
    }

    private function getForeignKeysValue($model)
    {
        $foreignKeys = $model->getForeignKeys();
        $reflectionClass = new ReflectionClass($model);
        $tableName = $reflectionClass->getShortName();
        $values = [];
        foreach ($foreignKeys as $foreignKey) {
            $relatedTableName = Helper::getTableNameFromForeignKey($tableName, $foreignKey);
            $values[$foreignKey] = ['type' => 'select', 'options' => self::getRelatedData($relatedTableName)];
        }
        return $values;
    }

    /**
     * Get the values of the model's properties.
     *
     * @param object $model The model object.
     * @param array $properties The properties of the model.
     * @return array The values of the model's properties.
     */
    private function getPropertiesValues($model, $properties)
    {
        $values = [];
        $properties = array_diff_key($properties, array_flip($model->getForeignKeys()));

        foreach ($properties as $propertyName => $propertyType) {
            if (!property_exists($model, $propertyName)) {
                continue;
            }

            $reflectionProperty = new \ReflectionProperty($model, $propertyName);

            if ($propertyType == 'array') {
                $values[$propertyName] = $this->handleArrayProperty($model, $reflectionProperty, $propertyName);
            } else {
                $values[$propertyName] = $this->handleOtherProperty($model, $reflectionProperty, $propertyType, $propertyName);
            }
        }

        return $values;
    }


    private function handleArrayProperty($model, $reflectionProperty, $propertyName)
    {
        $dropDownMethod = $model->getDropDownProperty($reflectionProperty);
        $defaultModel = new $model();

        return [
            'type' => ($dropDownMethod != null) ? 'select' : 'checkboxes',
            'options' => ($dropDownMethod != null) ? $defaultModel->$propertyName : $model->$propertyName,
            'value' => ($model->$propertyName != null) ? $model->$propertyName[0] : null
        ];
    }

    private function handleOtherProperty($model, $reflectionProperty, $propertyType, $propertyName)
    {
        $dateFormat = $model->getDateFormat($reflectionProperty);
        return [
            'type' => ($dateFormat !== null) ? $dateFormat : $propertyType,
            'value' => $model->$propertyName
        ];
    }

}
