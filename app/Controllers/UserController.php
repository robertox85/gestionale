<?php

namespace App\Controllers;

use App\Libraries\Helper;
use App\Libraries\Table;
use App\Models\Anagrafica;
use App\Models\Gruppo;
use App\Models\Pratica;
use App\Models\Ruolo;
use App\Models\Utente;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->table = new Table(
            [
                [
                    'label' => 'ID',
                    'sortable' => true,
                    'sortUrl' => 'utenti',
                    'sortKey' => 'id'
                ],
                [
                    'label' => 'Nome',
                    'sortable' => true,
                    'sortUrl' => 'utenti',
                    'sortKey' => 'nome'
                ],
                [
                    'label' => 'Email',
                    'sortable' => true,
                    'sortUrl' => 'utenti',
                    'sortKey' => 'email'
                ],
                [
                    'label' => 'Gruppi',
                    'sortable' => true,
                    'sortUrl' => 'utenti',
                    'sortKey' => 'gruppi'
                ],
                [
                    'label' => 'Pratiche',
                    'sortable' => true,
                    'sortUrl' => 'utenti',
                    'sortKey' => 'pratiche'
                ],
                [
                    'label' => 'Ruolo',
                    'sortable' => true,
                    'sortUrl' => 'utenti',
                    'sortKey' => 'id_ruolo'
                ],

                [
                    'label' => 'Azioni',
                    'sortable' => false
                ],
            ]
        );

        $this->filters = [
            'ruoli' => Ruolo::getAll()
        ];
    }

    // Views
    public function utentiView()
    {
        $ruoliFilter = (isset($_POST['ruoli'])) ? $_POST['ruoli'] : [];

        if (!empty($ruoliFilter)) {
            $this->args['where']['id_ruolo'] = $ruoliFilter;
        }

        $this->buildTableRows();

        $totalItems = Utente::getTotalCount($this->args);

        echo $this->view->render('utenti.html.twig', [
            'entity' => 'utenti',
            'selectedRoles' => $ruoliFilter,
            'filters' => $this->filters,
            'headers' => $this->table->getHeaders(),
            'rows' => $this->table->getRows(),
            'pagination' => [
                'totalPages' => ceil($totalItems / $this->args['limit']), // Necessario calcolarlo in base alla tua logica di paginazione
                'totalItems' => $totalItems,
                'itemsPerPage' => $this->args['limit'],
                'currentPage' => $this->args['currentPage'],
            ],
        ]);
    }
    public function contropartiView(): void
    {
        $this->args['where']['id_ruolo'] = [6]; // imposta l'id_ruolo come 5 per le controparti
        // empty filter
        $this->filters = [];
        $this->utentiView(); // chiama il metodo utentiView
    }
    public function assistitiView(): void
    {
        $this->filters = [];
        $this->args['where']['id_ruolo'] = [5]; // imposta l'id_ruolo come 4 per gli assistiti
        $this->utentiView(); // chiama il metodo utentiView
    }
    public function utenteView($id): void
    {
        // Show Pratiche Table if Gruppi has Pratiche
        $utente = new Utente($id);
        $gruppi = $utente->getGruppi();
        $hasPratiche = false;
        $pratiche = [];
        foreach ($gruppi as $gruppo) {
            $gruppo = new Gruppo($gruppo->id);
            if ($gruppo->getPratiche() !== false && !empty($gruppo->getPratiche())) {
                $hasPratiche = true;
                $pratiche[] = $gruppo->getPratiche();
            }
        }

        if ($hasPratiche) {
            $table = new Table(
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
            $this->args['where']['id_utente'] = $id;
            foreach ($pratiche as $pratica) {
                $pratica = new Pratica($pratica[0]);
                $nrPratica = $pratica->getNrPratica();
                $table->addRow(
                    [
                        'cells' => [
                            ['content' => $pratica->getNrPratica()],
                            ['content' => $pratica->getGruppoObj()->getNome()],
                            ['content' => $pratica->getNome()],
                            ['content' => $pratica->getStato()],
                            ['content' => $this->createEditCell($pratica)]
                        ]
                    ]
                );
            }
        }


        echo $this->view->render('editUtente.html.twig', [
            'utente' => new Utente($id),
            'ruoli' => Ruolo::getAll(),
            'gruppi' => Gruppo::getAll(),
            'headers' => ($hasPratiche) ? $table->getHeaders() : [],
            'rows' => ($hasPratiche) ? $table->getRows() : [],
            'hasPratiche' => $hasPratiche
        ]);
    }
    public function utenteCreaView(): void
    {
        echo $this->view->render('creaUtente.html.twig', [
            'ruoli' => Ruolo::getAll(),
            'gruppi' => Gruppo::getAll()
        ]);
    }

    // Actions
    public function editUtente(): void
    {
        $utente = new Utente($_POST['id_utente']);
        $data = $utente->sanificaInput($_POST, ['password', 'password_confirm']);
        $update = $utente->preparaAggiornamento($data);
        if ($update === true) {
            $utente->update();
            Helper::addSuccess('Utente aggiornato con successo');
            $this->redirectToReferer();
            exit;
        } else {
            $errors = $utente->getErrors();
            foreach ($errors as $item) {
                Helper::addError($item);
            }
            header('Location: /utenti/edit/' . $utente->getId());
            exit;
        }
    }
    public function deleteUtente($id): void
    {
        $this->deleteUserAndRelatedRecords($id);
        $this->redirectToReferer();
    }
    public function createUtente(): void
    {
        $utente = $this->creaUtente($_POST);


        if (!$utente) {
            header('Location: /utenti/crea');
            return;
        }

        $anagrafica = $this->creaAnagrafica($utente->getId(), $_POST);

        if (!$anagrafica) {
            header('Location: /utenti/crea');
            return;
        }

        $utente->aggiornaAssociazioniGruppo($_POST['gruppi'], $utente->getId());

        Helper::addSuccess('Utente creato con successo');
        $this->redirectToReferer();
        //header('Location: /utenti');
    }

    // Private methods
    private function deleteUserAndRelatedRecords($id): void
    {
        $utente = new Utente($id);
        if ($utente->getAnagrafica() !== false) {
            $utente->getAnagrafica()->delete();
        }
        Gruppo::removeRecordFromUtentiGruppiByUtenteId($id);
        $utente->delete();
    }
    private function creaAnagrafica($utente_id, array $data): Anagrafica|bool
    {
        $anagrafica = new Anagrafica();
        $anagrafica->setIdUtente($utente_id);
        $anagrafica->setProprieta($data);

        $anagrafica_id = $anagrafica->save();
        if (!$anagrafica_id) {
            Helper::addError('Errore nella creazione dell\'anagrafica');
            return false;
        }

        return $anagrafica;
    }
    private function creaUtente(array $data): Utente|bool
    {
        $utente = new Utente();
        $data = $utente->sanificaInput($data, ['password', 'password_confirm']);
        $utente->setEmail($data['email']);
        $utente->setUsername($data['username']);
        $utente->setIdRuolo($data['ruolo']);

        if (!$utente->controllaEsettaPassword($data)) {
            return false;
        }

        $utente_id = $utente->save();

        if (!$utente_id) {
            Helper::addError('Errore nella creazione dell\'utente');
            return false;
        }

        return $utente;
    }
    private function redirectToReferer(): void
    {
        $redirectPaths = [
            'controparti' => '/controparti',
            'assistiti' => '/assistiti'
        ];

        foreach ($redirectPaths as $key => $path) {
            if (strpos($_SERVER['HTTP_REFERER'], $key) !== false) {
                header('Location: ' . $path);
                exit;
            }
        }

        header('Location: /utenti');
        exit;
    }
    private function createUtenteRow(Utente $utente): array
    {
        return [
            'cells' => [
                ['content' => $utente->getId()],
                ['content' => $utente->getAnagrafica()->getNome() . ' ' . $utente->getAnagrafica()->getCognome() . ' ' . $utente->getAnagrafica()->getDenominazione()],
                ['content' => $utente->getEmail()],
                ['content' => $utente->getGruppiCount()],
                ['content' => $utente->getPraticheCount()],
                ['content' => $utente->getRuoloObj()->getNome()],
                ['content' => $this->createActionsCell($utente)],
            ]
        ];
    }
    private function buildTableRows(): void
    {
        $utenti = Utente::getAllUtenti($this->args);

        foreach ($utenti as $utente) {
            $this->table->addRow($this->createUtenteRow($utente));
        }

    }

}