<?php

namespace App\Libraries;

class DynamicFormComponent
{
    private array $entityFields;
    private $model;
    private array $relatedFields = [];

    private string $primaryKeyName;

    public function __construct($model)
    {
        $this->model = $model;
        $this->entityFields = ReflectionHelper::getEntityFields($model, $this->relatedFields);
        $this->primaryKeyName = $this->model->getPrimaryKeyName();
    }

    public function renderForm(array $formData): string
    {
        $args = [
            'model' => $this->model,
            'entityFields' => $this->entityFields,
            'relatedFields' => $this->relatedFields,
            'formData' => $formData,
            'primaryKeyName' => $this->primaryKeyName
        ];

        $form = new FormRenderer($args);
        return $form->renderForm();
    }


}

