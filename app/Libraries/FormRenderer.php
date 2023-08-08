<?php

namespace App\Libraries;

use Cassandra\Date;

class FormRenderer
{
    private $model;
    private array $formData;
    private array $entityFields;
    private  array $entityValues = [];
    private string $primaryKeyName;

    public function __construct($args)
    {
        $this->model = $args['model'];
        $this->formData = $args['formData'];
        $this->entityFields = $args['entityFields'];
        $this->entityValues = $args['entityValues'];
        $this->primaryKeyName = $args['primaryKeyName'];
    }

    public function renderField(string $fieldName, mixed $fieldType, mixed $fieldValue = ''): string
    {

        // if fielName is 'password' or 'password_confirmation', $fieldType is 'password'
        if (in_array($fieldName, ['password', 'password_confirmation'])) {
            $fieldType = 'password';
        }

        switch ($fieldType) {
            case 'text':
                return $this->renderTextField($fieldName, $fieldValue);
            case 'textarea':
                return $this->renderTextAreaField($fieldName, $fieldValue);
            case 'number':
                return $this->renderNumberField($fieldName, $fieldValue);
            case 'DateTime':
            case 'datetime':
            case 'date':
                return $this->renderDateField($fieldName, $fieldValue);
            case 'time':
                return $this->renderTimeField($fieldName, $fieldValue);
            case 'boolean':
                return $this->renderBooleanField($fieldName, $fieldValue);
            case 'password':
                return $this->renderPasswordField($fieldName, $fieldValue);
            case 'array':
                return $this->renderCheckboxesField($fieldName, $fieldValue);
            default:
                if (is_array($fieldType)) {
                    return $this->renderSelectField($fieldName, $fieldType['options'], $fieldValue);
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
            //$fieldValue = $this->getFieldValue($fieldName, $this->formData, $this->primaryKeyName);
            $fieldValue = $this->entityValues[$fieldName] ?? '';
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

    private function renderSelectField($fieldLabel, mixed $fieldOptions, ?string $fieldValue = ''): string
    {
        $name = $this->getInputName($fieldLabel);
        $options = '';

        foreach ($fieldOptions as $value_arr) {
            // replace ' with \' in $value
            if (is_array($value_arr)) {
                $value = str_replace("'", "", $value_arr['value']);
                $isSelect = $value === $fieldValue ? 'selected' : '';
                $options .= '<option
                    ' . $isSelect . '
                value="' . $value_arr['id'] . '">' . $value . '</option>';
            } else {
                $value = str_replace("'", "", $value_arr);
                $isSelect = $value === $fieldValue ? 'selected' : '';
                $options .= '<option
                    ' . $isSelect . '
                 value="' . $value . '">' . $value . '</option>';
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

    private function renderDateField($fieldLabel, \DateTime|string $fieldValue): string
    {
        $name = $this->getInputName($fieldLabel);
        $fieldValue = $fieldValue ? $fieldValue->format('Y-m-d') : date('Y-m-d');
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

    private function openFormTag($action, $method, $id, $class = 'mb-4 grid grid-cols-2 gap-4'): string
    {
        return '<form action="' . $action . '" method="' . $method . '" id="' . $id . '" class="' . $class . '">';
    }

    private function closeFormTag(): string
    {
        return '</form>';
    }

    private function getSubmitButton($label = 'Submit'): string
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

    private function splitCamelCase($fieldName): array|string|null
    {
        $fieldName = str_replace("_", " ", $fieldName);
        return preg_replace('/(?<!^)[A-Z]/', ' $0', $fieldName);
    }

    private function getInputName($fieldName)
    {
        $fieldName = str_replace(" ", "_", $fieldName);
        return strtolower($fieldName);
    }

    private function getGetterName($fieldName)
    {
        $fieldName = str_replace("_", " ", $fieldName);
        $getter = ucwords($fieldName);
        $getter = str_replace(" ", "", $getter);
        return 'get' . $getter;
    }

    private function renderPasswordField(string $fieldName): string
    {
        $name = $this->getInputName($fieldName);
        $html = '<div class="flex justify-between mb-4 col-span-2">';
        $html .= '<div class="mb-4 w-1/2 mr-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="password" name="' . $name . '" id="' . $name . '">
                </div>';
        $html .= '<div class="mb-4 w-1/2 ml-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '_confirm">' . ucfirst($fieldName) . ' Confirmation:</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="password" name="' . $name . '_confirm" id="' . $name . '_confirm">
                </div>';
        $html .= '</div>';
        return $html;
    }

    private function renderCheckboxesField(string $fieldName, mixed $fieldValues)
    {
        $name = $this->getInputName($fieldName);
        $html = '<div class="mb-4 col-span-2">';
        $html .= '<label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>';
        foreach ($fieldValues as $index => $fieldValue) {
            $html .= '<div class="mb-4 w-1/2 mr-2 flex items-center space-x-2">
                        <input
                        class="" 
                        type="checkbox" name="' . $name . '[]" id="' . $name . '_' . $index . '" value="' . $fieldValue . '">
                        <label
                        class="" 
                        for="' . $name . '_' . $index . '">' . ucfirst($fieldValue) . '</label>
                    </div>';

        }

        $html .= '</div>';
        return $html;
    }

}
