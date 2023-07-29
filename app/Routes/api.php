<?php

use FastRoute\RouteCollector;
// Configurazione delle rotte e dei middleware con MiddlewareStack

return function (RouteCollector $r) {

	// Rotte pubbliche
	$r->addRoute('GET', '/api/v1/403', ['App\Controllers\ErrorController', 'forbiddenView']);
	$r->addRoute('GET', '/api/v1/404', ['App\Controllers\ErrorController', 'notFoundView']);
	$r->addRoute('GET', '/api/v1/405', ['App\Controllers\ErrorController', 'notAllowedView']);
	$r->addRoute('GET', '/api/v1/500', ['App\Controllers\ErrorController', 'internalErrorView']);

	$r->addRoute('GET', '/api/v1', ['App\Controllers\HomeController', 'home']);
	// Rotte per DisponibilitaSale
	$r->addRoute('GET', '/api/v1/disponibilita-sale', ['App\Controllers\DisponibilitaSaleApiController', 'index']);
	$r->addRoute('GET', '/api/v1/disponibilita-sale/create', ['App\Controllers\DisponibilitaSaleApiController', 'create']);
	$r->addRoute('POST', '/api/v1/disponibilita-sale/store', ['App\Controllers\DisponibilitaSaleApiController', 'store']);

	$r->addRoute('GET', '/api/v1/disponibilita-sale/edit/{id:\d+}', ['App\Controllers\DisponibilitaSaleApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/disponibilita-sale/update', ['App\Controllers\DisponibilitaSaleApiController', 'update']);
	$r->addRoute('GET', '/api/v1/disponibilita-sale/delete/{id:\d+}', ['App\Controllers\DisponibilitaSaleApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/disponibilita-sale/bulk-delete', ['App\Controllers\DisponibilitaSaleApiController', 'bulkDelete']);

	// Rotte per EccezioniSale
	$r->addRoute('GET', '/api/v1/eccezioni-sale', ['App\Controllers\EccezioniSaleApiController', 'index']);
	$r->addRoute('GET', '/api/v1/eccezioni-sale/create', ['App\Controllers\EccezioniSaleApiController', 'create']);
	$r->addRoute('POST', '/api/v1/eccezioni-sale/store', ['App\Controllers\EccezioniSaleApiController', 'store']);

	$r->addRoute('GET', '/api/v1/eccezioni-sale/edit/{id:\d+}', ['App\Controllers\EccezioniSaleApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/eccezioni-sale/update', ['App\Controllers\EccezioniSaleApiController', 'update']);
	$r->addRoute('GET', '/api/v1/eccezioni-sale/delete/{id:\d+}', ['App\Controllers\EccezioniSaleApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/eccezioni-sale/bulk-delete', ['App\Controllers\EccezioniSaleApiController', 'bulkDelete']);

	// Rotte per LogOperazioniUtente
	$r->addRoute('GET', '/api/v1/log-operazioni-utente', ['App\Controllers\LogOperazioniUtenteApiController', 'index']);
	$r->addRoute('GET', '/api/v1/log-operazioni-utente/create', ['App\Controllers\LogOperazioniUtenteApiController', 'create']);
	$r->addRoute('POST', '/api/v1/log-operazioni-utente/store', ['App\Controllers\LogOperazioniUtenteApiController', 'store']);

	$r->addRoute('GET', '/api/v1/log-operazioni-utente/edit/{id:\d+}', ['App\Controllers\LogOperazioniUtenteApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/log-operazioni-utente/update', ['App\Controllers\LogOperazioniUtenteApiController', 'update']);
	$r->addRoute('GET', '/api/v1/log-operazioni-utente/delete/{id:\d+}', ['App\Controllers\LogOperazioniUtenteApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/log-operazioni-utente/bulk-delete', ['App\Controllers\LogOperazioniUtenteApiController', 'bulkDelete']);

	// Rotte per Notifiche
	$r->addRoute('GET', '/api/v1/notifiche', ['App\Controllers\NotificheApiController', 'index']);
	$r->addRoute('GET', '/api/v1/notifiche/create', ['App\Controllers\NotificheApiController', 'create']);
	$r->addRoute('POST', '/api/v1/notifiche/store', ['App\Controllers\NotificheApiController', 'store']);

	$r->addRoute('GET', '/api/v1/notifiche/edit/{id:\d+}', ['App\Controllers\NotificheApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/notifiche/update', ['App\Controllers\NotificheApiController', 'update']);
	$r->addRoute('GET', '/api/v1/notifiche/delete/{id:\d+}', ['App\Controllers\NotificheApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/notifiche/bulk-delete', ['App\Controllers\NotificheApiController', 'bulkDelete']);

	// Rotte per PreferenzeUtenteSale
	$r->addRoute('GET', '/api/v1/preferenze-utente-sale', ['App\Controllers\PreferenzeUtenteSaleApiController', 'index']);
	$r->addRoute('GET', '/api/v1/preferenze-utente-sale/create', ['App\Controllers\PreferenzeUtenteSaleApiController', 'create']);
	$r->addRoute('POST', '/api/v1/preferenze-utente-sale/store', ['App\Controllers\PreferenzeUtenteSaleApiController', 'store']);

	$r->addRoute('GET', '/api/v1/preferenze-utente-sale/edit/{id:\d+}', ['App\Controllers\PreferenzeUtenteSaleApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/preferenze-utente-sale/update', ['App\Controllers\PreferenzeUtenteSaleApiController', 'update']);
	$r->addRoute('GET', '/api/v1/preferenze-utente-sale/delete/{id:\d+}', ['App\Controllers\PreferenzeUtenteSaleApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/preferenze-utente-sale/bulk-delete', ['App\Controllers\PreferenzeUtenteSaleApiController', 'bulkDelete']);

	// Rotte per Prenotazioni
	$r->addRoute('GET', '/api/v1/prenotazioni', ['App\Controllers\PrenotazioniApiController', 'index']);
	$r->addRoute('GET', '/api/v1/prenotazioni/create', ['App\Controllers\PrenotazioniApiController', 'create']);
	$r->addRoute('POST', '/api/v1/prenotazioni/store', ['App\Controllers\PrenotazioniApiController', 'store']);

	$r->addRoute('GET', '/api/v1/prenotazioni/edit/{id:\d+}', ['App\Controllers\PrenotazioniApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/prenotazioni/update', ['App\Controllers\PrenotazioniApiController', 'update']);
	$r->addRoute('GET', '/api/v1/prenotazioni/delete/{id:\d+}', ['App\Controllers\PrenotazioniApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/prenotazioni/bulk-delete', ['App\Controllers\PrenotazioniApiController', 'bulkDelete']);

	// Rotte per Recensioni
	$r->addRoute('GET', '/api/v1/recensioni', ['App\Controllers\RecensioniApiController', 'index']);
	$r->addRoute('GET', '/api/v1/recensioni/create', ['App\Controllers\RecensioniApiController', 'create']);
	$r->addRoute('POST', '/api/v1/recensioni/store', ['App\Controllers\RecensioniApiController', 'store']);

	$r->addRoute('GET', '/api/v1/recensioni/edit/{id:\d+}', ['App\Controllers\RecensioniApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/recensioni/update', ['App\Controllers\RecensioniApiController', 'update']);
	$r->addRoute('GET', '/api/v1/recensioni/delete/{id:\d+}', ['App\Controllers\RecensioniApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/recensioni/bulk-delete', ['App\Controllers\RecensioniApiController', 'bulkDelete']);

	// Rotte per RelazioniSaleRisorse
	$r->addRoute('GET', '/api/v1/relazioni-sale-risorse', ['App\Controllers\RelazioniSaleRisorseApiController', 'index']);
	$r->addRoute('GET', '/api/v1/relazioni-sale-risorse/create', ['App\Controllers\RelazioniSaleRisorseApiController', 'create']);
	$r->addRoute('POST', '/api/v1/relazioni-sale-risorse/store', ['App\Controllers\RelazioniSaleRisorseApiController', 'store']);

	$r->addRoute('GET', '/api/v1/relazioni-sale-risorse/edit/{id:\d+}', ['App\Controllers\RelazioniSaleRisorseApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/relazioni-sale-risorse/update', ['App\Controllers\RelazioniSaleRisorseApiController', 'update']);
	$r->addRoute('GET', '/api/v1/relazioni-sale-risorse/delete/{id:\d+}', ['App\Controllers\RelazioniSaleRisorseApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/relazioni-sale-risorse/bulk-delete', ['App\Controllers\RelazioniSaleRisorseApiController', 'bulkDelete']);

	// Rotte per Risorse
	$r->addRoute('GET', '/api/v1/risorse', ['App\Controllers\RisorseApiController', 'index']);
	$r->addRoute('GET', '/api/v1/risorse/create', ['App\Controllers\RisorseApiController', 'create']);
	$r->addRoute('POST', '/api/v1/risorse/store', ['App\Controllers\RisorseApiController', 'store']);

	$r->addRoute('GET', '/api/v1/risorse/edit/{id:\d+}', ['App\Controllers\RisorseApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/risorse/update', ['App\Controllers\RisorseApiController', 'update']);
	$r->addRoute('GET', '/api/v1/risorse/delete/{id:\d+}', ['App\Controllers\RisorseApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/risorse/bulk-delete', ['App\Controllers\RisorseApiController', 'bulkDelete']);

	// Rotte per Sale
	$r->addRoute('GET', '/api/v1/sale', ['App\Controllers\SaleApiController', 'index']);
	$r->addRoute('GET', '/api/v1/sale/create', ['App\Controllers\SaleApiController', 'create']);
	$r->addRoute('POST', '/api/v1/sale/store', ['App\Controllers\SaleApiController', 'store']);

	$r->addRoute('GET', '/api/v1/sale/edit/{id:\d+}', ['App\Controllers\SaleApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/sale/update', ['App\Controllers\SaleApiController', 'update']);
	$r->addRoute('GET', '/api/v1/sale/delete/{id:\d+}', ['App\Controllers\SaleApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/sale/bulk-delete', ['App\Controllers\SaleApiController', 'bulkDelete']);

	// Rotte per Utenti
	$r->addRoute('GET', '/api/v1/utenti', ['App\Controllers\UtentiApiController', 'index']);
	$r->addRoute('GET', '/api/v1/utenti/create', ['App\Controllers\UtentiApiController', 'create']);
	$r->addRoute('POST', '/api/v1/utenti/store', ['App\Controllers\UtentiApiController', 'store']);

	$r->addRoute('GET', '/api/v1/utenti/edit/{id:\d+}', ['App\Controllers\UtentiApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/utenti/update', ['App\Controllers\UtentiApiController', 'update']);
	$r->addRoute('GET', '/api/v1/utenti/delete/{id:\d+}', ['App\Controllers\UtentiApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/utenti/bulk-delete', ['App\Controllers\UtentiApiController', 'bulkDelete']);

};

