<?php

namespace App\Libraries;

// DynamicFormComponent.php

// DynamicFormComponent.php

use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;


class DynamicFormComponent
{
    private array $entityFields;
    private $model;


    /**
     * @throws ReflectionException
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->entityFields = $this->getEntityFields($model);
    }

    // Resto del codice invariato
    private function getColumnsType($tableName): array
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

    function get_singular_form($word)
    {

        $plural_to_singular = array(
            '/(che)$/' => 'ca',     // esempi: bacheche -> bacheca
            '/(e)$/' => 'a',        // esempi: sale -> sala
            // Aggiungi altre regole specifiche per casi particolari
        );

        foreach ($plural_to_singular as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                $word = preg_replace($rule, $replacement, $word);
                break; // Esci dal loop dopo la prima sostituzione
            }
        }

        return $word;
    }


    /**
     * @throws ReflectionException
     */
    private function getEntityFields($model): array
    {
        $reflectionClass = new ReflectionClass($model);
        $shortClassName = (new ReflectionClass($model))->getShortName();
        $columnTypes = $this->getColumnsType($shortClassName);


        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        $primaryKeyName = Helper::getTablePrimaryKeyName($reflectionClass);

        // Escludi i metodi che non devono essere considerati come campi, getIdEntity ad esempio
        $getPrimaryKeyMethod = 'getId' . $primaryKeyName;

        $exclude = [$getPrimaryKeyMethod, 'getCreatedAt', 'getUpdatedAt', 'getPrimaryKeyName'];
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

        return $fields;
    }

    // Resto del codice invariato
    public function renderForm(array $formData = []): string
    {
        $form = '<form action="' . $formData['action'] . '" method="post" class="mb-4">';
        $form .= '<input type="hidden" name="csrf_token" value="' . $formData['csrf_token'] . '">';

        $form .= '<div class="mb-4 grid grid-cols-2 gap-4">';
        foreach ($this->entityFields as $fieldName => $fieldType) {
            // replace _ with ' ' in $fieldName
            $fieldName = str_replace("_", " ", $fieldName);
            // separate $fieldName written DataOraInizio to Data Ora Inizio
            $fieldName = preg_replace('/(?<!^)[A-Z]/', ' $0', $fieldName);

            $form .= $this->renderField($fieldName, $fieldType);
        }
        $form .= '</div>';

        $form .= '<button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">';
        $form .= $formData['button_label'] ?? 'Submit';
        $form .= '</button>';
        $form .= '</form>';

        return $form;
    }

    private function renderField($fieldName, $fieldType, $fieldValue = ''): string
    {
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

    private function renderTextField($fieldLabel, $fieldValue): string
    {
        $name = $this->getInputName($fieldLabel);
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldLabel) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="text" name="' . $name . '" id="' . $name . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderTextAreaField($fieldLabel, $fieldValue): string
    {
        $name = $this->getInputName($fieldLabel);
        return '<div class="mb-4 col-span-2">
                    <label 
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="' . $name . '">' . ucfirst($fieldLabel) . ':</label>
                    <textarea
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    name="' . $name . '" id="' . $name . '">' . $fieldValue . '</textarea>
                </div>';
    }

    private function renderSelectField($fieldLabel, mixed $fieldValue): string
    {
        $name = $this->getInputName($fieldLabel);
        $options = '';

        foreach ($fieldValue as $value) {
            // replace ' with \' in $value
            $value = str_replace("'", "", $value);
            $options .= '<option value="' . $value . '">' . $value . '</option>';
        }

        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldLabel) . ':</label>
                    <select
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    name="' . $name . '" id="' . $name . '">' . $options . '</select>
                </div>';
    }

    private function renderDateField($fieldLabel, mixed $fieldValue): string
    {
        $name = $this->getInputName($fieldLabel);
        // turn $fieldValue from 2021-09-01 00:00:00 to 2021-09-01, if $fieldValue is empty, set it to today
        $fieldValue = $fieldValue ? substr($fieldValue, 0, 10) : date("Y-m-d");
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldLabel) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="date" name="' . $name . '" id="' . $name . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderBooleanField($fieldLabel, mixed $fieldValue): string
    {
        $name = $this->getInputName($fieldLabel);
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldLabel) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="checkbox" name="' . $name . '" id="' . $name . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderTimeField($fieldLabel, mixed $fieldValue): string
    {
        $name = $this->getInputName($fieldLabel);
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldLabel) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="time" name="' . $name . '" id="' . $name . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderNumberField($fieldLabel, mixed $fieldValue): string
    {
        $name = $this->getInputName($fieldLabel);
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldLabel) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="number"
                    min="0" 
                    name="' . $name . '" id="' . $name . '" value="' . $fieldValue . '">
                </div>';
    }

    public function renderEditForm(array $formData): string
    {
        $primaryKeyName = $this->getPrimaryKeyName();
        $form = $this->openFormTag($formData['action'], 'post', 'edit-form', 'mb-4 grid grid-cols-2 gap-4');

        $form .= '<input type="hidden" name="csrf_token" value="' . $formData['csrf_token'] . '">';
        if (isset($formData[$primaryKeyName])) {
            $form .= '<input type="hidden" name="' . $primaryKeyName . '" value="' . $formData[$primaryKeyName] . '">';
        }


        foreach ($this->entityFields as $fieldName => $fieldType) {
            $fieldValue = $this->model->{$this->getGetterName($fieldName)}();
            $fieldName = $this->splitCamelCase($fieldName);
            $form .= $this->renderField($fieldName, $fieldType, $fieldValue);
        }

        $form .= $this->getSubmitButton($formData['button_label'] ?? 'Submit');
        $form .= $this->closeFormTag();

        return $form;
    }

    private function getGetterName($fieldName)
    {
        $fieldName = str_replace("_", " ", $fieldName);
        $getter = ucwords($fieldName);
        $getter = str_replace(" ", "", $getter);
        $getter = 'get' . $getter;
        return $getter;
    }

    private function splitCamelCase($fieldName)
    {
        $fieldName = str_replace("_", " ", $fieldName);
        $fieldName = preg_replace('/(?<!^)[A-Z]/', ' $0', $fieldName);
        return $fieldName;
    }

    private function getInputName($fieldName)
    {
        $fieldName = str_replace(" ", "_", $fieldName);
        $fieldName = strtolower($fieldName);
        return $fieldName;
    }

    private function openFormTag($action, $method, $id, $class = 'mb-4 grid grid-cols-2 gap-4')
    {
        $form = '<form action="' . $action . '" method="' . $method . '" id="' . $id . '" class="' . $class . '">';
        return $form;
    }

    private function closeFormTag()
    {
        $form = '</form>';
        return $form;
    }

    private function getSubmitButton($label = 'Submit')
    {
        $button = '<button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">';
        $button .= $label;
        $button .= '</button>';
        return $button;
    }

    private function getPrimaryKeyName()
    {
        return $this->model->getPrimaryKeyName();
    }

}

