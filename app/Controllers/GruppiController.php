<?php

namespace App\Controllers;

use App\Libraries\Helper;
use App\Models\Gruppo;

use App\Libraries\Database;

class GruppiController extends BaseController
{
    public function gruppiView()
    {
        $gruppi = Gruppo::getAll();
        $gruppi = array_map(function ($gruppo) {
            $gruppo = new Gruppo($gruppo->id);
            return $gruppo;
        }, $gruppi);



        echo $this->view->render('gruppi.html.twig',
            [
                'gruppi' => $gruppi,
            ]
        );
    }

    public function creaGruppoView()
    {
        echo $this->view->render('creaGruppo.html.twig');
    }

    // editGruppoView
    public function editGruppoView(int $id_gruppo)
    {
        $gruppo = new Gruppo($id_gruppo);
        echo $this->view->render('editGruppo.html.twig',
            [
                'gruppo' => $gruppo
            ]
        );
    }

    // creaGruppo POST
    public function creaGruppo()
    {
        try {
            // Validazione dei dati
            $nomeGruppo = $_POST['nome_gruppo'];
            $gruppo = new Gruppo();
            $gruppo->setNome($nomeGruppo);
            $gruppo->save();

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


}