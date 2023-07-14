<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Libraries\Table;
use App\Libraries\TwigConfigurator;
use App\Libraries\TwigGlobalVars;
use Twig\Environment;

abstract class BaseController
{
    protected Environment $view;

    protected Table $table;
    protected array $args;
    protected array $filters;

    protected Auth $auth;

    public function __construct()
    {
        $this->view = TwigConfigurator::configure();
        $this->args = $this->getArgsArray();
        $this->auth = new Auth();
        TwigGlobalVars::addGlobals($this->view);
    }


    public function getArgsArray()
    {
        return [
            'limit' => $_GET['limit'] ?? 10,
            'currentPage' => $_GET['page'] ?? 1,
            'sort' =>$_GET['sort'] ?? 'id',
            'order' => $_GET['direction'] ?? 'asc',
        ];
    }

    protected function createActionsCell($entity)
    {
        return $this->createEditCell($entity) . $this->createDeleteCell($entity);
    }

    protected function createEditCell($entity)
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

    // createSearchCell
    private function createSearchCell($entity)
    {
        // get name from entity
        $tableName = $entity->getTableName();
        return '<a href="/' . strtolower($tableName) . '/search/' . $entity->getId() . '" class="text-xl "><ion-icon name="search-outline" role="img" class="md hydrated text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-500"></ion-icon></a>';
    }
}