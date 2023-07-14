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

    // Registrazione
    //$r->addRoute('GET', '/sign-up', ['App\Controllers\AuthController', 'signUpView']);
    //$r->addRoute('POST', '/sign-up', ['App\Controllers\AuthController', 'signUpUser']);

    // Usa l'istanza di AuthenticationMiddleware per le tue rotte che richiedono autenticazione
    $r->addRoute('GET', '/', ['App\Controllers\HomeController', 'homeView']);

    $middlewareStack = new MiddlewareStack();
    $middlewareStack->add(new AuthenticationMiddleware(
            [
                'amministratore',
                'dominus',
                'segretaria',
            ]
        )
    );
    $middlewareStack->add(new AuthorizationMiddleware('visualizza_pratiche'));

    // Pratiche
    $r->addRoute('GET', '/pratiche', [
        'middleware' => [$middlewareStack],
        'handler' => ['App\Controllers\PraticheController', 'praticheView']
    ]);

    $middlewareStack = new MiddlewareStack();
    $middlewareStack->add(new AuthenticationMiddleware(
            [
                'amministratore',
                'referente',
                'cliente',
            ]
        )
    );
    $middlewareStack->add(new AuthorizationMiddleware('visualizza_pratiche'));

    $r->addRoute('GET', '/mie_pratiche', [
        'middleware' => [$middlewareStack],
        'handler' => ['App\Controllers\PraticheController', 'miePraticheView']
    ]);

    $middlewareStack = new MiddlewareStack();
    $middlewareStack->add(new AuthenticationMiddleware(
            [
                'amministratore',
                'dominus',
                'segretaria',
            ]
        )
    );
    $middlewareStack->add(new AuthorizationMiddleware('modifica_pratiche'));


    // Pratica
    $r->addRoute('GET', '/pratiche/edit/{id:\d+}', [
        'middleware' => [$middlewareStack],
        'handler' => ['App\Controllers\PraticheController', 'editPraticaView']
    ]);

    $r->addRoute('POST', '/pratiche/edit', [
        'middleware' => [$middlewareStack],
        'handler' => ['App\Controllers\PraticheController', 'editPratica']
    ]);

    $middlewareStack = new MiddlewareStack();
    $middlewareStack->add(new AuthenticationMiddleware(
            [
                'amministratore',
                'dominus',
                'segretaria',
            ]
        )
    );
    $middlewareStack->add(new AuthorizationMiddleware('elimina_pratiche'));
    $r->addRoute('GET', '/pratiche/delete/{id:\d+}', [
        'middleware' => [$middlewareStack],
        'handler' => ['App\Controllers\PraticheController', 'deletePratica']
    ]);

    $middlewareStack = new MiddlewareStack();
    $middlewareStack->add(new AuthenticationMiddleware(
            [
                'amministratore',
                'dominus',
                'segretaria',
            ]
        )
    );
    $middlewareStack->add(new AuthorizationMiddleware('crea_pratica'));


    // pratiche/crea
    $r->addRoute('GET', '/pratiche/crea', [
        'middleware' => [$middlewareStack],
        'handler' => ['App\Controllers\PraticheController', 'praticaCreaView']
    ]);

    // pratiche/crea POST
    $r->addRoute('POST', '/pratiche/crea', [
        'middleware' => [$middlewareStack],
        'handler' => ['App\Controllers\PraticheController', 'createPratica']
    ]);

    // Ricerca
    $r->addRoute('GET', '/ricerca', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\SearchController', 'ricercaView']
    ]);

    // Utenti
    $r->addRoute('GET', '/utenti', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'utentiView']
    ]);


    $r->addRoute('POST', '/utenti', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'utentiView']
    ]);


    // Assistiti
    $r->addRoute('GET', '/controparti', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'contropartiView']
    ]);

    $r->addRoute('GET', '/assistiti', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'assistitiView']
    ]);


    // Utente
    $r->addRoute('GET', '/utenti/edit/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'utenteView']
    ]);

    // utenti/edit POST
    $r->addRoute('POST', '/utenti/edit', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'editUtente']
    ]);

    // utenti/delete
    $r->addRoute('GET', '/utenti/delete/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'deleteUtente']
    ]);

    // utenti/crea
    $r->addRoute('GET', '/utenti/crea', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'utenteCreaView']
    ]);

    // utenti/crea POST
    $r->addRoute('POST', '/utenti/crea', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'createUtente']
    ]);


    // Archivio
    $r->addRoute('GET', '/archivio', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\ArchiveController', 'archivioView']
    ]);

    // Programmi
    $r->addRoute('GET', '/programmi', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\ProgramController', 'programmiView']
    ]);

    // Programma
    $r->addRoute('GET', '/programma/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\ProgramController', 'programmaView']
    ]);

    // gruppi


    $r->addRoute('GET', '/gruppi', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\GruppiController', 'gruppiView']
    ]);


    $r->addRoute('GET', '/gruppi/crea', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\GruppiController', 'creaGruppoView']
    ]);


    // creaGruppoAjax
    $r->addRoute('POST', '/creaGruppoAjax', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\GruppiController', 'creaGruppoAjax']
    ]);

    $r->addRoute('POST', '/gruppi/crea', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\GruppiController', 'creaGruppo']
    ]);

    $r->addRoute('GET', '/gruppi/delete/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\GruppiController', 'deleteGruppo']
    ]);


    // gruppo
    $r->addRoute('GET', '/gruppi/edit/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\GruppiController', 'editGruppoView']
    ]);

    $r->addRoute('POST', '/gruppi/edit', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\GruppiController', 'editGruppo']
    ]);

    // search ajax routes with parameters
    $r->addRoute('GET', '/search', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\SearchController', 'search']
    ]);

    $r->addRoute('POST', '/create', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\SearchController', 'create']
    ]);


    // Impostazioni
    $r->addRoute('GET', '/impostazioni', [
        'middleware' => new AuthenticationMiddleware('amministratore'),
        'handler' => ['App\Controllers\SettingsController', 'impostazioniView']
    ]);

    // Impostazioni assegna_permessi_ruoli POST
    $r->addRoute('POST', '/impostazioni/aggiornaRuolo', [
        'middleware' => new AuthenticationMiddleware('amministratore'),
        'handler' => ['App\Controllers\SettingsController', 'aggiornaRuolo']
    ]);

    // creaPermesso
    $r->addRoute('POST', '/impostazioni/creaPermesso', [
        'middleware' => new AuthenticationMiddleware('amministratore'),
        'handler' => ['App\Controllers\SettingsController', 'creaPermesso']
    ]);

    // impostazioni/eliminaPermesso
    $r->addRoute('GET', '/impostazioni/eliminaPermesso/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware('amministratore'),
        'handler' => ['App\Controllers\SettingsController', 'eliminaPermesso']
    ]);

    // rotta pubblica
    $r->addRoute('GET', '/public', ['App\Controllers\HomeController', 'publicView']);


    // set_language must have '?_locale=en' in the URL to set the language to 'en
    $r->addRoute('GET', '/set_language', ['App\Controllers\LocaleController', 'setLanguage']);
};

return $routes;
