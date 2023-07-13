<?php

namespace App\Controllers;

use App\Libraries\Helper;
use App\Models\Anagrafica;
use App\Models\Gruppo;
use App\Models\Pratica;
use App\Models\Ruolo;
use App\Models\Utente;
class UserController extends BaseController
{
    private function getUtentiSortedBy(array $args, callable $sortCallback, callable $mapCallback): array
    {
        $args['sort'] = 'id';
        $utenti = Utente::getAll($args);

        usort($utenti, $sortCallback);

        $utenti = array_map($mapCallback, $utenti);

        return $utenti;
    }
    private function sortCallbackByMethod(string $method): callable
    {
        return function ($a, $b) use ($method) {
            $a = new Utente($a->getId());
            $b = new Utente($b->getId());
            $a = $a->$method();
            $b = $b->$method();
            if (empty($a) && empty($b)) return 0;
            if (empty($a)) return 1;
            if (empty($b)) return -1;
            return $a[0]->getId() - $b[0]->getId();
        };
    }
    private function getUtentiSortedByGruppi(array $args): array
    {
        $sortCallback = $this->sortCallbackByMethod('getGruppi');

        $mapCallback = function ($utente) {
            return new Utente($utente->getId());
        };

        return $this->getUtentiSortedBy($args, $sortCallback, $mapCallback);
    }
    private function getUtentiSortedByPratiche(array $args): array
    {
        $sortCallback = $this->sortCallbackByMethod('getPratiche');

        $mapCallback = function ($utente) {
            return new Utente($utente->getId());
        };

        return $this->getUtentiSortedBy($args, $sortCallback, $mapCallback);
    }
    private function getUtentiSortedByNome(array $args): array
    {
        $direction = $args['order'] ?? 'asc';
        $sortCallback = $direction === 'asc'
            ? function ($a, $b) {
                return strcmp($a->getAnagrafica()->getNome(), $b->getAnagrafica()->getNome());
            }
            : function ($a, $b) {
                return strcmp($b->getAnagrafica()->getNome(), $a->getAnagrafica()->getNome());
            };

        return $this->getUtentiSortedBy($args, $sortCallback, [$this, 'mapUtente']);
    }
    private function mapUtente(Utente $utente): Utente
    {
        $ruolo = $utente->getRuolo()->getNome();
        $utente->setRuolo(strtolower($ruolo));
        return $utente;
    }
    public function renderViewWithSorting($defaultSortFunction): void
    {
        $args = $this->createViewArgs();
        $sortFunctions = $this->getSortFunctions();
        $sortFunction = $sortFunctions[$args['sort']] ?? $defaultSortFunction;
        $this->renderUtentiViewWithSortFunction($sortFunction, $args);
    }
    private function renderUtentiViewWithSortFunction(array $sortFunction, array $args): void
    {
        $utenti = call_user_func($sortFunction, $args);

        $totalItems = Utente::getTotalCount();
        $totalPages = ceil($totalItems / $args['limit']);

        echo $this->view->render('utenti.html.twig', [
            'utenti' => $utenti,
            'entity' => 'utenti',
            'pagination' => [
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'itemsPerPage' => $args['limit'],
                'currentPage' => $args['currentPage'],
            ],
            'headers' => $this->getUtentiTableHeader(),
            'rows' => $this->getUtentiTableRows($utenti),
            'filters' => $this->getFilters()
        ]);
    }
    public function getSortFunctions()
    {
        return [
            'nome' => [$this, 'getUtentiSortedByNome'],
            'gruppi' => [$this, 'getUtentiSortedByGruppi'],
            'pratiche' => [$this, 'getUtentiSortedByPratiche'],
        ];
    }
    public function utentiView(): void
    {
        $this->renderViewWithSorting([Utente::class, 'getAll']);
    }
    public function contropartiView(): void
    {
        $this->renderViewWithSorting([Utente::class, 'getControparti']);
    }
    public function assistitiView(): void
    {
        $this->renderViewWithSorting([Utente::class, 'getAssistiti']);
    }
    public function utenteView($id): void
    {
        echo $this->view->render('editUtente.html.twig', [
            'utente' => new Utente($id),
            'ruoli' => Ruolo::getAll(),
            'gruppi' => Gruppo::getAll()
        ]);
    }
    public function editUtente(): void
    {
        $utente = new Utente($_POST['id_utente']);
        $dati = $this->sanificaInput($_POST, ['password', 'password_confirm']);
        $result = $utente->preparaAggiornamento($dati);
        if ($result === true) {
            $utente->update();
            Helper::addSuccess('Utente aggiornato con successo');
            header('Location: /utenti');
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
    public function deleteUtente($id)
    {
        $this->deleteUserAndRelatedRecords($id);
        $this->redirectToReferer();
    }

    private function deleteUserAndRelatedRecords($id)
    {
        $utente = new Utente($id);
        if ($utente->getAnagrafica() !== false) {
            $utente->getAnagrafica()->delete();
        }
        Gruppo::removeRecordFromUtentiGruppiByUtenteId($id);
        $utente->delete();
    }

    private function redirectToReferer()
    {
        $redirectPaths = [
            'controparti' => '/controparti',
            'assistiti' => '/assistiti'
        ];

        foreach($redirectPaths as $key => $path) {
            if (strpos($_SERVER['HTTP_REFERER'], $key) !== false) {
                header('Location: ' . $path);
                exit;
            }
        }

        header('Location: /utenti');
        exit;
    }

    public function utenteCreaView()
    {
        echo $this->view->render('creaUtente.html.twig', [
            'ruoli' => Ruolo::getAll(),
            'gruppi' => Gruppo::getAll()
        ]);
    }

    public function createUtente()
    {
        $dati = $this->sanificaInput($_POST,['password','password_confirm']);
        $utente = $this->creaUtente($dati);

        if (!$utente) {
            header('Location: /utenti/crea');
            return;
        }

        $anagrafica = $this->creaAnagrafica($utente->getId(), $dati);

        if (!$anagrafica) {
            header('Location: /utenti/crea');
            return;
        }

        $utente->aggiornaAssociazioniGruppo($dati['gruppi'], $utente->getId());

        Helper::addSuccess('Utente creato con successo');
        header('Location: /utenti');
    }

    private function creaAnagrafica($utente_id, array $data)
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

    private function creaUtente(array $data)
    {
        $utente = new Utente();
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

    public function searchUtenteView($id)
    {
        $utente = Utente::getById($id);

        if (!$utente) {
            Helper::addError('Utente non trovato');
            header('Location: /utenti');
            return;
        }

        $pratiche = [];

        // foreach gruppo get pratiche
        $gruppi = $utente->getGruppi();
        foreach ($gruppi as $gruppo) {
            $pratiche = array_merge($pratiche, $gruppo->getPratiche());
        }

        $pratiche = array_unique($pratiche, SORT_REGULAR);
        $pratiche = array_map(function ($pratica) {
            $pratica = new Pratica($pratica);
            return $pratica;
        }, $pratiche);

        $headers = [
            [
                'label' => 'ID',
                'sortable' => true,
                'sortUrl' => 'pratiche',
                'sortKey' => 'id'
            ],
            [
                'label' => 'Nome',
                'sortable' => true,
                'sortUrl' => 'pratiche',
                'sortKey' => 'nome'
            ],
            [
                'label' => 'Creata il',
                'sortable' => true,
                'sortUrl' => 'pratiche',
                'sortKey' => 'data'
            ],
            [
                'label' => 'Azioni',
                'sortable' => false
            ],
        ];
        $rows = [];
        foreach ($pratiche as $pratica) {
            $rows[] = [
                'cells' => [
                    ['content' => $pratica->getId()],
                    ['content' => $pratica->getNome()],
                    ['content' => $pratica->getCreatedAt()],
                    ['content' => $this->createActionsCell($pratica)],
                ]
            ];
        }

        echo $this->view->render('utente.html.twig', [
            'utente' => $utente,
            'headers' => $headers,
            'rows' => $rows
        ]);


    }
    private function getFilters()
    {
        // get all ruoli and foreach ruolo get count utenti with that ruolo
        $ruoli = Ruolo::getAll();
        $ruoli = array_map(function ($ruolo) {
            $ruolo = new Ruolo($ruolo->getId());
            $ruolo->setCountUtenti($ruolo->getCountUtenti());
            return $ruolo;
        }, $ruoli);


        // gruppi
        $gruppi = Gruppo::getAll();
        $gruppi = array_map(function ($gruppo) {
            $gruppo = new Gruppo($gruppo->getId());
            $gruppo->setCountUtenti($gruppo->getCountUtenti());
            return $gruppo;
        }, $gruppi);


        $filters = [
            'ruoli' => $ruoli,
        ];

        return $filters;
    }
    public function utentiFilters()
    {
        $filters = $this->sanificaInput($_POST['ruoli']);


        // get all utenti with ruolo in filters
        $utenti = Utente::getAll();
        $utenti = array_filter($utenti, function ($utente) use ($filters) {
            if (empty($filters)) return true;
            return in_array($utente->getIdRuolo(), $filters);
        });

        echo $this->view->render('utenti.html.twig', [
            'filters' => $this->getFilters(),
            'totalItems' => count($utenti),
            'totalPages' => 1,
            'currentPage' => 1,
            'headers' => [
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
                    'label' => 'Ruolo',
                    'sortable' => true,
                    'sortUrl' => 'utenti',
                    'sortKey' => 'id_ruolo'
                ],

                [
                    'label' => 'Azioni',
                    'sortable' => false
                ],
            ],
            'rows' => array_map(function ($utente) {
                $utente = new Utente($utente->getId());
                return [
                    'cells' => [
                        ['content' => $utente->getId()],
                        ['content' => $utente->getAnagrafica()->getNome() . ' ' . $utente->getAnagrafica()->getCognome() . ' ' . $utente->getAnagrafica()->getDenominazione()],
                        ['content' => $utente->getEmail()],
                        ['content' => $utente->getRuolo()->getNome()],
                        ['content' => $this->createActionsCell($utente)],
                    ]
                ];
            }, $utenti)
        ]);
    }

    private function getUtentiTableHeader()
    {
        return [
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
        ];
    }
    private function getUtentiTableRows(array $utenti)
    {
        return array_map(function ($utente) {
            return [
                'cells' => [
                    ['content' => $utente->getId()],
                    ['content' => $utente->getAnagrafica()->getNome() . ' ' . $utente->getAnagrafica()->getCognome() . ' ' . $utente->getAnagrafica()->getDenominazione()],
                    ['content' => $utente->getEmail()],
                    ['content' => count($utente->getGruppi())],
                    ['content' => count($utente->getPratiche())],
                    ['content' => $utente->getRuolo()->getNome()],
                    ['content' => $this->createActionsCell($utente)],
                ]
            ];
        }, $utenti);
    }


}