<?php

// Connessione al database utilizzando PDO
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gestionale', 'root', '');

// Query per ottenere l'elenco delle tabelle nel database
$sql = "SHOW TABLES";
$stmt = $pdo->query($sql);

// Ottieni l'elenco delle tabelle
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Genera la lista delle rotte
$routeList = "<?php\n\nuse FastRoute\RouteCollector;\n";
$routeList .= "// Configurazione delle rotte e dei middleware con MiddlewareStack\n\n";
$routeList .= "return function (RouteCollector \$r) {\n\n";

// Aggiungi le rotte pubbliche
$routeList .= "\t// Rotte pubbliche\n";
$routeList .= "\t\$r->addRoute('GET', '/api/v1/403', ['App\Controllers\ErrorController', 'forbiddenView']);\n";
$routeList .= "\t\$r->addRoute('GET', '/api/v1/404', ['App\Controllers\ErrorController', 'notFoundView']);\n";
$routeList .= "\t\$r->addRoute('GET', '/api/v1/405', ['App\Controllers\ErrorController', 'notAllowedView']);\n";
$routeList .= "\t\$r->addRoute('GET', '/api/v1/500', ['App\Controllers\ErrorController', 'internalErrorView']);\n\n";
$routeList .= "\t\$r->addRoute('GET', '/api/v1/', ['App\Controllers\HomeController', 'home']);\n";
// Genera le rotte per le tabelle nel database
foreach ($tables as $tableName) {
    // Genera il nome del controller e delle azioni
    $controllerName = ucfirst(str_replace('_', '', ucwords($tableName, '_'))) . 'ApiController';
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

    $routeList .= "\t\$r->addRoute('GET', '/api/v1/" . $route . "', ['App\Controllers\Api\\$controllerName', '$indexAction']);\n";
    $routeList .= "\t\$r->addRoute('GET', '/api/v1/" . $route . "/create', ['App\Controllers\Api\\$controllerName', '$createAction']);\n";
    $routeList .= "\t\$r->addRoute('POST', '/api/v1/" . $route . "/store', ['App\Controllers\Api\\$controllerName', '$storeAction']);\n\n";

    $routeList .= "\t\$r->addRoute('GET', '/api/v1/" . $route . "/edit/{id:\d+}', ['App\Controllers\Api\\$controllerName', '$editAction']);\n";
    $routeList .= "\t\$r->addRoute('POST', '/api/v1/" . $route . "/update', ['App\Controllers\Api\\$controllerName', '$updateAction']);\n";

    $routeList .= "\t\$r->addRoute('GET', '/api/v1/" . $route . "/delete/{id:\d+}', ['App\Controllers\Api\\$controllerName', '$deleteAction']);\n";
    $routeList .= "\t\$r->addRoute('POST', '/api/v1/" . $route . "/bulk-delete', ['App\Controllers\Api\\$controllerName', '$bulkDeleteAction']);\n\n";
}

$routeList .= "};\n\n";


// Salva il codice generato nel file routes.php
file_put_contents('api.php', $routeList);

// Sposta il file routes.php nella cartella app/Routes
rename('api.php', 'app/Routes/api.php');

echo "Controlla il file routes.php per verificare le modifiche.\n";

echo "Aggiornamento delle rotte completato!\n";
