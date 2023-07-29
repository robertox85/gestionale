<?php

// Leggi il contenuto del file delle rotte (routes.php)
$routesFileContent = file_get_contents('app/Routes/routes.php');

// Cerca le rotte che corrispondono all'azione index() di ogni tabella nel database. Le rotte sono definite come: ->addRoute('GET', '/{nome-tabella}', ['App\Controllers\{NomeTabella}Controller', 'index'])
preg_match_all('/->addRoute\(\'GET\', \'\/([a-z-]+)\', \[\'App\\\\Controllers\\\\([a-zA-Z]+)Controller\', \'index\'\]\)/', $routesFileContent, $matches);

// Ottieni l'elenco delle tabelle e dei rispettivi controller
$tables = $matches[1];
$controllers = $matches[2];

// Genera il codice per la sidebar.twig
$sidebarCode = "<aside class=\"fixed top-0 left-0 z-40 w-48 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-700\" aria-label=\"Sidenav\" id=\"drawer-navigation\">\n";
$sidebarCode .= "\t<div class=\"overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800\">\n";

$sidebarCode .= "\t\t\t<ul class=\"space-y-2\">\n";

// Genera i link per l'azione index() di ogni tabella
foreach ($tables as $key => $tableName) {
    $controllerName = $controllers[$key];


    // add separator for tables with camel case name
    $route = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $tableName));
    // remove separator for tables with camel case name
    $route = str_replace('-', ' ', $route);
    $route = ucwords($route);



    $sidebarCode .= "\t\t\t\t<li>\n";
    $sidebarCode .= "\t\t\t\t\t<a href=\"{{ url('/" . strtolower($tableName) . "') }}\"\n";
    $sidebarCode .= "\t\t\t\t\t   class=\"flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group\">\n";
    $sidebarCode .= "\t\t\t\t\t\t<ion-icon name=\"document-text\"\n";
    $sidebarCode .= "\t\t\t\t\t\t\t  class=\"w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white\"></ion-icon>\n";
    $sidebarCode .= "\t\t\t\t\t\t<span class=\"ml-3\">\n";
    $sidebarCode .= "\t\t\t\t\t\t\t{{ '" . $route . "'|trans }}\n";
    $sidebarCode .= "\t\t\t\t\t\t</span>\n";
    $sidebarCode .= "\t\t\t\t\t</a>\n";
    $sidebarCode .= "\t\t\t\t</li>\n";

}

$sidebarCode .= "\t\t</ul>\n";

$sidebarCode .= "</div>\n";
$sidebarCode .= "</aside>\n";

// Salva il codice generato nel file sidebar.twig
file_put_contents('sidebar.html.twig', $sidebarCode);

// Sposta il file sidebar.twig nella cartella app/Views
rename('sidebar.html.twig', 'app/Views/sidebar.html.twig');

echo "Aggiornamento della sidebar completato!\n";
