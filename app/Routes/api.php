<?php

use FastRoute\RouteCollector;
// Configurazione delle rotte e dei middleware con MiddlewareStack

return function (RouteCollector $r) {

	// Rotte pubbliche
	$r->addRoute('GET', '/api/v1/403', ['App\Controllers\Api\ErrorController', 'forbiddenView']);
	$r->addRoute('GET', '/api/v1/404', ['App\Controllers\Api\ErrorController', 'notFoundView']);
	$r->addRoute('GET', '/api/v1/405', ['App\Controllers\Api\ErrorController', 'notAllowedView']);
	$r->addRoute('GET', '/api/v1/500', ['App\Controllers\Api\ErrorController', 'internalErrorView']);

	$r->addRoute('GET', '/api/v1/', ['App\Controllers\Api\HomeController', 'home']);
	// Rotte per DisponibilitaSale
	$r->addRoute('GET', '/api/v1/disponibilita-sale', ['App\Controllers\Api\DisponibilitaSaleApiController', 'index']);
	$r->addRoute('GET', '/api/v1/disponibilita-sale/create', ['App\Controllers\Api\DisponibilitaSaleApiController', 'create']);
	$r->addRoute('POST', '/api/v1/disponibilita-sale/store', ['App\Controllers\Api\DisponibilitaSaleApiController', 'store']);

	$r->addRoute('GET', '/api/v1/disponibilita-sale/edit/{id:\d+}', ['App\Controllers\Api\DisponibilitaSaleApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/disponibilita-sale/update', ['App\Controllers\Api\DisponibilitaSaleApiController', 'update']);
	$r->addRoute('GET', '/api/v1/disponibilita-sale/delete/{id:\d+}', ['App\Controllers\Api\DisponibilitaSaleApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/disponibilita-sale/bulk-delete', ['App\Controllers\Api\DisponibilitaSaleApiController', 'bulkDelete']);

	// Rotte per EccezioniSale
	$r->addRoute('GET', '/api/v1/eccezioni-sale', ['App\Controllers\Api\EccezioniSaleApiController', 'index']);
	$r->addRoute('GET', '/api/v1/eccezioni-sale/create', ['App\Controllers\Api\EccezioniSaleApiController', 'create']);
	$r->addRoute('POST', '/api/v1/eccezioni-sale/store', ['App\Controllers\Api\EccezioniSaleApiController', 'store']);

	$r->addRoute('GET', '/api/v1/eccezioni-sale/edit/{id:\d+}', ['App\Controllers\Api\EccezioniSaleApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/eccezioni-sale/update', ['App\Controllers\Api\EccezioniSaleApiController', 'update']);
	$r->addRoute('GET', '/api/v1/eccezioni-sale/delete/{id:\d+}', ['App\Controllers\Api\EccezioniSaleApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/eccezioni-sale/bulk-delete', ['App\Controllers\Api\EccezioniSaleApiController', 'bulkDelete']);

	// Rotte per LogOperazioniUtente
	$r->addRoute('GET', '/api/v1/log-operazioni-utente', ['App\Controllers\Api\LogOperazioniUtenteApiController', 'index']);
	$r->addRoute('GET', '/api/v1/log-operazioni-utente/create', ['App\Controllers\Api\LogOperazioniUtenteApiController', 'create']);
	$r->addRoute('POST', '/api/v1/log-operazioni-utente/store', ['App\Controllers\Api\LogOperazioniUtenteApiController', 'store']);

	$r->addRoute('GET', '/api/v1/log-operazioni-utente/edit/{id:\d+}', ['App\Controllers\Api\LogOperazioniUtenteApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/log-operazioni-utente/update', ['App\Controllers\Api\LogOperazioniUtenteApiController', 'update']);
	$r->addRoute('GET', '/api/v1/log-operazioni-utente/delete/{id:\d+}', ['App\Controllers\Api\LogOperazioniUtenteApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/log-operazioni-utente/bulk-delete', ['App\Controllers\Api\LogOperazioniUtenteApiController', 'bulkDelete']);

	// Rotte per Notifiche
	$r->addRoute('GET', '/api/v1/notifiche', ['App\Controllers\Api\NotificheApiController', 'index']);
	$r->addRoute('GET', '/api/v1/notifiche/create', ['App\Controllers\Api\NotificheApiController', 'create']);
	$r->addRoute('POST', '/api/v1/notifiche/store', ['App\Controllers\Api\NotificheApiController', 'store']);

	$r->addRoute('GET', '/api/v1/notifiche/edit/{id:\d+}', ['App\Controllers\Api\NotificheApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/notifiche/update', ['App\Controllers\Api\NotificheApiController', 'update']);
	$r->addRoute('GET', '/api/v1/notifiche/delete/{id:\d+}', ['App\Controllers\Api\NotificheApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/notifiche/bulk-delete', ['App\Controllers\Api\NotificheApiController', 'bulkDelete']);

	// Rotte per PreferenzeUtenteSale
	$r->addRoute('GET', '/api/v1/preferenze-utente-sale', ['App\Controllers\Api\PreferenzeUtenteSaleApiController', 'index']);
	$r->addRoute('GET', '/api/v1/preferenze-utente-sale/create', ['App\Controllers\Api\PreferenzeUtenteSaleApiController', 'create']);
	$r->addRoute('POST', '/api/v1/preferenze-utente-sale/store', ['App\Controllers\Api\PreferenzeUtenteSaleApiController', 'store']);

	$r->addRoute('GET', '/api/v1/preferenze-utente-sale/edit/{id:\d+}', ['App\Controllers\Api\PreferenzeUtenteSaleApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/preferenze-utente-sale/update', ['App\Controllers\Api\PreferenzeUtenteSaleApiController', 'update']);
	$r->addRoute('GET', '/api/v1/preferenze-utente-sale/delete/{id:\d+}', ['App\Controllers\Api\PreferenzeUtenteSaleApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/preferenze-utente-sale/bulk-delete', ['App\Controllers\Api\PreferenzeUtenteSaleApiController', 'bulkDelete']);

	// Rotte per Prenotazioni
	$r->addRoute('GET', '/api/v1/prenotazioni', ['App\Controllers\Api\PrenotazioniApiController', 'index']);
	$r->addRoute('GET', '/api/v1/prenotazioni/create', ['App\Controllers\Api\PrenotazioniApiController', 'create']);
	$r->addRoute('POST', '/api/v1/prenotazioni/store', ['App\Controllers\Api\PrenotazioniApiController', 'store']);

	$r->addRoute('GET', '/api/v1/prenotazioni/edit/{id:\d+}', ['App\Controllers\Api\PrenotazioniApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/prenotazioni/update', ['App\Controllers\Api\PrenotazioniApiController', 'update']);
	$r->addRoute('GET', '/api/v1/prenotazioni/delete/{id:\d+}', ['App\Controllers\Api\PrenotazioniApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/prenotazioni/bulk-delete', ['App\Controllers\Api\PrenotazioniApiController', 'bulkDelete']);

	// Rotte per Recensioni
	$r->addRoute('GET', '/api/v1/recensioni', ['App\Controllers\Api\RecensioniApiController', 'index']);
	$r->addRoute('GET', '/api/v1/recensioni/create', ['App\Controllers\Api\RecensioniApiController', 'create']);
	$r->addRoute('POST', '/api/v1/recensioni/store', ['App\Controllers\Api\RecensioniApiController', 'store']);

	$r->addRoute('GET', '/api/v1/recensioni/edit/{id:\d+}', ['App\Controllers\Api\RecensioniApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/recensioni/update', ['App\Controllers\Api\RecensioniApiController', 'update']);
	$r->addRoute('GET', '/api/v1/recensioni/delete/{id:\d+}', ['App\Controllers\Api\RecensioniApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/recensioni/bulk-delete', ['App\Controllers\Api\RecensioniApiController', 'bulkDelete']);

	// Rotte per RelazioniSaleRisorse
	$r->addRoute('GET', '/api/v1/relazioni-sale-risorse', ['App\Controllers\Api\RelazioniSaleRisorseApiController', 'index']);
	$r->addRoute('GET', '/api/v1/relazioni-sale-risorse/create', ['App\Controllers\Api\RelazioniSaleRisorseApiController', 'create']);
	$r->addRoute('POST', '/api/v1/relazioni-sale-risorse/store', ['App\Controllers\Api\RelazioniSaleRisorseApiController', 'store']);

	$r->addRoute('GET', '/api/v1/relazioni-sale-risorse/edit/{id:\d+}', ['App\Controllers\Api\RelazioniSaleRisorseApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/relazioni-sale-risorse/update', ['App\Controllers\Api\RelazioniSaleRisorseApiController', 'update']);
	$r->addRoute('GET', '/api/v1/relazioni-sale-risorse/delete/{id:\d+}', ['App\Controllers\Api\RelazioniSaleRisorseApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/relazioni-sale-risorse/bulk-delete', ['App\Controllers\Api\RelazioniSaleRisorseApiController', 'bulkDelete']);

	// Rotte per Risorse
	$r->addRoute('GET', '/api/v1/risorse', ['App\Controllers\Api\RisorseApiController', 'index']);
	$r->addRoute('GET', '/api/v1/risorse/create', ['App\Controllers\Api\RisorseApiController', 'create']);
	$r->addRoute('POST', '/api/v1/risorse/store', ['App\Controllers\Api\RisorseApiController', 'store']);

	$r->addRoute('GET', '/api/v1/risorse/edit/{id:\d+}', ['App\Controllers\Api\RisorseApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/risorse/update', ['App\Controllers\Api\RisorseApiController', 'update']);
	$r->addRoute('GET', '/api/v1/risorse/delete/{id:\d+}', ['App\Controllers\Api\RisorseApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/risorse/bulk-delete', ['App\Controllers\Api\RisorseApiController', 'bulkDelete']);

	// Rotte per Sale
	$r->addRoute('GET', '/api/v1/sale', ['App\Controllers\Api\SaleApiController', 'index']);
	$r->addRoute('GET', '/api/v1/sale/create', ['App\Controllers\Api\SaleApiController', 'create']);
	$r->addRoute('POST', '/api/v1/sale/store', ['App\Controllers\Api\SaleApiController', 'store']);

	$r->addRoute('GET', '/api/v1/sale/edit/{id:\d+}', ['App\Controllers\Api\SaleApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/sale/update', ['App\Controllers\Api\SaleApiController', 'update']);
	$r->addRoute('GET', '/api/v1/sale/delete/{id:\d+}', ['App\Controllers\Api\SaleApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/sale/bulk-delete', ['App\Controllers\Api\SaleApiController', 'bulkDelete']);

	// Rotte per Utenti
	$r->addRoute('GET', '/api/v1/utenti', ['App\Controllers\Api\UtentiApiController', 'index']);
	$r->addRoute('GET', '/api/v1/utenti/create', ['App\Controllers\Api\UtentiApiController', 'create']);
	$r->addRoute('POST', '/api/v1/utenti/store', ['App\Controllers\Api\UtentiApiController', 'store']);

	$r->addRoute('GET', '/api/v1/utenti/edit/{id:\d+}', ['App\Controllers\Api\UtentiApiController', 'edit']);
	$r->addRoute('POST', '/api/v1/utenti/update', ['App\Controllers\Api\UtentiApiController', 'update']);
	$r->addRoute('GET', '/api/v1/utenti/delete/{id:\d+}', ['App\Controllers\Api\UtentiApiController', 'delete']);
	$r->addRoute('POST', '/api/v1/utenti/bulk-delete', ['App\Controllers\Api\UtentiApiController', 'bulkDelete']);

};

