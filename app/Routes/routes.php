<?php
// start session
session_start();

use FastRoute\RouteCollector;
use App\Middleware\AuthenticationMiddleware;
use App\Middleware\AuthorizationMiddleware;
use App\Middleware\MiddlewareStack;

// Configurazione delle rotte e dei middleware con MiddlewareStack


$routes = function (RouteCollector $r) {


    $middlewareStack = new MiddlewareStack();

    $middlewareStack->add(new AuthenticationMiddleware(['amministratore','operatore']));
    $middlewareStack->add(new AuthorizationMiddleware('visualizza_pratiche'));

    // Rotte pubbliche
    $r->addRoute('GET', '/404', ['App\Controllers\ErrorController', 'notFoundView']);
    $r->addRoute('GET', '/405', ['App\Controllers\ErrorController', 'notAllowedView']);
    $r->addRoute('GET', '/500', ['App\Controllers\ErrorController', 'internalErrorView']);

    // Login
    $r->addRoute('GET', '/sign-in', ['App\Controllers\AuthController', 'signInView']);
    $r->addRoute('POST', '/sign-in', ['App\Controllers\AuthController', 'signInUser']);

    // Logout
    $r->addRoute('GET', '/sign-out', ['App\Controllers\AuthController', 'signOutUser']);

    // Registrazione
    $r->addRoute('GET', '/sign-up', ['App\Controllers\AuthController', 'signUpView']);
    $r->addRoute('POST', '/sign-up', ['App\Controllers\AuthController', 'signUpUser']);



    // Usa l'istanza di AuthenticationMiddleware per le tue rotte che richiedono autenticazione
    $r->addRoute('GET', '/', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\HomeController', 'homeView']
    ]);

    // Pratiche
    $r->addRoute('GET', '/pratiche', [
        'middleware' => [$middlewareStack],
        'handler' => ['App\Controllers\PraticheController', 'praticheView']
    ]);

    // Pratica
    $r->addRoute('GET', '/pratiche/edit/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\PraticheController', 'editPraticaView']
    ]);

    $r->addRoute('POST', '/pratiche/edit', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\PraticheController', 'editPratica']
    ]);

    $r->addRoute('GET', '/pratiche/delete/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\PraticheController', 'deletePratica']
    ]);

    // pratiche/crea
    $r->addRoute('GET', '/pratiche/crea', [
        'middleware' => new AuthenticationMiddleware(
            ['amministratore','dominus']
        ),

        'handler' => ['App\Controllers\PraticheController', 'praticaCreaView']
    ]);

    // pratiche/crea POST
    $r->addRoute('POST', '/pratiche/crea', [
        'middleware' => new AuthenticationMiddleware(
            ['amministratore','dominus']
        ),
        'handler' => ['App\Controllers\PraticheController', 'createPratica']
    ]);


    // Assistiti
    $r->addRoute('GET', '/assistiti', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'assistitiView']
    ]);

    // Assistito
    $r->addRoute('GET', '/assistito/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'assistitoView']
    ]);

    // Controparti
    $r->addRoute('GET', '/controparti', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'contropartiView']
    ]);

    // Controparte
    $r->addRoute('GET', '/controparte/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'controparteView']
    ]);

    // Collaboratori
    $r->addRoute('GET', '/collaboratori', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'collaboratoriView']
    ]);

    // Collaboratore
    $r->addRoute('GET', '/collaboratore/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\UserController', 'collaboratoreView']
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
    $middlewareStack = new MiddlewareStack();
    $middlewareStack->add(new AuthenticationMiddleware(['amministratore','dominus']));
    $middlewareStack->add(new AuthorizationMiddleware('visualizza_gruppi'));

    $r->addRoute('GET', '/gruppi', [
        'middleware' => $middlewareStack,
        'handler' => ['App\Controllers\GruppiController', 'gruppiView']
    ]);

    $middlewareStack = new MiddlewareStack();
    $middlewareStack->add(new AuthenticationMiddleware(['amministratore','dominus']));
    $middlewareStack->add(new AuthorizationMiddleware(['crea_gruppo','visualizza_gruppi','modifica_gruppo','elimina_gruppo']));

    $r->addRoute('GET', '/gruppi/crea', [
        'middleware' => $middlewareStack,
        'handler' => ['App\Controllers\GruppiController', 'creaGruppoView']
    ]);

    $r->addRoute('POST', '/gruppi/crea', [
        'middleware' => $middlewareStack,
        'handler' => ['App\Controllers\GruppiController', 'creaGruppo']
    ]);

    $r->addRoute('GET', '/gruppi/delete/{id:\d+}', [
        'middleware' => $middlewareStack,
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

    $middlewareStack = new MiddlewareStack();
    $middlewareStack->add(new AuthenticationMiddleware(['amministratore','dominus']));
    $middlewareStack->add(new AuthorizationMiddleware('visualizza_sottogruppi'));

    // sotto gruppi
    $r->addRoute('GET', '/sottogruppi', [
        'middleware' => $middlewareStack,
        'handler' => ['App\Controllers\SottoGruppoController', 'sottogruppiView']
    ]);

    $r->addRoute('GET', '/sottogruppi/{id:\d+}', [
        'middleware' => $middlewareStack,
        'handler' => ['App\Controllers\SottoGruppoController', 'sottogruppiView']
    ]);

    // sotto gruppo
    $r->addRoute('GET', '/sottogruppo/{id:\d+}', [
        'middleware' => new AuthenticationMiddleware(),
        'handler' => ['App\Controllers\GruppiController', 'sottogruppoView']
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


    // rotta pubblica
    $r->addRoute('GET', '/public', ['App\Controllers\HomeController', 'publicView']);



    // set_language must have '?_locale=en' in the URL to set the language to 'en
    $r->addRoute('GET', '/set_language', ['App\Controllers\LocaleController', 'setLanguage']);
};

return $routes;
