<?php

namespace App\Controllers;

use App\Libraries\Database;
use App\Libraries\Helper;
use App\Libraries\Table;
use App\Models\Anagrafica;
use App\Models\Gruppo;
use App\Models\Nota;
use App\Models\Pratica;
use App\Models\Ruolo;
use App\Models\Scadenza;
use App\Models\Udienza;
use App\Models\Utente;

class PraticheController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->table = new Table(
            [
                [
                    'label' => 'Nr. pratica',
                    'sortable' => true,
                    'sortUrl' => 'pratiche',
                    'sortKey' => 'nr_pratica'
                ],
                [
                    'label' => 'Gruppo',
                    'sortable' => true,
                    'sortUrl' => 'pratiche',
                    'sortKey' => 'gruppo'
                ],
                [
                    'label' => 'Nome',
                    'sortable' => true,
                    'sortUrl' => 'pratiche',
                    'sortKey' => 'nome'
                ],
                [
                    'label' => 'Stato',
                    'sortable' => true,
                    'sortUrl' => 'pratiche',
                    'sortKey' => 'stato'
                ],

                [
                    'label' => 'Azioni',
                    'sortable' => false
                ],
            ]
        );
    }

    // Views
    public function praticheView()
    {

        $this->buildTableRows();

        $totalItems = Pratica::getTotalCount();

        echo $this->view->render(
            'pratiche.html.twig',
            [
                'entity' => 'pratiche',
                'headers' => $this->table->getHeaders(),
                'rows' => $this->table->getRows(),
                'pagination' => [
                    'totalPages' => ceil($totalItems / $this->args['limit']), // Necessario calcolarlo in base alla tua logica di paginazione
                    'totalItems' => $totalItems,
                    'itemsPerPage' => $this->args['limit'],
                    'currentPage' => $this->args['currentPage'],
                ],
            ]
        );
    }
    public function praticaCreaView()
    {

        echo $this->view->render(
            'creaPratica.html.twig',
            [
                'gruppi' => Gruppo::getAll(),
            ]
        );
    }
    public function miePraticheView()
    {
        $utente = Utente::getCurrentUser();
        $pratiche = $utente->getPraticheUtente();

        $this->buildTableRows($pratiche);

        $totalItems = Pratica::getTotalCount();

        echo $this->view->render(
            'pratiche.html.twig',
            [
                'entity' => 'pratiche',
                'headers' => $this->table->getHeaders(),
                'rows' => $this->table->getRows(),
                'pagination' => [
                    'totalPages' => ceil($totalItems / $this->args['limit']), // Necessario calcolarlo in base alla tua logica di paginazione
                    'totalItems' => $totalItems,
                    'itemsPerPage' => $this->args['limit'],
                    'currentPage' => $this->args['currentPage'],
                ],
            ]
        );
    }
    public function editPraticaView(int $id_pratica)
    {
        /*
        $assistiti = Utente::getAssistiti();
        $assistiti = array_map(function ($assistito) {
            return new Utente($assistito->id);
        }, $assistiti);
        $controparti = Utente::getControparti();
        $controparti = array_map(function ($controparte) {
            return new Utente($controparte->id);
        }, $controparti);
        */
        echo $this->view->render(
            'editPratica.html.twig',
            [
                'pratica' => new Pratica($id_pratica),
                'id_pratica' => $id_pratica,
                'gruppi' => Gruppo::getAll(),
                'utenti' => Utente::getAll([
                    'where' => [
                        'id_ruolo' => [6],
                        'operator' => 'NOT IN'
                    ]
                ]),
                'pratiche' => Pratica::getAll(),
            ]
        );
    }

    // Actions
    public function createPratica()
    {
        $pratica = $this->createAndSavePratica();
        $this->createAndSaveNrPratica($pratica);
        $this->createAndSaveScadenze($pratica);
        $this->createAndSaveUdienze($pratica);
        $this->createAndSaveNote($pratica);

        Helper::addSuccess('Pratica creata con successo');
        header("Location: /pratiche/edit/" . $pratica->getId());
        exit();
    }

    public function editPratica()
    {
        $pratica = new Pratica($_POST['id_pratica']);
        $this->updateAndSavePratica($pratica);
        $pratica->clearScadenze();
        $this->createAndSaveScadenze($pratica);
        $pratica->clearUdienze();
        $this->createAndSaveUdienze($pratica);
        $pratica->clearNote();
        $this->createAndSaveNote($pratica);

        Helper::addSuccess('Pratica aggiornata con successo');
        header('Location: /pratiche/edit/' . $pratica->getId());

    }
    public function deletePratica(int $id_pratica)
    {
        // Ottenere i dati inviati dal form
        $pratica = new Pratica($id_pratica);

        Database::beginTransaction();
        try {

            $pratica->deleteNote();
            $pratica->deleteUdienze();
            $pratica->deleteScadenze();

            $pratica->delete();

            Database::commit();

            Helper::addSuccess('Pratica eliminata con successo');

        } catch (\Exception $e) {
            Database::rollBack();
            echo $e->getMessage();
            header('Location: /pratiche');
        }


        // Reindirizzare l'utente alla pagina di visualizzazione della pratica appena creata
        header("Location: /pratiche");
    }

    // Private methods
    private function createAndSavePratica(): Pratica
    {
        $pratica = new Pratica();

        $fields = ['nome', 'tipologia', 'competenza', 'ruolo_generale', 'giudice', 'stato', 'id_gruppo'];
        foreach ($fields as $field) {
            $pratica->setFieldIfExistInPost($pratica, $field);
        }

        $pratica->save();

        return $pratica;
    }
    private function createAndSaveScadenze(Pratica $pratica): void
    {
        $data = $pratica->sanificaInput($_POST);
        $scadenze = $data['scadenze'];

        foreach ($scadenze as $scadenzaData) {
            $scadenza = new Scadenza();
            $scadenza->setData($scadenzaData['data']);
            $scadenza->setMotivo($scadenzaData['motivo']);
            $scadenza->setIdPratica($pratica->getId());
            $scadenza->save();
        }
    }
    private function createAndSaveUdienze(Pratica $pratica): void
    {
        $udienze = $_POST['udienze'];

        foreach ($udienze as $udienzaData) {
            $udienza = new Udienza();
            $udienza->setData($udienzaData['data']);
            $udienza->setDescrizione($udienzaData['descrizione']);
            $udienza->setIdPratica($pratica->getId());
            $udienza->save();
        }
    }
    private function buildTableRows(array $pratiche = []): void
    {
        if (empty($pratiche)) {
            $pratiche = Pratica::getAllPratiche($this->args);
        }
        foreach ($pratiche as $pratica) {
            $this->table->addRow($this->createPraticaRow($pratica));
        }

    }
    private function createPraticaRow(mixed $pratica): array
    {

        return [
            'cells' => [
                ['content' => $pratica->getNrPratica()],
                ['content' => $pratica->getGruppoObj()->getNome()],
                ['content' => $pratica->getNome()],
                ['content' => $pratica->getStato()],
                ['content' => $this->createActionsCell($pratica)],
            ]
        ];
    }

    private function createAndSaveNrPratica($pratica): void
    {
        $pratica->setNrPratica(Pratica::generateNrPratica($pratica->getIdGruppo()));
        $pratica->update();
    }

    private function updateAndSavePratica(Pratica $pratica = null): void
    {
        if (!$pratica) {
            $pratica = new Pratica($_POST['id_pratica']);
        }

        $fields = ['nome', 'tipologia', 'competenza', 'ruolo_generale', 'giudice', 'stato', 'id_gruppo'];
        foreach ($fields as $field) {
            $pratica->setFieldIfExistInPost($pratica, $field);
        }
        $pratica->update();
    }


    private function createAndSaveNote(Pratica $pratica): void
    {
        foreach ($_POST['note'] as $notaData) {
            $nota = new Nota();
            $nota->setTipologia($notaData['tipologia']);
            $nota->setDescrizione($notaData['descrizione']);
            $nota->setVisibilita($notaData['visibilita']);
            $nota->setIdPratica($pratica->getId());
            $nota->save();
        }
    }


}