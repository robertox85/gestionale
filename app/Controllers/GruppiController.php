<?php

namespace App\Controllers;

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
        // create a new group and redirect to edit page
        try {
            $gruppo = new Gruppo();
            $gruppo->setNome('Nuovo gruppo');
            $gruppo->save();
        } catch (\Exception $e) {
            echo $e->getMessage();
            header('Location: /gruppi');
        }

        header('Location: /gruppi/edit/' . $gruppo->getId());
    }

    // editGruppoView
    public function editGruppoView(int $id_gruppo)
    {
        echo $this->view->render('edit_gruppo.html.twig',
            [
                'gruppo' => new Gruppo($id_gruppo),
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
            $gruppo->clearUtenti();

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

        header('Location: /gruppi/edit/' . $id);
    }


    // deletegruppo
    public function deleteGruppo($id)
    {
        try {
            $gruppo = new Gruppo($id);

            Database::beginTransaction();

            try{
                $gruppo->clearUtenti();
                $gruppo->clearPratiche();
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