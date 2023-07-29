<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Libraries\Database;
use App\Libraries\Table;
use App\Libraries\TwigConfigurator;
use App\Libraries\TwigGlobalVars;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

abstract class BaseController
{
    protected Environment $view;
    protected Table $table;
    protected array $args;
    protected array $filters;
    protected Auth $auth;
    protected Database $db;

    public function __construct()
    {
        $this->view = TwigConfigurator::configure();
        $this->args = $this->getArgsArray();
        $this->auth = new Auth();
        $this->db = Database::getInstance();
        TwigGlobalVars::addGlobals($this->view);
    }

    protected function getPagingParams()
    {
        $request = Request::createFromGlobals();
        $limit = $request->query->get('limit', 10);
        $page = $request->query->get('page', 1);
        $order_by = $request->query->get('order_by', 'ID_utente');
        $direction = $request->query->get('direction', 'ASC');
        $search = $request->query->get('s', '');

        // Validate limit
        $limit = (int)$limit;
        if ($limit < 1 || $limit > 100) {
            throw new \InvalidArgumentException('Invalid limit value. It should be between 1 and 100.');
        }

        // Validate page
        $page = (int)$page;
        if ($page < 1) {
            throw new \InvalidArgumentException('Invalid page number. It should be greater than 0.');
        }

        // Validate order by. This should be one of the columns of your table.
        $allowed_order_by_values = ['ID_utente', 'nome', 'cognome', 'email'];
        if (!in_array($order_by, $allowed_order_by_values)) {
            //throw new \InvalidArgumentException('Invalid order by value. Allowed values are: ' . implode(', ', $allowed_order_by_values));
        }

        // Validate direction
        $allowed_direction_values = ['ASC', 'DESC'];
        if (!in_array(strtoupper($direction), $allowed_direction_values)) {
            throw new \InvalidArgumentException('Invalid direction. Allowed values are: ASC, DESC');
        }

        // Validate search string
        if (!preg_match('/^[a-zA-Z0-9 ]*$/', $search)) {
            throw new \InvalidArgumentException('Invalid characters in search string. Only alphanumeric characters and space are allowed.');
        }

        return [
            'limit' => $limit,
            'page' => $page,
            'currentPage' => $page,
            'order_by' => $order_by,
            'direction' => $direction,
            'search' => $search,
            'query' => $request->query->all(),
        ];
    }


    public function getArgsArray()
    {
        return [
            'limit' => $_GET['limit'] ?? 10,
            'currentPage' => $_GET['page'] ?? 1,
            'order_by' =>$_GET['sort'] ?? 'id',
            'order' => $_GET['direction'] ?? 'asc',
            'search' => $_GET['s'] ?? '',
        ];
    }

    protected function createActionsCell($entity, $is_archive = false)
    {
        if ($is_archive) {
            return $this->createRestoreCell($entity);
        }
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
        return '<a href="/' . strtolower($tableName) . '/delete/' . $entity->getId() . '"  onclick="return confirm(\'Sei sicuro di voler eliminare questo elemento?\')"  class="text-xl"><ion-icon name="trash-outline" role="img" class="md hydrated text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-500"></ion-icon></a>';
    }

    // createSearchCell
    private function createSearchCell($entity)
    {
        // get name from entity
        $tableName = $entity->getTableName();
        return '<a href="/' . strtolower($tableName) . '/search/' . $entity->getId() . '" class="text-xl "><ion-icon name="search-outline" role="img" class="md hydrated text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-500"></ion-icon></a>';
    }

    private function createRestoreCell($entity)
    {
        // get name from entity
        $tableName = $entity->getTableName();
        return '<a href="/' . strtolower($tableName) . '/restore/' . $entity->getId() . '"  onclick="return confirm(\'Sei sicuro di voler ripristinare questo elemento?\')"  class="text-xl"><ion-icon name="refresh-circle-outline" role="img" class="md hydrated text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-500"></ion-icon></a>';

    }

    /**
     * Genera l'URL completo per una determinata rotta.
     *
     * @param string $route Nome della rotta (es. 'utenti.index')
     * @param array $params Parametri aggiuntivi per la rotta (opzionale)
     * @return string URL completo per la rotta specificata
     */
    function url(string $route, array $params = []): string
    {
        $baseUrl = $_ENV['BASE_URL'] ?? '';
        $routeParts = explode('.', $route);
        $controllerName = $routeParts[0];
        $actionName = $routeParts[1] ?? 'index';

        $url = $baseUrl . '/' . strtolower($controllerName);

        if ($actionName !== 'index') {
            $url .= '/' . $actionName;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

}