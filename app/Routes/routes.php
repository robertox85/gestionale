<?php

// start session
session_start();

use FastRoute\RouteCollector;

// Carica le rotte web
$webRoutes = require_once __DIR__ . '/web.php';

// Carica le rotte API
$apiRoutes = require_once __DIR__ . '/api.php';

// Combina le rotte
$routes = function (RouteCollector $r) use ($webRoutes, $apiRoutes) {
    $webRoutes($r);
    $apiRoutes($r);
};



return $routes;
