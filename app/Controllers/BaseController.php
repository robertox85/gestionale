<?php

namespace App\Controllers;

use GuzzleHttp\Psr7\Request;
use Twig\Environment;
use App\Libraries\TwigConfigurator;
use App\Libraries\TwigGlobalVars;
use App\Libraries\Database;

abstract class BaseController
{
    protected Environment $view;


    public function __construct()
    {
        $this->view = TwigConfigurator::configure();
        TwigGlobalVars::addGlobals($this->view);
    }

    public function generateForm($model, $template_name) {
        $tableName = $model->getTableName();
        $fields = $model->getFields();

        // Utilizzare Twig per generare il form.
        // Potresti passare $tableName e $fields al tuo template per usarli nella generazione del form.
        echo $this->view->render($template_name, ['tableName' => $tableName, 'fields' => $fields]);
    }

}