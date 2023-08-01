<?php

use FastRoute\RouteCollector;
// Configurazione delle rotte e dei middleware con MiddlewareStack

return function (RouteCollector $r) {

	// Rotte pubbliche
	$r->addRoute('GET', '/403', ['App\Controllers\Web\ErrorController', 'forbiddenView']);
	$r->addRoute('GET', '/404', ['App\Controllers\Web\ErrorController', 'notFoundView']);
	$r->addRoute('GET', '/405', ['App\Controllers\Web\ErrorController', 'notAllowedView']);
	$r->addRoute('GET', '/500', ['App\Controllers\Web\ErrorController', 'internalErrorView']);

	$r->addRoute('GET', '/', ['App\Controllers\Web\HomeController', 'home']);
	// Rotte per DisponibilitaSale
	$r->addRoute('GET', '/disponibilita-sale', ['App\Controllers\Web\DisponibilitaSaleController', 'index']);
	$r->addRoute('GET', '/disponibilita-sale/create', ['App\Controllers\Web\DisponibilitaSaleController', 'create']);
	$r->addRoute('POST', '/disponibilita-sale/store', ['App\Controllers\Web\DisponibilitaSaleController', 'store']);

	$r->addRoute('GET', '/disponibilita-sale/edit/{id:\d+}', ['App\Controllers\Web\DisponibilitaSaleController', 'edit']);
	$r->addRoute('POST', '/disponibilita-sale/update', ['App\Controllers\Web\DisponibilitaSaleController', 'update']);
	$r->addRoute('GET', '/disponibilita-sale/delete/{id:\d+}', ['App\Controllers\Web\DisponibilitaSaleController', 'delete']);
	$r->addRoute('POST', '/disponibilita-sale/bulk-delete', ['App\Controllers\Web\DisponibilitaSaleController', 'bulkDelete']);

	// Rotte per EccezioniSale
	$r->addRoute('GET', '/eccezioni-sale', ['App\Controllers\Web\EccezioniSaleController', 'index']);
	$r->addRoute('GET', '/eccezioni-sale/create', ['App\Controllers\Web\EccezioniSaleController', 'create']);
	$r->addRoute('POST', '/eccezioni-sale/store', ['App\Controllers\Web\EccezioniSaleController', 'store']);

	$r->addRoute('GET', '/eccezioni-sale/edit/{id:\d+}', ['App\Controllers\Web\EccezioniSaleController', 'edit']);
	$r->addRoute('POST', '/eccezioni-sale/update', ['App\Controllers\Web\EccezioniSaleController', 'update']);
	$r->addRoute('GET', '/eccezioni-sale/delete/{id:\d+}', ['App\Controllers\Web\EccezioniSaleController', 'delete']);
	$r->addRoute('POST', '/eccezioni-sale/bulk-delete', ['App\Controllers\Web\EccezioniSaleController', 'bulkDelete']);

	// Rotte per LogOperazioniUtente
	$r->addRoute('GET', '/log-operazioni-utente', ['App\Controllers\Web\LogOperazioniUtenteController', 'index']);
	$r->addRoute('GET', '/log-operazioni-utente/create', ['App\Controllers\Web\LogOperazioniUtenteController', 'create']);
	$r->addRoute('POST', '/log-operazioni-utente/store', ['App\Controllers\Web\LogOperazioniUtenteController', 'store']);

	$r->addRoute('GET', '/log-operazioni-utente/edit/{id:\d+}', ['App\Controllers\Web\LogOperazioniUtenteController', 'edit']);
	$r->addRoute('POST', '/log-operazioni-utente/update', ['App\Controllers\Web\LogOperazioniUtenteController', 'update']);
	$r->addRoute('GET', '/log-operazioni-utente/delete/{id:\d+}', ['App\Controllers\Web\LogOperazioniUtenteController', 'delete']);
	$r->addRoute('POST', '/log-operazioni-utente/bulk-delete', ['App\Controllers\Web\LogOperazioniUtenteController', 'bulkDelete']);

	// Rotte per Notifiche
	$r->addRoute('GET', '/notifiche', ['App\Controllers\Web\NotificheController', 'index']);
	$r->addRoute('GET', '/notifiche/create', ['App\Controllers\Web\NotificheController', 'create']);
	$r->addRoute('POST', '/notifiche/store', ['App\Controllers\Web\NotificheController', 'store']);

	$r->addRoute('GET', '/notifiche/edit/{id:\d+}', ['App\Controllers\Web\NotificheController', 'edit']);
	$r->addRoute('POST', '/notifiche/update', ['App\Controllers\Web\NotificheController', 'update']);
	$r->addRoute('GET', '/notifiche/delete/{id:\d+}', ['App\Controllers\Web\NotificheController', 'delete']);
	$r->addRoute('POST', '/notifiche/bulk-delete', ['App\Controllers\Web\NotificheController', 'bulkDelete']);

	// Rotte per PreferenzeUtenteSale
	$r->addRoute('GET', '/preferenze-utente-sale', ['App\Controllers\Web\PreferenzeUtenteSaleController', 'index']);
	$r->addRoute('GET', '/preferenze-utente-sale/create', ['App\Controllers\Web\PreferenzeUtenteSaleController', 'create']);
	$r->addRoute('POST', '/preferenze-utente-sale/store', ['App\Controllers\Web\PreferenzeUtenteSaleController', 'store']);

	$r->addRoute('GET', '/preferenze-utente-sale/edit/{id:\d+}', ['App\Controllers\Web\PreferenzeUtenteSaleController', 'edit']);
	$r->addRoute('POST', '/preferenze-utente-sale/update', ['App\Controllers\Web\PreferenzeUtenteSaleController', 'update']);
	$r->addRoute('GET', '/preferenze-utente-sale/delete/{id:\d+}', ['App\Controllers\Web\PreferenzeUtenteSaleController', 'delete']);
	$r->addRoute('POST', '/preferenze-utente-sale/bulk-delete', ['App\Controllers\Web\PreferenzeUtenteSaleController', 'bulkDelete']);

	// Rotte per Prenotazioni
	$r->addRoute('GET', '/prenotazioni', ['App\Controllers\Web\PrenotazioniController', 'index']);
	$r->addRoute('GET', '/prenotazioni/create', ['App\Controllers\Web\PrenotazioniController', 'create']);
	$r->addRoute('POST', '/prenotazioni/store', ['App\Controllers\Web\PrenotazioniController', 'store']);

	$r->addRoute('GET', '/prenotazioni/edit/{id:\d+}', ['App\Controllers\Web\PrenotazioniController', 'edit']);
	$r->addRoute('POST', '/prenotazioni/update', ['App\Controllers\Web\PrenotazioniController', 'update']);
	$r->addRoute('GET', '/prenotazioni/delete/{id:\d+}', ['App\Controllers\Web\PrenotazioniController', 'delete']);
	$r->addRoute('POST', '/prenotazioni/bulk-delete', ['App\Controllers\Web\PrenotazioniController', 'bulkDelete']);

	// Rotte per Recensioni
	$r->addRoute('GET', '/recensioni', ['App\Controllers\Web\RecensioniController', 'index']);
	$r->addRoute('GET', '/recensioni/create', ['App\Controllers\Web\RecensioniController', 'create']);
	$r->addRoute('POST', '/recensioni/store', ['App\Controllers\Web\RecensioniController', 'store']);

	$r->addRoute('GET', '/recensioni/edit/{id:\d+}', ['App\Controllers\Web\RecensioniController', 'edit']);
	$r->addRoute('POST', '/recensioni/update', ['App\Controllers\Web\RecensioniController', 'update']);
	$r->addRoute('GET', '/recensioni/delete/{id:\d+}', ['App\Controllers\Web\RecensioniController', 'delete']);
	$r->addRoute('POST', '/recensioni/bulk-delete', ['App\Controllers\Web\RecensioniController', 'bulkDelete']);

	// Rotte per RelazioniSaleRisorse
	$r->addRoute('GET', '/relazioni-sale-risorse', ['App\Controllers\Web\RelazioniSaleRisorseController', 'index']);
	$r->addRoute('GET', '/relazioni-sale-risorse/create', ['App\Controllers\Web\RelazioniSaleRisorseController', 'create']);
	$r->addRoute('POST', '/relazioni-sale-risorse/store', ['App\Controllers\Web\RelazioniSaleRisorseController', 'store']);

	$r->addRoute('GET', '/relazioni-sale-risorse/edit/{id:\d+}', ['App\Controllers\Web\RelazioniSaleRisorseController', 'edit']);
	$r->addRoute('POST', '/relazioni-sale-risorse/update', ['App\Controllers\Web\RelazioniSaleRisorseController', 'update']);
	$r->addRoute('GET', '/relazioni-sale-risorse/delete/{id:\d+}', ['App\Controllers\Web\RelazioniSaleRisorseController', 'delete']);
	$r->addRoute('POST', '/relazioni-sale-risorse/bulk-delete', ['App\Controllers\Web\RelazioniSaleRisorseController', 'bulkDelete']);

	// Rotte per Risorse
	$r->addRoute('GET', '/risorse', ['App\Controllers\Web\RisorseController', 'index']);
	$r->addRoute('GET', '/risorse/create', ['App\Controllers\Web\RisorseController', 'create']);
	$r->addRoute('POST', '/risorse/store', ['App\Controllers\Web\RisorseController', 'store']);

	$r->addRoute('GET', '/risorse/edit/{id:\d+}', ['App\Controllers\Web\RisorseController', 'edit']);
	$r->addRoute('POST', '/risorse/update', ['App\Controllers\Web\RisorseController', 'update']);
	$r->addRoute('GET', '/risorse/delete/{id:\d+}', ['App\Controllers\Web\RisorseController', 'delete']);
	$r->addRoute('POST', '/risorse/bulk-delete', ['App\Controllers\Web\RisorseController', 'bulkDelete']);

	// Rotte per Sale
	$r->addRoute('GET', '/sale', ['App\Controllers\Web\SaleController', 'index']);
	$r->addRoute('GET', '/sale/create', ['App\Controllers\Web\SaleController', 'create']);
	$r->addRoute('POST', '/sale/store', ['App\Controllers\Web\SaleController', 'store']);

	$r->addRoute('GET', '/sale/edit/{id:\d+}', ['App\Controllers\Web\SaleController', 'edit']);
	$r->addRoute('POST', '/sale/update', ['App\Controllers\Web\SaleController', 'update']);
	$r->addRoute('GET', '/sale/delete/{id:\d+}', ['App\Controllers\Web\SaleController', 'delete']);
	$r->addRoute('POST', '/sale/bulk-delete', ['App\Controllers\Web\SaleController', 'bulkDelete']);

	// Rotte per Utenti
	$r->addRoute('GET', '/utenti', ['App\Controllers\Web\UtentiController', 'index']);
	$r->addRoute('GET', '/utenti/create', ['App\Controllers\Web\UtentiController', 'create']);
	$r->addRoute('POST', '/utenti/store', ['App\Controllers\Web\UtentiController', 'store']);

	$r->addRoute('GET', '/utenti/edit/{id:\d+}', ['App\Controllers\Web\UtentiController', 'edit']);
	$r->addRoute('POST', '/utenti/update', ['App\Controllers\Web\UtentiController', 'update']);
	$r->addRoute('GET', '/utenti/delete/{id:\d+}', ['App\Controllers\Web\UtentiController', 'delete']);
	$r->addRoute('POST', '/utenti/bulk-delete', ['App\Controllers\Web\UtentiController', 'bulkDelete']);


};

