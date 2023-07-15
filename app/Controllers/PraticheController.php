<?php

namespace App\Controllers;

use App\Libraries\Database;
use App\Libraries\Helper;
use App\Libraries\Table;
use App\Models\Gruppo;
use App\Models\Nota;
use App\Models\Pratica;
use App\Models\Scadenza;
use App\Models\Udienza;
use App\Models\Utente;
use App\Services\PraticaService;

class PraticheController extends BaseController
{
    protected $praticaService;

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
        $this->praticaService = new PraticaService(new Pratica());
    }

    // Views
    public function praticheView()
    {
        $this->buildTableRows();

        // where pratiche is_deleted = 0
        $totalItems = Pratica::getTotalCount([
            'where' => [
                'is_deleted' => ['value' => 0, 'operator' => '=']
            ]
        ]);

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
        try {
            $this->praticaService->createPratica();
            $this->praticaService->createAndSaveNrPratica();
            $this->praticaService->saveScadenze();
            $this->praticaService->saveUdienze();
            $this->praticaService->saveNote();

            Helper::addSuccess('Pratica creata con successo');
            header('Location: /pratiche');
            exit();
        } catch (\Exception $e) {
            Helper::addError($e->getMessage());
            header('Location: /pratiche');
            exit();
        }
    }

    public function editPratica()
    {
        try {
            $pratica = new Pratica($_POST['id_pratica']);
            $oldGruppo = $pratica->getIdGruppo();
            $this->praticaService = new PraticaService($pratica);
            if($oldGruppo != $_POST['id_gruppo']) {
                $newNrPratica = Pratica::generateNrPratica($_POST['id_gruppo']);
                $this->praticaService->updateNrPratica($newNrPratica);
            }
            $this->praticaService->updatePratica();
            $this->praticaService->updateScadenze();
            $this->praticaService->updateUdienze();
            $this->praticaService->updateNote();

            Helper::addSuccess('Pratica aggiornata con successo');
            header('Location: /pratiche');
            exit();
        } catch (\Exception $e) {
            Helper::addError($e->getMessage());
            header('Location: /pratiche');
            exit();
        }
    }
    public function deletePratica(int $id_pratica)
    {
        $pratica = new Pratica($id_pratica);
        try {
            $pratica->delete();
            Helper::addSuccess('Pratica eliminata con successo');
        } catch (\Exception $e) {
            Helper::addError('Errore durante l\'eliminazione della pratica ' . $e->getMessage());
        }


        // Reindirizzare l'utente alla pagina di visualizzazione della pratica appena creata
        header("Location: /pratiche");
        exit();
    }

    // Private methods
    private function buildTableRows(array $pratiche = []): void
    {
        if (empty($pratiche)) {
            // override $this->args['sort'] if is equal to id with nr_pratica
            if ($this->args['sort'] == 'id') {
                $this->args['sort'] = 'nr_pratica';
            }
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


}