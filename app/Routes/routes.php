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

	$r->addRoute('GET', '/', ['App\Controllers\HomeController', 'home']);
	// Rotte per DisponibilitaSale
	$r->addRoute('GET', '/disponibilita-sale', ['App\Controllers\DisponibilitaSaleController', 'index']);
	$r->addRoute('GET', '/disponibilita-sale/create', ['App\Controllers\DisponibilitaSaleController', 'create']);
	$r->addRoute('POST', '/disponibilita-sale/store', ['App\Controllers\DisponibilitaSaleController', 'store']);

	$r->addRoute('GET', '/disponibilita-sale/edit/{id:\d+}', ['App\Controllers\DisponibilitaSaleController', 'edit']);
	$r->addRoute('POST', '/disponibilita-sale/update', ['App\Controllers\DisponibilitaSaleController', 'update']);
	$r->addRoute('GET', '/disponibilita-sale/delete/{id:\d+}', ['App\Controllers\DisponibilitaSaleController', 'delete']);
	$r->addRoute('POST', '/disponibilita-sale/bulk-delete', ['App\Controllers\DisponibilitaSaleController', 'bulkDelete']);

	// Rotte per EccezioniSale
	$r->addRoute('GET', '/eccezioni-sale', ['App\Controllers\EccezioniSaleController', 'index']);
	$r->addRoute('GET', '/eccezioni-sale/create', ['App\Controllers\EccezioniSaleController', 'create']);
	$r->addRoute('POST', '/eccezioni-sale/store', ['App\Controllers\EccezioniSaleController', 'store']);

	$r->addRoute('GET', '/eccezioni-sale/edit/{id:\d+}', ['App\Controllers\EccezioniSaleController', 'edit']);
	$r->addRoute('POST', '/eccezioni-sale/update', ['App\Controllers\EccezioniSaleController', 'update']);
	$r->addRoute('GET', '/eccezioni-sale/delete/{id:\d+}', ['App\Controllers\EccezioniSaleController', 'delete']);
	$r->addRoute('POST', '/eccezioni-sale/bulk-delete', ['App\Controllers\EccezioniSaleController', 'bulkDelete']);

	// Rotte per LogOperazioniUtente
	$r->addRoute('GET', '/log-operazioni-utente', ['App\Controllers\LogOperazioniUtenteController', 'index']);
	$r->addRoute('GET', '/log-operazioni-utente/create', ['App\Controllers\LogOperazioniUtenteController', 'create']);
	$r->addRoute('POST', '/log-operazioni-utente/store', ['App\Controllers\LogOperazioniUtenteController', 'store']);

	$r->addRoute('GET', '/log-operazioni-utente/edit/{id:\d+}', ['App\Controllers\LogOperazioniUtenteController', 'edit']);
	$r->addRoute('POST', '/log-operazioni-utente/update', ['App\Controllers\LogOperazioniUtenteController', 'update']);
	$r->addRoute('GET', '/log-operazioni-utente/delete/{id:\d+}', ['App\Controllers\LogOperazioniUtenteController', 'delete']);
	$r->addRoute('POST', '/log-operazioni-utente/bulk-delete', ['App\Controllers\LogOperazioniUtenteController', 'bulkDelete']);

	// Rotte per Notifiche
	$r->addRoute('GET', '/notifiche', ['App\Controllers\NotificheController', 'index']);
	$r->addRoute('GET', '/notifiche/create', ['App\Controllers\NotificheController', 'create']);
	$r->addRoute('POST', '/notifiche/store', ['App\Controllers\NotificheController', 'store']);

	$r->addRoute('GET', '/notifiche/edit/{id:\d+}', ['App\Controllers\NotificheController', 'edit']);
	$r->addRoute('POST', '/notifiche/update', ['App\Controllers\NotificheController', 'update']);
	$r->addRoute('GET', '/notifiche/delete/{id:\d+}', ['App\Controllers\NotificheController', 'delete']);
	$r->addRoute('POST', '/notifiche/bulk-delete', ['App\Controllers\NotificheController', 'bulkDelete']);

	// Rotte per PreferenzeUtenteSale
	$r->addRoute('GET', '/preferenze-utente-sale', ['App\Controllers\PreferenzeUtenteSaleController', 'index']);
	$r->addRoute('GET', '/preferenze-utente-sale/create', ['App\Controllers\PreferenzeUtenteSaleController', 'create']);
	$r->addRoute('POST', '/preferenze-utente-sale/store', ['App\Controllers\PreferenzeUtenteSaleController', 'store']);

	$r->addRoute('GET', '/preferenze-utente-sale/edit/{id:\d+}', ['App\Controllers\PreferenzeUtenteSaleController', 'edit']);
	$r->addRoute('POST', '/preferenze-utente-sale/update', ['App\Controllers\PreferenzeUtenteSaleController', 'update']);
	$r->addRoute('GET', '/preferenze-utente-sale/delete/{id:\d+}', ['App\Controllers\PreferenzeUtenteSaleController', 'delete']);
	$r->addRoute('POST', '/preferenze-utente-sale/bulk-delete', ['App\Controllers\PreferenzeUtenteSaleController', 'bulkDelete']);

	// Rotte per Prenotazioni
	$r->addRoute('GET', '/prenotazioni', ['App\Controllers\PrenotazioniController', 'index']);
	$r->addRoute('GET', '/prenotazioni/create', ['App\Controllers\PrenotazioniController', 'create']);
	$r->addRoute('POST', '/prenotazioni/store', ['App\Controllers\PrenotazioniController', 'store']);

	$r->addRoute('GET', '/prenotazioni/edit/{id:\d+}', ['App\Controllers\PrenotazioniController', 'edit']);
	$r->addRoute('POST', '/prenotazioni/update', ['App\Controllers\PrenotazioniController', 'update']);
	$r->addRoute('GET', '/prenotazioni/delete/{id:\d+}', ['App\Controllers\PrenotazioniController', 'delete']);
	$r->addRoute('POST', '/prenotazioni/bulk-delete', ['App\Controllers\PrenotazioniController', 'bulkDelete']);

	// Rotte per Recensioni
	$r->addRoute('GET', '/recensioni', ['App\Controllers\RecensioniController', 'index']);
	$r->addRoute('GET', '/recensioni/create', ['App\Controllers\RecensioniController', 'create']);
	$r->addRoute('POST', '/recensioni/store', ['App\Controllers\RecensioniController', 'store']);

	$r->addRoute('GET', '/recensioni/edit/{id:\d+}', ['App\Controllers\RecensioniController', 'edit']);
	$r->addRoute('POST', '/recensioni/update', ['App\Controllers\RecensioniController', 'update']);
	$r->addRoute('GET', '/recensioni/delete/{id:\d+}', ['App\Controllers\RecensioniController', 'delete']);
	$r->addRoute('POST', '/recensioni/bulk-delete', ['App\Controllers\RecensioniController', 'bulkDelete']);

	// Rotte per RelazioniSaleRisorse
	$r->addRoute('GET', '/relazioni-sale-risorse', ['App\Controllers\RelazioniSaleRisorseController', 'index']);
	$r->addRoute('GET', '/relazioni-sale-risorse/create', ['App\Controllers\RelazioniSaleRisorseController', 'create']);
	$r->addRoute('POST', '/relazioni-sale-risorse/store', ['App\Controllers\RelazioniSaleRisorseController', 'store']);

	$r->addRoute('GET', '/relazioni-sale-risorse/edit/{id:\d+}', ['App\Controllers\RelazioniSaleRisorseController', 'edit']);
	$r->addRoute('POST', '/relazioni-sale-risorse/update', ['App\Controllers\RelazioniSaleRisorseController', 'update']);
	$r->addRoute('GET', '/relazioni-sale-risorse/delete/{id:\d+}', ['App\Controllers\RelazioniSaleRisorseController', 'delete']);
	$r->addRoute('POST', '/relazioni-sale-risorse/bulk-delete', ['App\Controllers\RelazioniSaleRisorseController', 'bulkDelete']);

	// Rotte per Risorse
	$r->addRoute('GET', '/risorse', ['App\Controllers\RisorseController', 'index']);
	$r->addRoute('GET', '/risorse/create', ['App\Controllers\RisorseController', 'create']);
	$r->addRoute('POST', '/risorse/store', ['App\Controllers\RisorseController', 'store']);

	$r->addRoute('GET', '/risorse/edit/{id:\d+}', ['App\Controllers\RisorseController', 'edit']);
	$r->addRoute('POST', '/risorse/update', ['App\Controllers\RisorseController', 'update']);
	$r->addRoute('GET', '/risorse/delete/{id:\d+}', ['App\Controllers\RisorseController', 'delete']);
	$r->addRoute('POST', '/risorse/bulk-delete', ['App\Controllers\RisorseController', 'bulkDelete']);

	// Rotte per Sale
	$r->addRoute('GET', '/sale', ['App\Controllers\SaleController', 'index']);
	$r->addRoute('GET', '/sale/create', ['App\Controllers\SaleController', 'create']);
	$r->addRoute('POST', '/sale/store', ['App\Controllers\SaleController', 'store']);

	$r->addRoute('GET', '/sale/edit/{id:\d+}', ['App\Controllers\SaleController', 'edit']);
	$r->addRoute('POST', '/sale/update', ['App\Controllers\SaleController', 'update']);
	$r->addRoute('GET', '/sale/delete/{id:\d+}', ['App\Controllers\SaleController', 'delete']);
	$r->addRoute('POST', '/sale/bulk-delete', ['App\Controllers\SaleController', 'bulkDelete']);

	// Rotte per Utenti
	$r->addRoute('GET', '/utenti', ['App\Controllers\UtentiController', 'index']);
	$r->addRoute('GET', '/utenti/create', ['App\Controllers\UtentiController', 'create']);
	$r->addRoute('POST', '/utenti/store', ['App\Controllers\UtentiController', 'store']);

	$r->addRoute('GET', '/utenti/edit/{id:\d+}', ['App\Controllers\UtentiController', 'edit']);
	$r->addRoute('POST', '/utenti/update', ['App\Controllers\UtentiController', 'update']);
	$r->addRoute('GET', '/utenti/delete/{id:\d+}', ['App\Controllers\UtentiController', 'delete']);
	$r->addRoute('POST', '/utenti/bulk-delete', ['App\Controllers\UtentiController', 'bulkDelete']);

};

return $routes;
