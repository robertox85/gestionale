<?php
$dir = dirname(__DIR__, 1);
// Autoload delle classi
require_once $dir . '/vendor/autoload.php';

require $dir . '/app/Middleware/AuthenticationMiddleware.php';
require $dir . '/app/Middleware/AuthorizationMiddleware.php'; // Aggiunto

use FastRoute\Dispatcher;
use Dotenv\Dotenv;
use App\Libraries\Helper;
use App\Libraries\ErrorHandler;



// Caricamento delle variabili d'ambiente
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();
// Configurazione delle rotte
$routes = require $dir . '/app/Routes/routes.php';

// Inizializzazione del dispatcher di FastRoute
$dispatcher = FastRoute\simpleDispatcher($routes);


// Gestione delle richieste HTTP
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Rimozione dei parametri dalla URI
if (($pos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $pos);
}

// Gestione dello slash finale
if ($uri !== '/' && substr($uri, -1) === '/') {
    header('Location: ' . substr($uri, 0, -1), true, 301);
    exit();
}

// Dispatch della richiesta
try {
    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

    switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            // Pagina non trovata
            Helper::addError('Pagina non trovata');
            Helper::redirect('404');
            break;

        case Dispatcher::METHOD_NOT_ALLOWED:
            // Metodo non consentito
            $allowedMethods = $routeInfo[1];
            Helper::addError('Metodo non consentito');
            Helper::redirect('405');
            break;

        case Dispatcher::FOUND:
            // Controlla se la rotta è protetta da middleware
            if (isset($routeInfo[1]['middleware'])) {

                $middlewares = $routeInfo[1]['middleware']; // Array dei middleware
                if (!is_array($middlewares)) {
                    $middlewares = [$middlewares];
                }


                $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
                $response = new \GuzzleHttp\Psr7\Response();
                $body = $request->getParsedBody();

                // Gestisci ogni middleware
                foreach ($middlewares as $middleware) {
                    $response = $middleware($request, $response, function ($request, $response) use ($body, $routeInfo) {
                        $controllerClass = $routeInfo[1]['handler'][0];
                        $method = $routeInfo[1]['handler'][1];
                        $vars = $routeInfo[2];
                        $vars = array_values($vars);


                        // Caricamento del controller e invocazione del metodo

                        $controller = new $controllerClass();
                        try {
                            // call_user_func_array([$controller, $method], [$body] + $vars);
                            call_user_func_array([$controller, $method], $vars);
                        } catch (Exception $e) {
                            ErrorHandler::handle($e);
                            return;
                        }
                    });
                }

                break;
            } else {
                $controllerClass = $routeInfo[1][0];
                $method = $routeInfo[1][1];
                $vars = $routeInfo[2];

                // Caricamento del controller e invocazione del metodo
                $controller = new $controllerClass();
                try {
                    call_user_func_array([$controller, $method], $vars);
                } catch (Exception $e) {
                    ErrorHandler::handle($e);
                    return;
                } catch (Error $e) {
                    ErrorHandler::handle($e);
                    return;
                }
            }
            break;
    }
} catch (Exception $e) {
    ErrorHandler::handle($e);
    return;
} catch (Error $e) {
    ErrorHandler::handle($e);
    return;
}
