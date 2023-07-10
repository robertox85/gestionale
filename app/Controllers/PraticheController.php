<?php

namespace App\Controllers;

use App\Libraries\Database;
use App\Libraries\Helper;
use App\Models\Anagrafica;
use App\Models\Gruppo;
use App\Models\Pratica;
use App\Models\Scadenza;
use App\Models\Udienza;
use App\Models\Utente;

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
            'pratiche.html.twig',
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
        $pratica = new Pratica($id_pratica);
        $form = $pratica->getFields();
        echo $this->view->render(
            'editPratica.html.twig',
            [
                'pratica' => new Pratica($id_pratica),
                'form' => $form,
                'id_pratica' => $id_pratica,
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


        $pratica->clearAssistiti();
        foreach ($assistiti as $assistitoData) {
            $assistito = new Utente();
            $assistitoId = $assistito->save();
            $anagrafica  = new Anagrafica();
            $anagrafica->setNome('');
            $anagrafica->setNome($assistitoData['nome']);
            $anagrafica->setCognome('');
            $anagrafica->setCognome($assistitoData['cognome']);
            $anagrafica->setDenominazione('');
            $anagrafica->setDenominazione($assistitoData['denominazione']);
            $anagrafica->setTipoUtente('');
            $anagrafica->setTipoUtente($assistitoData['tipo_utente']);
            $anagrafica->setIdUtente($assistitoId);
            $anagrafica->save();

            $pratica->addAssistito($assistito);
        }

        $pratica->clearControparti();
        foreach ($controparti as $controparteData) {
            $controparte = new Utente();
            $controparte->save();
            $controparte->getAnagrafica()->setNome($controparteData['nome']);
            $controparte->getAnagrafica()->setCognome($controparteData['cognome']);
            $controparte->getAnagrafica()->setDenominazione($controparteData['denominazione']);
            $controparte->getAnagrafica()->setTipoUtente($controparteData['tipo_utente']);
            $controparte->getAnagrafica()->save();

            $pratica->addControparte($controparte);
        }

        $pratica->clearScadenze();
        foreach ($scadenze as $scadenzaData) {
            $scadenza = new Scadenza();
            $scadenza->setData($scadenzaData['data']);
            $scadenza->setMotivo($scadenzaData['motivo']);
            $scadenza->setIdPratica($id_pratica);
            $scadenza->save();

            $pratica->addScadenza($scadenza);
        }

        $pratica->clearUdienze();
        foreach ($udienze as $udienzaData) {
            $udienza = new Udienza();
            $udienza->setData($udienzaData['data']);
            $udienza->setDescrizione($udienzaData['descrizione']);
            $udienza->setIdPratica($id_pratica);
            $udienza->save();

            $pratica->addUdienza($udienza);
        }


        $pratica->update();

        Helper::addSuccess('Pratica aggiornata con successo');

        header('Location: /pratiche/edit/' . $pratica->getId());

    }

    // praticaCreaView
    public function praticaCreaView() {

        echo $this->view->render(
            'creaPratica.html.twig',
            [
                'gruppi' => Gruppo::getAll(),
            ]
        );
    }

    public function createPratica()
    {
        // Ottenere i dati inviati dal form
        $nr_pratica = 0;
        // Creare un'istanza del modello Pratica e assegnare i valori

        $pratica = new Pratica();
        //$pratica->setNrPratica($nr_pratica);
        if (isset($_POST['nome'])) $pratica->setNome($_POST['nome']);
        if (isset($_POST['tipologia'])) $pratica->setTipologia($_POST['tipologia']);
        if (isset($_POST['avvocato'])) $pratica->setAvvocato($_POST['avvocato']);
        if (isset($_POST['referente'])) $pratica->setReferente($_POST['referente']);
        if (isset($_POST['competenza'])) $pratica->setCompetenza($_POST['competenza']);
        if (isset($_POST['ruolo_generale'])) $pratica->setRuoloGenerale($_POST['ruolo_generale']);
        if (isset($_POST['giudice'])) $pratica->setGiudice($_POST['giudice']);
        if (isset($_POST['stato'])) $pratica->setStato($_POST['stato']);
        if (isset($_POST['id_gruppo'])) $pratica->setIdGruppo($_POST['id_gruppo']);


        // Salvare la pratica nel database (ad esempio, utilizzando un'istanza di un'API di accesso al database)
        $praticaId = $pratica->save();




        // Creare o aggiornare gli assistiti, le controparti, le scadenze, le udienze associati alla pratica
        $assistiti = $_POST['assistiti'];
        $controparti = $_POST['controparti'];
        $scadenze = $_POST['scadenze'];
        $udienze = $_POST['udienze'];

        foreach ($assistiti as $assistitoData) {
            $assistito = new Utente();
            $assistito->save();
            $assistito->getAnagrafica()->setNome($assistitoData['nome']);
            $assistito->getAnagrafica()->setCognome($assistitoData['cognome']);
            $assistito->getAnagrafica()->setDenominazione($assistitoData['denominazione']);
            $assistito->getAnagrafica()->setTipoUtente($assistitoData['tipo_utente']);
            $assistito->getAnagrafica()->save();

            $pratica->addAssistito($assistito);
        }

        foreach ($controparti as $controparteData) {
            $controparte = new Utente();
            $controparte->save();
            $controparte->getAnagrafica()->setNome($controparteData['nome']);
            $controparte->getAnagrafica()->setCognome($controparteData['cognome']);
            $controparte->getAnagrafica()->setDenominazione($controparteData['denominazione']);
            $controparte->getAnagrafica()->setTipoUtente($controparteData['tipo_utente']);
            $controparte->getAnagrafica()->save();

            $pratica->addControparte($controparte);
        }


        foreach ($scadenze as $scadenzaData) {
            $scadenza = new Scadenza();
            $scadenza->setData($scadenzaData['data']);
            $scadenza->setMotivo($scadenzaData['motivo']);
            $scadenza->setIdPratica($praticaId);
            $scadenza->save();

            $pratica->addScadenza($scadenza);
        }

        foreach ($udienze as $udienzaData) {
            $udienza = new Udienza();
            $udienza->setData($udienzaData['data']);
            $udienza->setDescrizione($udienzaData['descrizione']);
            $udienza->setIdPratica($praticaId);
            $udienza->save();

            $pratica->addUdienza($udienza);
        }

        // Reindirizzare l'utente alla pagina di visualizzazione della pratica appena creata
        Helper::addSuccess('Pratica creata con successo');
        header("Location: /pratiche");

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