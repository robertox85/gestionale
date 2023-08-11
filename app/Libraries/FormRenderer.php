<?php

namespace App\Libraries;


class FormRenderer
{

    public function render($args): string
    {
        $form = $this->openFormTag($args['action'], 'post', 'edit-form', 'mb-4 grid grid-cols-2 gap-4');
        $form .= '<input type="hidden" name="csrf_token" value="' . $args['csrf_token'] . '">';

        if (isset($args['primary_key'])) {
            $form .= '<input type="hidden" name="' . $args['primary_key']['name'] . '" value="' . $args['primary_key']['value'] . '">';
        }

        foreach ($args['entities'] as $key => $field) {
            $field['name'] = $this->getInputName($key);
            $form .= $this->renderField($field);
        }

        $form .= $this->getSubmitButton($args['button_label'] ?? 'Submit');
        $form .= $this->closeFormTag();
        return $form;
    }

    public function renderField($field): string
    {
        $name = $field['name'];
        $type = $field['type'];
        $options = [];
        if ($type == 'select' || $type == 'checkboxes') {
            $options = $field['options'];
        }
        $value = $field['value'] ?? '';
        // if fielName is 'password' or 'password_confirmation', $fieldType is 'password'
        if (in_array(strtolower($name), ['password', 'password_confirmation'])) {
            $type = 'password';
        }

        return match ($type) {
            'textarea' => $this->renderTextAreaField($name, $value),
            'number', 'int' => $this->renderNumberField($name, $value),
            'datetime' => $this->renderDateTimeField($name, $value),
            'date' => $this->renderDateField($name, $value),
            'time' => $this->renderTimeField($name, $value),
            'boolean' => $this->renderBooleanField($name, $value),
            'password' => $this->renderPasswordField($name, $value),
            'checkboxes' => $this->renderCheckboxesField($name, $options),
            'select' => $this->renderSelectField($name, $options, $value),
            default => $this->renderTextField($name, $value),
        };
    }

    // Aggiungi altri metodi di rendering del form qui, se necessario
    private function renderTextField($name, $fieldValue): string
    {
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . strtolower(str_replace(' ', '_', $name)) . '">' . ucfirst($name) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="text" name="' . strtolower(str_replace(' ', '_', $name)) . '" id="' . strtolower(str_replace(' ', '_', $name)) . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderTextAreaField($name, $fieldValue): string
    {
        return '<div class="mb-4 col-span-2">
                    <label 
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="' . strtolower(str_replace(' ', '_', $name)) . '">' . ucfirst($name) . ':</label>
                    <textarea
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    name="' . strtolower(str_replace(' ', '_', $name)) . '" id="' . strtolower(str_replace(' ', '_', $name)) . '">' . $fieldValue . '</textarea>
                </div>';
    }

    private function renderSelectField($name, mixed $fieldOptions, $fieldValue = ''): string
    {

        $options = '';

        foreach ($fieldOptions as $value_arr) {
            // replace ' with \' in $value
            if (is_array($value_arr)) {
                $value = $value_arr['value'];
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
                    for="' . strtolower(str_replace(' ', '_', $name)) . '">' . ucfirst($name) . ':</label>
                    <select
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    name="' . strtolower(str_replace(' ', '_', $name)) . '" id="' . strtolower(str_replace(' ', '_', $name)) . '">' . $options . '</select>
                </div>';
    }

    private function renderDateField($name, \DateTime|string $fieldValue): string
    {

        if ($fieldValue === '') {
            // now in local timezone MYSQL. There is a 2 hour difference between the MySQL server and the local timezone
            $fieldValue = (new \DateTime('Europe/Rome'))->format('Y-m-d');
        }

        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . strtolower(str_replace(' ', '_', $name)) . '">' . ucfirst($name) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="date" name="' . strtolower(str_replace(' ', '_', $name)) . '" id="' . strtolower(str_replace(' ', '_', $name)) . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderBooleanField($name, mixed $fieldValue): string
    {

        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . strtolower(str_replace(' ', '_', $name)) . '">' . ucfirst($name) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="checkbox" name="' . strtolower(str_replace(' ', '_', $name)) . '" id="' . strtolower(str_replace(' ', '_', $name)) . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderTimeField($name, mixed $fieldValue): string
    {
        if ($fieldValue instanceof \DateTime) {
            $fieldValue = $fieldValue->format('H:i');
        }

        if ($fieldValue === '') {
            // now in local timezone MYSQL. There is a 2 hour difference between the MySQL server and the local timezone
            $fieldValue = (new \DateTime('Europe/Rome'))->format('H:i');
        }

        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . strtolower(str_replace(' ', '_', $name)) . '">' . ucfirst($name) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="time" name="' . strtolower(str_replace(' ', '_', $name)) . '" id="' . strtolower(str_replace(' ', '_', $name)) . '" value="' . $fieldValue . '">
                </div>';
    }

    private function renderNumberField($name, mixed $fieldValue): string
    {
        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . strtolower(str_replace(' ', '_', $name)) . '">' . ucfirst($name) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="number"
                    min="0" 
                    name="' . strtolower(str_replace(' ', '_', $name)) . '" id="' . strtolower(str_replace(' ', '_', $name)) . '" value="' . $fieldValue . '">
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
        $fieldName = $this->splitCamelCase($fieldName);
        return ucwords($fieldName);
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
        $html = '<div class="mb-4">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($fieldName) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="password" name="' . $name . '" id="' . $name . '">
                </div>';
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

    private function renderDateTimeField(mixed $name, mixed $value)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d\TH:i');
        }

        if ($value === '') {
            // now in local timezone MYSQL. There is a 2 hour difference between the MySQL server and the local timezone
            $value = (new \DateTime('Europe/Rome'))->format('Y-m-d\TH:i');
        }

        return '<div class="mb-4 col-span-2">
                    <label
                    class="block text-gray-700 text-sm font-bold mb-2" 
                    for="' . $name . '">' . ucfirst($name) . ':</label>
                    <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    type="datetime-local" name="' . $name . '" id="' . $name . '" value="' . $value . '">
                </div>';
    }

}
