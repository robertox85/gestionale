<?php

namespace App\Controllers;

use App\Models\BaseModel;
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

    public function generateForm($model, $template_name)
    {
        $tableName = $model->getTableName();
        $fields = $model->getFields();

        // Utilizzare Twig per generare il form.
        // Potresti passare $tableName e $fields al tuo template per usarli nella generazione del form.
        echo $this->view->render($template_name, ['tableName' => $tableName, 'fields' => $fields]);
    }

    protected function createViewArgs()
    {
        $limit = $_GET['limit'] ?? 10;
        $currentPage = $_GET['page'] ?? 1;
        $sort = $_GET['sort'] ?? 'id';
        $direction = $_GET['direction'] ?? 'asc';

        return [
            'limit' => $limit,
            'currentPage' => $currentPage,
            'sort' => $sort,
            'order' => $direction,
        ];
    }

    protected function createActionsCell($entity)
    {
        // if is Utenti, return only view and edit
        if ($entity->getTableName() === 'Utenti') {
            return $this->createSearchCell($entity) . $this->createEditCell($entity) . $this->createDeleteCell($entity);
        }

        return $this->createEditCell($entity) . $this->createDeleteCell($entity);

    }

    private function createEditCell($entity)
    {
        // get name from entity
        $tableName = $entity->getTableName();
        return '<a href="/' . strtolower($tableName) . '/edit/' . $entity->getId() . '" class="text-xl "><ion-icon name="create-outline" role="img" class="md hydrated text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-500"></ion-icon></a>';
    }

    private function createDeleteCell($entity)
    {
        // get name from entity
        $tableName = $entity->getTableName();
        return '<a href="/' . strtolower($tableName) . '/delete/' . $entity->getId() . '"  onclick="return confirm(\'Sei sicuro di voler eliminare questo gruppo?\')"  class="text-xl"><ion-icon name="trash-outline" role="img" class="md hydrated text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-500"></ion-icon></a>';
    }

    private function createViewCell($entity)
    {
        // get name from entity
        $tableName = $entity->getTableName();
        return '<a href="/' . strtolower($tableName) . '/view/' . $entity->getId() . '" class="text-xl "><ion-icon name="eye-outline" role="img" class="md hydrated text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-500"></ion-icon></a>';
    }

    // createSearchCell
    private function createSearchCell($entity)
    {
        // get name from entity
        $tableName = $entity->getTableName();
        return '<a href="/' . strtolower($tableName) . '/search/' . $entity->getId() . '" class="text-xl "><ion-icon name="search-outline" role="img" class="md hydrated text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-500"></ion-icon></a>';
    }
}