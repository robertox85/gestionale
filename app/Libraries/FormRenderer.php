<?php

namespace App\Libraries;

class FormRenderer
{
    private $model;
    private array $formData;
    private array $entityFields;
    private string $primaryKeyName;

    public function __construct($args)
    {
        $this->model = $args['model'];
        $this->formData = $args['formData'];
        $this->entityFields = $args['entityFields'];
        $this->primaryKeyName = $args['primaryKeyName'];
    }
    public function renderField(string $fieldName, mixed $fieldType, mixed $fieldValue = ''): string
    {
        switch ($fieldType) {
            case 'text':
                return $this->renderTextField($fieldName, $fieldValue);
            case 'textarea':
                return $this->renderTextAreaField($fieldName, $fieldValue);
            case 'number':
                return $this->renderNumberField($fieldName, $fieldValue);
            case 'datetime':
            case 'date':
                return $this->renderDateField($fieldName, $fieldValue);
            case 'time':
                return $this->renderTimeField($fieldName, $fieldValue);
            case 'boolean':
                return $this->renderBooleanField($fieldName, $fieldValue);
            default:
                if (is_array($fieldType)) {
                    return $this->renderSelectField($fieldName, $fieldType['options']);
                }
                return $this->renderTextField($fieldName, $fieldValue);
        }
    }

    public function renderForm(): string
    {



        $form = $this->openFormTag($this->formData['action'], 'post', 'edit-form', 'mb-4 grid grid-cols-2 gap-4');
        $form .= '<input type="hidden" name="csrf_token" value="' . $this->formData['csrf_token'] . '">';

        if (isset($this->formData[$this->primaryKeyName])) {
            $form .= '<input type="hidden" name="' . $this->primaryKeyName . '" value="' . $this->formData[$this->primaryKeyName] . '">';
        }

        foreach ($this->entityFields as $fieldName => $fieldType) {
            $fieldValue = $this->getFieldValue($fieldName, $this->formData, $this->primaryKeyName);
            $fieldName = $this->splitCamelCase($fieldName);
            $form .= $this->renderField($fieldName, $fieldType, $fieldValue);
        }

        $form .= $this->getSubmitButton($formData['button_label'] ?? 'Submit');
        $form .= $this->closeFormTag();
        return $form;
    }

    // Aggiungi altri metodi di rendering del form qui, se necessario
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

        foreach ($fieldValue as $value_arr) {
            // replace ' with \' in $value
            if (is_array($value_arr)) {
                $value = str_replace("'", "", $value_arr['value']);
                $options .= '<option value="' . $value_arr['id'] . '">' . $value . '</option>';
            } else {
                $value = str_replace("'", "", $value_arr);
                $options .= '<option value="' . $value . '">' . $value . '</option>';
            }

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

    private function getFieldValue(int|string $fieldName, array $formData, $primaryKeyName)
    {
        return isset($formData[$primaryKeyName]) ? $this->model->{$this->getGetterName($fieldName)}() : $formData[$fieldName] ?? '';
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

    private function getGetterName($fieldName)
    {
        $fieldName = str_replace("_", " ", $fieldName);
        $getter = ucwords($fieldName);
        $getter = str_replace(" ", "", $getter);
        $getter = 'get' . $getter;
        return $getter;
    }

}
