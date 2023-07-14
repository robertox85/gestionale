<?php

namespace App\Controllers;

use App\Libraries\Helper;
use App\Libraries\Table;
use App\Models\Gruppo;

use App\Libraries\Database;
use App\Models\Pratica;
use App\Models\Utente;

class GruppiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->table = new Table(
            [
                [
                    'label' => 'ID',
                    'sortable' => true,
                    'sortUrl' => 'gruppi',
                    'sortKey' => 'id'
                ],
                [
                    'label' => 'Nome',
                    'sortable' => true,
                    'sortUrl' => 'gruppi',
                    'sortKey' => 'nome'
                ],
                [
                    'label' => 'Utenti',
                    'sortable' => true,
                    'sortUrl' => 'gruppi',
                    'sortKey' => 'utenti',
                ],
                [
                    'label' => 'Pratiche',
                    'sortable' => true,
                    'sortUrl' => 'gruppi',
                    'sortKey' => 'pratiche'
                ],
                [
                    'label' => 'Azioni',
                    'sortable' => false
                ],
            ]
        );
    }

    // Views
    public function gruppiView(): void
    {

        $this->buildTableRows();

        $totalItems = Gruppo::getTotalCount();

        echo $this->view->render('gruppi.html.twig',
            [
                'entity' => 'gruppi',
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
    public function creaGruppoView(): void
    {
        echo $this->view->render(
            'creaGruppo.html.twig',
            [
                'utenti' => $this->getUtenti(),
            ]
        );
    }
    public function editGruppoView(int $id_gruppo): void
    {
        echo $this->view->render('editGruppo.html.twig',
            [
                'gruppo' => new Gruppo($id_gruppo),
                'utenti' => $this->getUtenti(),
                'pratiche' => Pratica::getAll()
            ]
        );
    }

    // Actions
    public function creaGruppoAjax() {

        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);
        $this->creaGruppo($data);

        return json_encode(['success' => true]);
    }
    public function creaGruppo($data = null): void
    {
        // Validazione dei dati
        if (!$data) $data = $_POST;
        $gruppo = new Gruppo();
        $data = $gruppo->sanificaInput($data);
        $gruppo->setNome($data['nome_gruppo']);

        $gruppoId = $gruppo->save();
        if($gruppoId) {
            $this->addUtentiToGruppo($gruppoId, $data['utenti']);
            Helper::addSuccess('Gruppo creato con successo');
        } else {
            Helper::addError('Errore durante la creazione del gruppo');
        }

        header('Location: /gruppi');
    }
    public function editGruppo(): void
    {
        // Creazione del gruppo
        $gruppo = new Gruppo($_POST['id_gruppo']);

        // Validazione dei dati
        $data = $gruppo->sanificaInput($_POST);

        $gruppo->setNome($data['nome_gruppo']);


        // Avvio della transazione
        Database::beginTransaction();

        // Aggiornamento del gruppo
        try {
            $gruppo->removeRecordFromUtentiGruppiByGruppoId();
            $this->addUtentiToGruppo($gruppo->getId(), $data['utenti']);

            $gruppo->removeGruppoFromPraticheByGruppoId();
            $this->addPraticheToGruppo($gruppo->getId(), $data['pratiche']);

            $gruppo->update();
        } catch (\Exception $e) {
            Helper::addError('Errore durante la modifica del gruppo ' . $e->getMessage());
            Database::rollBack();
        }


        // Conferma della transazione
        Database::commit();

        Helper::addSuccess('Gruppo modificato con successo');

        header('Location: /gruppi/');
    }
    public function deleteGruppo($id): void
    {
        try {
            $gruppo = new Gruppo($id);

            Database::beginTransaction();

            try {
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

    // Helpers
    private function addUtentiToGruppo($id, $utenti) {
        $gruppo = new Gruppo($id);
        foreach ($utenti as $id_utente) {
            $gruppo->addUtente($id_utente);
        }
        return $gruppo;
    }
    private function buildTableRows(array $gruppi = []): void
    {
        if(empty($gruppi)) {
            $gruppi = Gruppo::getAllGruppi($this->args);
        }

        foreach ($gruppi as $gruppo) {
            $this->table->addRow($this->createGruppoRow($gruppo));
        }
    }
    private function createGruppoRow(mixed $gruppo): array
    {
        return [
            'cells' => [
                ['content' => $gruppo->getId()],
                ['content' => $gruppo->getNome()],
                ['content' => $gruppo->getCountUtenti()],
                ['content' => count($gruppo->getPratiche())],
                ['content' => $this->createActionsCell($gruppo)],
            ]
        ];
    }
    private function addPraticheToGruppo(int $getId, mixed $pratiche)
    {
        foreach ($pratiche as $id_pratica) {
            $pratica = new Pratica($id_pratica);
            $pratica->setIdGruppo($getId);
            $pratica->update();
        }
    }
    private function getUtenti() {
        return Utente::getAll([
            'where' => [
                'id_ruolo' => [6],
                'operator' => 'NOT IN'
            ]
        ]);
    }
}