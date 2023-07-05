<?php

namespace App\Controllers;

use App\Libraries\Database;
use App\Models\Gruppo;
use App\Models\Pratica;

class PraticheController extends BaseController
{

    public function praticheView()
    {
        $pratiche = Pratica::getAll();
        $pratiche = array_map(function ($pratica) {
            return new Pratica($pratica->id);
        }, $pratiche);

        // if length of pratiche is 0, set default values
        if (count($pratiche) == 0) {
            $itemsPerPage = 10;
            $totalItems = 0;
            $totalPages = 0;
            $currentPage = 1;
        } else {
            $itemsPerPage = 10;
            $totalItems = count($pratiche);
            $totalPages = ceil($totalItems / $itemsPerPage);
            $currentPage = 1;
            $offset = ($currentPage - 1) * $itemsPerPage;
            $pratiche = array_slice($pratiche, $offset, $itemsPerPage);
        }


        echo $this->view->render(
            'listaPratiche.html.twig',
            [
                'totalItems' => $totalItems,
                'itemsPerPage' => $itemsPerPage,
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'pratiche' => $pratiche,
            ]
        );
        exit();
    }

    public function editPraticaView(int $id_pratica)
    {
        echo $this->view->render(
            'editPratica.html.twig',
            [
                'pratica' => new Pratica($id_pratica),
                'gruppi' => Gruppo::getAll(),
            ]
        );
    }

    //editPratica
    public function editPratica() {
        $id_pratica = $_POST['id_pratica'];
        $nome = $_POST['nome'];
        $tipologia = $_POST['tipologia'];
        $avvocato = $_POST['avvocato'];
        $referente = $_POST['referente'];
        $competenza = $_POST['competenza'];
        $ruolo_generale = $_POST['ruolo_generale'];
        $giudice = $_POST['giudice'];
        $stato = $_POST['stato'];
        $id_gruppo = $_POST['id_gruppo'];


        // Dati in relazione con altre tabelle
        $assistiti = $_POST['assistiti'];
        $controparti = $_POST['controparti'];

        $scadenze = $_POST['scadenze'];
        $udienze = $_POST['udienze'];
        $note = $_POST['note'];


        $pratica = new Pratica($id_pratica);
        $pratica->setNome($nome);
        $pratica->setTipologia($tipologia);
        $pratica->setAvvocato($avvocato);
        $pratica->setReferente($referente);
        $pratica->setCompetenza($competenza);
        $pratica->setRuoloGenerale($ruolo_generale);
        $pratica->setGiudice($giudice);
        $pratica->setStato($stato);
        $pratica->setIdGruppo($id_gruppo);

        $pratica->clearScadenze();
        foreach ($scadenze as $scadenza) {
            $pratica->addScadenza($scadenza);
        }

        $pratica->clearUdienze();
        foreach ($udienze as $udienza) {
            $pratica->addUdienza($udienza);
        }

        $pratica->clearNote();
        foreach ($note as $nota) {
            $pratica->addNota($nota);
        }


        $pratica->update();

        header('Location: /pratiche/edit/' . $pratica->getId());

    }

    // praticaCreaView
    public function praticaCreaView() {

        // create new practice and redirect to edit page
        try {
            $pratica = new Pratica();
            $pratica->setNome('Nuova pratica');

            $pratica->save();
        } catch (\Exception $e) {
            echo $e->getMessage();
            header('Location: /pratiche');
        }

        header('Location: /pratiche/edit/' . $pratica->getId());
    }

    public function createPratica()
    {
        // Ottenere i dati inviati dal form
        $nr_pratica = 0;
        $nome = $_POST['nome'];
        $tipologia = $_POST['tipologia'];
        $avvocato = $_POST['avvocato'];
        $referente = $_POST['referente'];
        $competenza = $_POST['competenza'];
        $ruolo_generale = $_POST['ruolo_generale'];
        $giudice = $_POST['giudice'];
        $stato = $_POST['stato'];
        $id_gruppo = $_POST['id_gruppo'];




        // Creare un'istanza del modello Pratica e assegnare i valori

        $pratica = new Pratica();
        //$pratica->setNrPratica($nr_pratica);
        $pratica->setNome($nome);
        $pratica->setTipologia($tipologia);
        $pratica->setAvvocato($avvocato);
        $pratica->setReferente($referente);
        $pratica->setCompetenza($competenza);
        $pratica->setRuoloGenerale($ruolo_generale);
        $pratica->setGiudice($giudice);
        $pratica->setStato($stato);
        $pratica->setIdGruppo($id_gruppo);


        // Salvare la pratica nel database (ad esempio, utilizzando un'istanza di un'API di accesso al database)
        $praticaNew = $pratica->save();



        /*
        // Creare o aggiornare gli assistiti, le controparti, le scadenze, le udienze associati alla pratica
        $assistiti = $request['assistiti'];
        $controparti = $request['controparti'];
        $scadenze = $request['scadenze'];
        $udienze = $request['udienze'];

        foreach ($assistiti as $assistitoData) {
            $assistito = new Assistiti();
            $assistito->setNome($assistitoData['nome']);
            $assistito->setCognome($assistitoData['cognome']);

            // Salvare l'assistito e la relazione con la pratica nel database
            $this->assistitiModel->create($assistito, $praticaId);
        }
        */


        // Ripetere il processo per le controparti, le scadenze e le udienze

        // Reindirizzare l'utente alla pagina di visualizzazione della pratica appena creata
        header("Location: /pratiche/edit/$praticaNew");

    }

    // deletePratica
    public function deletePratica(int $id_pratica)
    {
        // Ottenere i dati inviati dal form
        $pratica = new Pratica($id_pratica);

        // TODO: devo prima cancellare le relazioni con le altre tabelle
        Database::beginTransaction();
        try{

            $pratica->deleteNote();
            $pratica->deleteUdienze();
            $pratica->deleteScadenze();

            $pratica->delete();

            Database::commit();

        } catch (\Exception $e) {
            Database::rollBack();
            echo $e->getMessage();
            header('Location: /pratiche');
        }


        // Reindirizzare l'utente alla pagina di visualizzazione della pratica appena creata
        header("Location: /pratiche");
    }


}