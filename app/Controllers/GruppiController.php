<?php

namespace App\Controllers;

use App\Libraries\Helper;
use App\Models\Gruppo;

use App\Libraries\Database;
use App\Models\Pratica;
use App\Models\Utente;

class GruppiController extends BaseController
{
    public function gruppiView()
    {
        $args = $this->createViewArgs();

        $gruppi = Gruppo::getAll($args);
        $gruppi = array_map(function ($gruppo) {
            $gruppo = new Gruppo($gruppo->id);
            return $gruppo;
        }, $gruppi);
        $totalItems = Gruppo::getAll();
        $totalItems = count($totalItems);
        $totalPages = ceil($totalItems / $args['limit']);


        echo $this->view->render('gruppi.html.twig',
            [
                'gruppi' => $gruppi,
                'entity' => 'pratiche',
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'itemsPerPage' => $args['limit'],
                'currentPage' => $args['currentPage'],
            ]
        );
    }

    public function creaGruppoView()
    {
        echo $this->view->render(
            'creaGruppo.html.twig',
            [
                'utenti' => $this->getUtenti()
            ]
        );
    }

    // editGruppoView
    public function editGruppoView(int $id_gruppo)
    {
        echo $this->view->render('editGruppo.html.twig',
            [
                'gruppo' => new Gruppo($id_gruppo),
                'utenti' => $this->getUtenti(),
                'pratiche' => Pratica::getAll()
            ]
        );
    }

    private function getUtenti() {
        $utenti= Utente::getAll();
        $utenti = array_filter($utenti, function ($utente) {
            return $utente->id_ruolo != 6;
        });
        $utenti = array_map(function ($utente) {
            return new Utente($utente->id);
        }, $utenti);
        return $utenti;
    }

    // creaGruppo POST
    public function creaGruppo()
    {
        try {
            // Validazione dei dati
            $nomeGruppo = $_POST['nome_gruppo'];
            $utenti = $_POST['utenti'];
            $gruppo = new Gruppo();
            $gruppo->setNome($nomeGruppo);

            $gruppoId = $gruppo->save();

            foreach ($utenti as $id_utente) {
                $gruppo = new Gruppo($gruppoId);
                $gruppo->addUtente($id_utente);
            }

            Helper::addSuccess('Gruppo creato con successo');

            header('Location: /gruppi');
        } catch (\Exception $e) {
            // Gestione delle eccezioni
            echo $e->getMessage();
        }
    }


    // editGruppo POST
    public function editGruppo() {
        $id = $_POST['id_gruppo'];

        // Validazione dei dati
        $nomeGruppo = $_POST['nome_gruppo'];
        $utenti = $_POST['utenti'];
        $pratiche = $_POST['pratiche'];


        // Creazione del gruppo
        $gruppo = new Gruppo($id);
        $gruppo->setNome($nomeGruppo);


        // Avvio della transazione
        Database::beginTransaction();

        // Aggiornamento del gruppo
        try {
            $gruppo->removeRecordFromUtentiGruppiByGruppoId();

            foreach ($utenti as $id_utente) {
                $gruppo->addUtente($id_utente);
            }

            $gruppo->removeGruppoFromPraticheByGruppoId();
            foreach ($pratiche as $id_pratica) {
                $pratica = new Pratica($id_pratica);
                $pratica->setIdGruppo($id);
                $pratica->update();
            }


            $gruppo->update();
        } catch (\Exception $e) {
            echo $e->getMessage();
            Database::rollBack();
        }


        // Conferma della transazione
        Database::commit();

        Helper::addSuccess('Gruppo modificato con successo');

        header('Location: /gruppi/');
    }


    // deletegruppo
    public function deleteGruppo($id)
    {
        try {
            $gruppo = new Gruppo($id);

            Database::beginTransaction();

            try{
                $gruppo->removeRecordFromUtentiGruppiByGruppoId();
                $gruppo->removeGruppoFromPraticheByGruppoId();
                $gruppo->delete();
            } catch (\Exception $e) {
                echo $e->getMessage();
                Database::rollBack();
            }

            Database::commit();

            header('Location: /gruppi');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    // viewGruppo
    public function viewGruppo($id)
    {
        $gruppo = new Gruppo($id);
        $utenti = $gruppo->getUtenti();
        $utenti = array_map(function ($utente) {
            return new Utente($utente->id);
        }, $utenti);

        $pratiche = $gruppo->getPratiche();

        echo $this->view->render('viewGruppo.html.twig',
            [
                'gruppo' => $gruppo,
                'utenti' => $utenti,
                'pratiche' => $pratiche
            ]
        );
    }


}