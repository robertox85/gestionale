<?php

namespace App\Controllers;

use App\Models\Utente;

class UserController extends BaseController
{
    public function utentiView()
    {
        $utenti = Utente::getAll();
        $utenti = array_map(function ($utente) {
            $utente = new Utente($utente->id_utente);
            $ruolo = $utente->getRuolo();
            $ruolo = $ruolo->getNomeRuolo();
            $utente->setRuolo(strtolower($ruolo));
            return $utente->toArray();
        }, $utenti);
        $totalItems = count($utenti);
        $totalPages = ceil($totalItems / 10);
        $currentPage = 1;
        echo $this->view->render('utenti.html.twig', [
            'utenti' => $utenti,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage
        ]);
    }
}