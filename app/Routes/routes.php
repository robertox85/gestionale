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

    // set_language must have '?_locale=en' in the URL to set the language to 'en
    $r->addRoute('GET', '/set_language', ['App\Controllers\Web\LanguageController', 'setLanguage']);
};



return $routes;
