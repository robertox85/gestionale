<?php

namespace App\Libraries;

// DynamicFormComponent.php

// DynamicFormComponent.php

use PDO;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use App\Libraries\Database;

class DynamicFormComponent
{
    private $entityFields;

    public function __construct($model)
    {
        $this->entityFields = $this->getEntityFields($model);
    }

    // Resto del codice invariato
    private function getColumnsType($tableName)
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

    private function getEntityFields($model)
    {
        $reflectionClass = new ReflectionClass($model);
        $columnTypes = $this->getColumnsType($model->getTableName());

        $shortClassName = (new \ReflectionClass($model))->getShortName();
        $tableName = $model::getPluralName($shortClassName);
        $singularLower = strtolower($shortClassName);

        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $exclude = ['getId', 'setId', 'getCreatedAt', 'getUpdatedAt', 'setCreatedAt', 'setUpdatedAt'];
        $fields = [];

        foreach ($methods as $method) {
            $methodName = $method->getName();

            if (in_array($methodName, $exclude) || strpos($methodName, 'getId' . $shortClassName) === 0) {
                continue;
            }

            // Verifica che il metodo sia un getter e non richieda parametri
            if (strpos($methodName, 'get') === 0 && $method->getNumberOfParameters() === 0) {
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
                    if (strpos($columnType, 'enum') === 0) {
                        $options = explode(',', substr($columnType, 5, -1));
                        $fields[$propertyName] = ['type' => 'select', 'options' => $options];
                    } // Esempio: se hai una colonna di tipo datetime, crei un campo di tipo data
                    elseif (strpos($columnType, 'datetime') === 0) {
                        $fields[$propertyName] = 'datetime';
                    } // Esempio: se hai una colonna di tipo date, crei un campo di tipo data
                    elseif (strpos($columnType, 'date') === 0) {
                        $fields[$propertyName] = 'date';
                    } // Esempio: se hai una colonna di tipo time, crei un campo di tipo data
                    elseif (strpos($columnType, 'time') === 0) {
                        $fields[$propertyName] = 'time';
                    } // Esempio: se hai una colonna di tipo boolean, crei un campo di tipo checkbox
                    elseif (strpos($columnType, 'tinyint(1)') === 0) {
                        $fields[$propertyName] = 'boolean';
                    } // Esempio: se hai una colonna di tipo int, crei un campo di tipo number
                    elseif (strpos($columnType, 'int') === 0) {
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

        return $fields;
    }

    // Resto del codice invariato
    public function renderForm(array $formData = [])
    {
        $form = '<form action="' . $formData['action'] . '" method="post" class="mb-4">';
        $form .= '<input type="hidden" name="csrf_token" value="' . $formData['csrf_token'] . '">';
        $form .= '<input type="hidden" name="id" value="' . ($formData['id'] ?? '') . '">';
        $form .= '<div class="mb-4 grid grid-cols-2 gap-4">';
        foreach ($this->entityFields as $fieldName => $fieldType) {
            // replace _ with ' ' in $fieldName
            $fieldName = str_replace("_", " ", $fieldName);
            // separate $fieldName written DataOraInizio to Data Ora Inizio
            $fieldName = preg_replace('/(?<!^)[A-Z]/', ' $0', $fieldName);

            $form .= $this->renderField($fieldName, $fieldType, $formData);
        }
        $form .= '</div>';

        $form .= '<button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">';
        $form .= $formData['button_label'] ?? 'Submit';
        $form .= '</button>';
        $form .= '</form>';

        return $form;
    }

    private function renderField($fieldName, $fieldType, $formData)
    {
        $fieldValue = $formData[$fieldName] ?? '';


        switch ($fieldType) {
            case 'text':
                return $this->renderTextField($fieldName, $fieldValue);
            case 'number':
                return $this->renderNumberField($fieldName, $fieldValue);
            case 'datetime':
            case 'date':
                return $this->renderDateField($fieldName, $fieldValue);
            case 'time':
                return $this->renderTimeField($fieldName, $fieldValue);
            case 'boolean':
                return $this->renderBooleanField($fieldName, $fieldValue);
            case 'textarea':
                return $this->renderTextAreaField($fieldName, $fieldValue);
            // Aggiungi altri tipi di campo qui se necessario


            default:
                // Campo sconosciuto, puoi gestire l'errore o generare un campo di default
                if (is_array($fieldType)) {
                    return $this->renderSelectField($fieldName, $fieldType['options']);
                } else {
                    return $this->renderTextField($fieldName, $fieldValue);
                }
        }
    }

    private function renderTextField($fieldName, $fieldValue)
    {
        $name = str_replace(" ", "_", $fieldName);
        $name = strtolower($name);

        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="text" name="' . $name . '" id="' . $name . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderTextAreaField($fieldName, $fieldValue)
    {
        $name = str_replace(" ", "_", $fieldName);
        $name = strtolower($name);
        return '<div class="mb-4 col-span-2">
                    <label 
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>
                    <textarea
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    name="' . $name . '" id="' . $name . '">' . $fieldValue . '</textarea>
                </div>';
    }

    private function renderSelectField($fieldName, mixed $fieldValue)
    {
        $name = str_replace(" ", "_", $fieldName);
        $name = strtolower($name);
        $options = '';

        foreach ($fieldValue as $key => $value) {
            // replace ' with \' in $value
            $value = str_replace("'", "", $value);
            $options .= '<option value="' . $value . '">' . $value . '</option>';
        }

        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>
                    <select
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    name="' . $name . '" id="' . $name . '">' . $options . '</select>
                </div>';
    }

    private function renderDateField($fieldName, mixed $fieldValue)
    {
        $name = str_replace(" ", "_", $fieldName);
        $name = strtolower($name);
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="date" name="' . $name . '" id="' . $name . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderBooleanField($fieldName, mixed $fieldValue)
    {
        $name = str_replace(" ", "_", $fieldName);
        $name = strtolower($name);
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="checkbox" name="' . $name . '" id="' . $fieldName . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderTimeField($fieldName, mixed $fieldValue)
    {
        $name = str_replace(" ", "_", $fieldName);
        $name = strtolower($name);
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="time" name="' . $name . '" id="' . $name . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderNumberField($fieldName, mixed $fieldValue)
    {
        $name = str_replace(" ", "_", $fieldName);
        $name = strtolower($name);
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="number"
                    min="0" 
                    name="' . $name . '" id="' . $name . '" value="' . $fieldValue . '">
                </div>';
    }
}

