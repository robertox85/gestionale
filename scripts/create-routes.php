<?php

// Connessione al database utilizzando PDO
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gestionale', 'root', '');

// Query per ottenere l'elenco delle tabelle nel database
$sql = "SHOW TABLES";
$stmt = $pdo->query($sql);

// Ottieni l'elenco delle tabelle
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Genera la lista delle rotte
$routeList = "<?php\n\n// start session\nsession_start();\n\nuse FastRoute\RouteCollector;\n";
$routeList .= "use App\Middleware\AuthenticationMiddleware;\n";
$routeList .= "use App\Middleware\AuthorizationMiddleware;\n";
$routeList .= "use App\Middleware\MiddlewareStack;\n\n";
$routeList .= "// Configurazione delle rotte e dei middleware con MiddlewareStack\n\n";
$routeList .= "\$routes = function (RouteCollector \$r) {\n\n";

// Aggiungi le rotte pubbliche
$routeList .= "\t// Rotte pubbliche\n";
$routeList .= "\t\$r->addRoute('GET', '/403', ['App\Controllers\ErrorController', 'forbiddenView']);\n";
$routeList .= "\t\$r->addRoute('GET', '/404', ['App\Controllers\ErrorController', 'notFoundView']);\n";
$routeList .= "\t\$r->addRoute('GET', '/405', ['App\Controllers\ErrorController', 'notAllowedView']);\n";
$routeList .= "\t\$r->addRoute('GET', '/500', ['App\Controllers\ErrorController', 'internalErrorView']);\n\n";
$routeList .= "\t\$r->addRoute('GET', '/', ['App\Controllers\HomeController', 'home']);\n";
// Genera le rotte per le tabelle nel database
foreach ($tables as $tableName) {
    // Genera il nome del controller e delle azioni
    $controllerName = ucfirst(str_replace('_', '', ucwords($tableName, '_'))) . 'Controller';
    $indexAction = 'index';
    $createAction = 'create';
    $editAction = 'edit';
    $updateAction = 'update';
    $deleteAction = 'delete';
    $storeAction = 'store';
    $bulkDeleteAction = 'bulkDelete';

    // Aggiungi le rotte per l'elenco, creazione, modifica ed eliminazione
    $routeList .= "\t// Rotte per $tableName\n";

    // add separator for tables with camel case name
    $route = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $tableName));

    $routeList .= "\t\$r->addRoute('GET', '/" . $route . "', ['App\Controllers\\$controllerName', '$indexAction']);\n";
    $routeList .= "\t\$r->addRoute('GET', '/" . $route . "/create', ['App\Controllers\\$controllerName', '$createAction']);\n";
    $routeList .= "\t\$r->addRoute('POST', '/" . $route . "/store', ['App\Controllers\\$controllerName', '$storeAction']);\n\n";

    $routeList .= "\t\$r->addRoute('GET', '/" . $route . "/edit/{id:\d+}', ['App\Controllers\\$controllerName', '$editAction']);\n";
    $routeList .= "\t\$r->addRoute('POST', '/" . $route . "/update', ['App\Controllers\\$controllerName', '$updateAction']);\n";

    $routeList .= "\t\$r->addRoute('GET', '/" . $route . "/delete/{id:\d+}', ['App\Controllers\\$controllerName', '$deleteAction']);\n";
    $routeList .= "\t\$r->addRoute('POST', '/" . $route . "/bulk-delete', ['App\Controllers\\$controllerName', '$bulkDeleteAction']);\n\n";
}

$routeList .= "};\n\n";
$routeList .= "return \$routes;\n";

// Salva il codice generato nel file routes.php
file_put_contents('routes.php', $routeList);

// Sposta il file routes.php nella cartella app/Routes
rename('routes.php', 'app/Routes/routes.php');

echo "Controlla il file routes.php per verificare le modifiche.\n";

echo "Aggiornamento delle rotte completato!\n";
