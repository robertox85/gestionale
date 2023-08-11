<?php

namespace App\Libraries;

class DynamicFormComponent
{
    private array $entities;
    private FormRenderer $formRenderer;

    public function __construct($model)
    {
        $this->entities = ReflectionHelper::getEntities($model);
        $this->formRenderer = new FormRenderer();
    }

    public function renderForm(array $args): string
    {
        $args['entities'] = $this->entities;
        return $this->formRenderer->render($args);
    }
}

