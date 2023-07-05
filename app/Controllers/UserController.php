<?php

namespace App\Controllers;

use App\Libraries\Helper;
use App\Models\Ruolo;
use App\Models\Utente;

class UserController extends BaseController
{
    public function utentiView()
    {
        $utenti = Utente::getAll();
        $utenti = array_map(function ($utente) {
            $utente = new Utente($utente->id);
            $ruolo = $utente->getRuolo();
            $ruolo = $ruolo->getNome();
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

    public function utenteView($id)
    {
        $utente = new Utente($id);
        //$utente = $utente->toArray();
        echo $this->view->render('editUtente.html.twig', [
            'utente' => $utente,
            'ruoli' => Ruolo::getAll()
        ]);
    }

    public function editUtente()
    {
        $id = $_POST['id_utente'];
        $utente = new Utente($id);
        $utente->setEmail($_POST['email']);

        if ($_POST['password'] != '' && $_POST['password'] != $_POST['password_confirm']) {
            Helper::addError('Le password non coincidono');
            header('Location: /utenti/edit/' . $id);
            return;
        }

        if ($_POST['password'] != '' && $_POST['password'] == $_POST['password_confirm']) {
            if (Utente::isValidPassword($_POST['password'])) {
                $utente->setPassword($_POST['password']);
            } else {
                Helper::addError('La password deve contenere almeno 8 caratteri, una lettera maiuscola, una lettera minuscola, un numero e un carattere speciale');
                header('Location: /utenti/edit/' . $id);
                return;
            }
        }

        $utente->setIdRuolo($_POST['ruolo']);

        // Anagrafica ha come parametri: nome, cognome, indirizzo, cap, citta provincia, telefono, cellulare, pec, codice_fiscale, partita_iva, note e id_utente.
        $anagrafica = $utente->getAnagrafica();
        $anagrafica->setNome($_POST['nome']);
        $anagrafica->setCognome($_POST['cognome']);
        $anagrafica->setIndirizzo($_POST['indirizzo']);
        $anagrafica->setCap($_POST['cap']);
        $anagrafica->setCitta($_POST['citta']);
        $anagrafica->setProvincia($_POST['provincia']);
        $anagrafica->setTelefono($_POST['telefono']);
        $anagrafica->setCellulare($_POST['cellulare']);
        $anagrafica->setPec($_POST['pec']);
        $anagrafica->setCodiceFiscale($_POST['codice_fiscale']);
        $anagrafica->setPartitaIva($_POST['partita_iva']);
        $anagrafica->setNote($_POST['note']);

        try {
            $utente->update();
            $anagrafica->update();
            Helper::addSuccess('Utente modificato con successo');
        } catch (\Exception $e) {
            Helper::addError($e->getMessage());
        }

        header('Location: /utenti/edit/' . $id);
    }
}