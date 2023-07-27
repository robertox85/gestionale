<?php
// start session
session_start();

use FastRoute\RouteCollector;
use App\Middleware\AuthenticationMiddleware;
use App\Middleware\AuthorizationMiddleware;
use App\Middleware\MiddlewareStack;

// Configurazione delle rotte e dei middleware con MiddlewareStack


$routes = function (RouteCollector $r) {


    // Rotte pubbliche
    $r->addRoute('GET', '/403', ['App\Controllers\ErrorController', 'forbiddenView']);
    $r->addRoute('GET', '/404', ['App\Controllers\ErrorController', 'notFoundView']);
    $r->addRoute('GET', '/405', ['App\Controllers\ErrorController', 'notAllowedView']);
    $r->addRoute('GET', '/500', ['App\Controllers\ErrorController', 'internalErrorView']);

    // Login
    $r->addRoute('GET', '/sign-in', ['App\Controllers\AuthController', 'signInView']);
    $r->addRoute('POST', '/sign-in', ['App\Controllers\AuthController', 'signInUser']);

    // Logout
    $r->addRoute('GET', '/sign-out', ['App\Controllers\AuthController', 'signOutUser']);


    // Forgot password
    $r->addRoute('GET', '/forgot-password', ['App\Controllers\AuthController', 'forgotPasswordView']);
    $r->addRoute('POST', '/forgot-password', ['App\Controllers\AuthController', 'forgotPassword']);

    // Usa l'istanza di AuthenticationMiddleware per le tue rotte che richiedono autenticazione
    $r->addRoute('GET', '/', ['App\Controllers\HomeController', 'homeView']);
    $r->addRoute('GET', '/utenti', ['App\Controllers\HomeController', 'homeView']);
    $r->addRoute('GET', '/utenti/new', ['App\Controllers\HomeController', 'newItem']);
    $r->addRoute('GET', '/utenti/delete/{id}', ['App\Controllers\HomeController', 'delete']);

    $r->addRoute('POST', '/utenti/bulk-delete', ['App\Controllers\HomeController', 'bulkDelete']);

    // Utenti
    $r->addRoute('GET', '/users', ['App\Controllers\UsersController', 'usersView']);


};

return $routes;
