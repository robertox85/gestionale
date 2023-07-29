<?php

namespace App\Controllers;

use App\Libraries\Auth;
use App\Libraries\Database;
use App\Libraries\TwigConfigurator;
use App\Libraries\TwigGlobalVars;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

abstract class BaseController
{
    protected Environment $view;
    protected array $args;
    protected array $filters;
    protected Auth $auth;
    protected Database $db;

    public function __construct()
    {
        $this->view = TwigConfigurator::configure();
        $this->args = [
            'limit' => $_GET['limit'] ?? 10,
            'currentPage' => $_GET['page'] ?? 1,
            'order_by' =>$_GET['sort'] ?? 'id',
            'order' => $_GET['direction'] ?? 'asc',
            'search' => $_GET['s'] ?? '',
        ];
        $this->auth = new Auth();
        $this->db = Database::getInstance();
        TwigGlobalVars::addGlobals($this->view);
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