<?php

namespace App\Libraries;

class DynamicFormComponent
{
    private array $entityFields;
    private array $entityValues;
    private $model;
    private array $relatedFields = [];

    private string $primaryKeyName;

    /**
     * @throws \ReflectionException
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->entityFields = ReflectionHelper::getEntityFields($model, $this->relatedFields);
        $this->entityValues = ReflectionHelper::getEntityValues($model);
        $this->primaryKeyName = $this->model->getPrimaryKeyName();
    }

    public function renderForm(array $formData): string
    {
        $args = [
            'model' => $this->model,
            'entityFields' => $this->entityFields,
            'entityValues' => $this->entityValues,
            'relatedFields' => $this->relatedFields,
            'formData' => $formData,
            'primaryKeyName' => $this->primaryKeyName
        ];

        $form = new FormRenderer($args);
        return $form->renderForm();
    }


}

