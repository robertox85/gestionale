<?php

namespace App\Controllers;

use App\Libraries\Helper;
use App\Models\Anagrafica;
use App\Models\Assistito;
use App\Models\Controparte;
use App\Models\Gruppo;
use App\Models\Ruolo;
use App\Models\Utente;

class UserController extends BaseController
{
    private function getGruppi()
    {
        $gruppi = Gruppo::getAll();
        $gruppi = array_map(function ($gruppo) {
            return new Gruppo($gruppo->id);
        }, $gruppi);
        return $gruppi;
    }

    private function getUtenti($args = [])
    {
        $utenti = Utente::getAll($args);
        $utenti = array_map(function ($utente) {
            $utente = new Utente($utente->id);
            $ruolo = $utente->getRuolo();
            $ruolo = $ruolo->getNome();
            $utente->setRuolo(strtolower($ruolo));
            return $utente;
        }, $utenti);
        return $utenti;
    }

    private function getUtentiSortedByNome($args = [])
    {
        $args['sort'] = 'id';
        $utenti = Utente::getAll($args);
        $direction = $args['order'] ?? 'asc';
        $compareFunction = $direction === 'asc'
            ? function ($a, $b) {
                $a = new Utente($a->id);
                $b = new Utente($b->id);
                return strcmp($a->getAnagrafica()->getNome(), $b->getAnagrafica()->getNome());
            }
            : function ($a, $b) {
                $a = new Utente($a->id);
                $b = new Utente($b->id);
                return strcmp($b->getAnagrafica()->getNome(), $a->getAnagrafica()->getNome());
            };
        usort($utenti, $compareFunction);
        $utenti = array_map(function ($utente) {
            $utente = new Utente($utente->id);
            $ruolo = $utente->getRuolo();
            $ruolo = $ruolo->getNome();
            $utente->setRuolo(strtolower($ruolo));
            return $utente;
        }, $utenti);
        return $utenti;
    }

    public function utentiView()
    {
        $args = $this->createViewArgs();
        $headers = [
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
        ];

        switch ($args['sort']) {
            case 'nome':
                $utenti = $this->getUtentiSortedByNome($args);
                break;
            default:
                $utenti = $this->getUtenti($args);
                break;
        }

        $totalItems = Utente::getAll();
        $totalItems = count($totalItems);
        $totalPages = ceil($totalItems / $args['limit']);


        $rows = [];
        foreach ($utenti as $utente) {
            $rows[] = [
                'cells' => [
                    ['content' => $utente->getId()],
                    ['content' => $utente->getAnagrafica()->getNome() . ' ' . $utente->getAnagrafica()->getCognome() . ' ' . $utente->getAnagrafica()->getDenominazione()],
                    ['content' => $utente->getEmail()],
                    ['content' => $utente->getRuolo()->getNome()],
                    ['content' => $this->createActionsCell($utente)],
                ]
            ];
        }

        echo $this->view->render('utenti.html.twig', [
            'utenti' => $utenti,
            'entity' => 'utenti',
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => $args['limit'],
            'currentPage' => $args['currentPage'],
            'headers' => $headers,
            'rows' => $rows,
        ]);
    }

    public function utenteView($id)
    {
        $utente = new Utente($id);

        //$utente = $utente->toArray();
        echo $this->view->render('editUtente.html.twig', [
            'utente' => $utente,
            'ruoli' => Ruolo::getAll(),
            'gruppi' => $this->getGruppi()
        ]);
    }

    public function editUtente()
    {
        $id = $_POST['id_utente'];
        $utente = new Utente($id);
        $utente->setEmail($_POST['email']);
        $utente->setIdRuolo($_POST['ruolo']);

        if ($_POST['password'] != '' && $_POST['password'] != $_POST['password_confirm']) {
            Helper::addError('Le password non coincidono');
            header('Location: /utenti/edit/' . $id);
            return;
        }

        if ($_POST['password'] != '' && $_POST['password'] == $_POST['password_confirm']) {
            if (Utente::isValidPassword($_POST['password'])) {
                $utente->setPassword($_POST['password']);
            } else {
                Helper::addError('La password deve contenere almeno 8 caratteri, una lettera maiuscola, una lettera minuscola, un numero e un carattere speciale');
                header('Location: /utenti/edit/' . $id);
                return;
            }
        }

        // Anagrafica ha come parametri: nome, cognome, indirizzo, cap, citta provincia, telefono, cellulare, pec, codice_fiscale, partita_iva, note e id_utente.
        $anagrafica = $utente->getAnagrafica();
        if ($anagrafica === false || $anagrafica === null) {
            // Gestisci l'errore qui, ad esempio potresti mostrare un messaggio di errore e interrompere l'esecuzione
            $utente->update();
            Helper::addWarning('Anagrafica non trovata per l\'utente specificato. Ho aggiornato solo i dati di login');
            header('Location: /utenti/edit/' . $id);
            return;
        }
        if (isset($_POST['nome'])) $anagrafica->setNome($_POST['nome']);
        if (isset($_POST['cognome'])) $anagrafica->setCognome($_POST['cognome']);
        if (isset($_POST['denominazione'])) $anagrafica->setDenominazione($_POST['denominazione']);
        if (isset($_POST['indirizzo'])) $anagrafica->setIndirizzo($_POST['indirizzo']);
        if (isset($_POST['cap'])) $anagrafica->setCap($_POST['cap']);
        if (isset($_POST['citta'])) $anagrafica->setCitta($_POST['citta']);
        if (isset($_POST['provincia'])) $anagrafica->setProvincia($_POST['provincia']);
        if (isset($_POST['telefono'])) $anagrafica->setTelefono($_POST['telefono']);
        if (isset($_POST['cellulare'])) $anagrafica->setCellulare($_POST['cellulare']);
        if (isset($_POST['pec'])) $anagrafica->setPec($_POST['pec']);
        if (isset($_POST['codice_fiscale'])) $anagrafica->setCodiceFiscale($_POST['codice_fiscale']);
        if (isset($_POST['partita_iva'])) $anagrafica->setPartitaIva($_POST['partita_iva']);
        if (isset($_POST['note'])) $anagrafica->setNote($_POST['note']);
        if (isset($_POST['tipo_utente'])) $anagrafica->setTipoUtente($_POST['tipo_utente']);

        Gruppo::removeRecordFromUtentiGruppiByUtenteId($id);
        foreach ($_POST['gruppi'] as $gruppo) {
            Gruppo::addRecordToUtentiGruppi($id, $gruppo);
        }

        try {
            $utente->update();
            $anagrafica->update();
            Helper::addSuccess('Utente modificato con successo');
        } catch (\Exception $e) {
            Helper::addError($e->getMessage());
        }

        header('Location: /utenti/');
    }

    public function deleteUtente($id)
    {
        $utente = new Utente($id);
        if ($utente->getAnagrafica() !== false) {
            $utente->getAnagrafica()->delete();
        }
        Assistito::removeRecordFromAssistitiByUtenteId($id);
        Controparte::removeRecordFromContropartiByUtenteId($id);
        Gruppo::removeRecordFromUtentiGruppiByUtenteId($id);
        $utente->delete();

        // if is controparti or assistiti view redirect to controparti or assistiti
        if (strpos($_SERVER['HTTP_REFERER'], 'controparti') !== false) {
            header('Location: /controparti');
            return;
        }

        if (strpos($_SERVER['HTTP_REFERER'], 'assistiti') !== false) {
            header('Location: /assistiti');
            return;
        }

        header('Location: /utenti');
    }

    public function utenteCreaView()
    {
        echo $this->view->render('creaUtente.html.twig', [
            'ruoli' => Ruolo::getAll(),
            'gruppi' => $this->getGruppi(),
        ]);
    }

    public function createUtente()
    {
        try {
            $utente = new Utente();
            $utente->setEmail($_POST['email']);
            $utente->setIdRuolo($_POST['ruolo']);

            if ($_POST['password'] != '' && $_POST['password'] != $_POST['password_confirm']) {
                Helper::addError('Le password non coincidono');
                header('Location: /utenti/crea');
                return;
            }

            if ($_POST['password'] != '' && $_POST['password'] == $_POST['password_confirm']) {
                if (Utente::isValidPassword($_POST['password'])) {
                    $utente->setPassword($_POST['password']);
                } else {
                    Helper::addError('La password deve contenere almeno 8 caratteri, una lettera maiuscola, una lettera minuscola, un numero e un carattere speciale');
                    header('Location: /utenti/crea');
                    return;
                }
            }

            $utente_id = $utente->save();
            if (!$utente_id) {
                Helper::addError('Errore nella creazione dell\'utente');
                header('Location: /utenti');
                return;
            }


            // Anagrafica ha come parametri: nome, cognome, indirizzo, cap, citta provincia, telefono, cellulare, pec, codice_fiscale, partita_iva, note e id_utente.
            $anagrafica = new Anagrafica();
            $anagrafica->setIdUtente($utente_id);
            if (isset($_POST['nome'])) $anagrafica->setNome($_POST['nome']);
            if (isset($_POST['cognome'])) $anagrafica->setCognome($_POST['cognome']);
            if (isset($_POST['denominazione'])) $anagrafica->setDenominazione($_POST['denominazione']);
            if (isset($_POST['indirizzo'])) $anagrafica->setIndirizzo($_POST['indirizzo']);
            if (isset($_POST['cap'])) $anagrafica->setCap($_POST['cap']);
            if (isset($_POST['citta'])) $anagrafica->setCitta($_POST['citta']);
            if (isset($_POST['provincia'])) $anagrafica->setProvincia($_POST['provincia']);
            if (isset($_POST['telefono'])) $anagrafica->setTelefono($_POST['telefono']);
            if (isset($_POST['cellulare'])) $anagrafica->setCellulare($_POST['cellulare']);
            if (isset($_POST['pec'])) $anagrafica->setPec($_POST['pec']);
            if (isset($_POST['codice_fiscale'])) $anagrafica->setCodiceFiscale($_POST['codice_fiscale']);
            if (isset($_POST['partita_iva'])) $anagrafica->setPartitaIva($_POST['partita_iva']);
            if (isset($_POST['note'])) $anagrafica->setNote($_POST['note']);
            if (isset($_POST['tipo_utente'])) $anagrafica->setTipoUtente($_POST['tipo_utente']);


            foreach ($_POST['gruppi'] as $gruppo) {
                $gruppo = new Gruppo($gruppo);
                $gruppo->addUtente($utente_id);
            }

            $anagrafica_id = $anagrafica->save();
            if (!$anagrafica_id) {
                Helper::addError('Errore nella creazione dell\'anagrafica');
                header('Location: /utenti/crea');
                return;
            }
        } catch (\Exception $e) {
            Helper::addError($e->getMessage());
        }

        Helper::addSuccess('Utente creato con successo');
        header('Location: /utenti');
    }


    // contropartiView
    public function contropartiView()
    {
        $args = $this->createViewArgs();
        $utenti = Utente::getControparti();
        $utenti = array_map(function ($utente) {
            $utente = new Utente($utente->id);
            $ruolo = $utente->getRuolo();
            $ruolo = $ruolo->getNome();
            $utente->setRuolo(strtolower($ruolo));
            return $utente;
        }, $utenti);
        $totalItems = count($utenti);
        $totalPages = ceil($totalItems / $args['limit']);


        $headers = [
            [
                'label' => 'ID',
                'sortable' => true,
                'sortUrl' => 'controparti',
                'sortKey' => 'id'
            ],
            [
                'label' => 'Nome',
                'sortable' => true,
                'sortUrl' => 'controparti',
                'sortKey' => 'nome'
            ],
            [
                'label' => 'Email',
                'sortable' => true,
                'sortUrl' => 'controparti',
                'sortKey' => 'email'
            ],
            [
                'label' => 'Ruolo',
                'sortable' => true,
                'sortUrl' => 'controparti',
                'sortKey' => 'id_ruolo'
            ],
            [
                'label' => 'Azioni',
                'sortable' => false
            ],
        ];
        $rows = [];
        foreach ($utenti as $utente) {
            $rows[] = [
                'cells' => [
                    ['content' => $utente->getId()],
                    ['content' => $utente->getAnagrafica()->getNome() . ' ' . $utente->getAnagrafica()->getCognome() . ' ' . $utente->getAnagrafica()->getDenominazione()],
                    ['content' => $utente->getEmail()],
                    ['content' => $utente->getRuolo()->getNome()],
                    ['content' => $this->createActionsCell($utente)],
                ]
            ];
        }


        echo $this->view->render('utenti.html.twig', [
            'utenti' => $utenti,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $args['currentPage'],
            'limit' => $args['limit'],
            'headers' => $headers,
            'rows' => $rows,

        ]);
    }

    public function assistitiView()
    {
        $args = $this->createViewArgs();
        $utenti = Utente::getAssistiti();
        $utenti = array_map(function ($utente) {
            $utente = new Utente($utente->id);
            $ruolo = $utente->getRuolo();
            $ruolo = $ruolo->getNome();
            $utente->setRuolo(strtolower($ruolo));
            return $utente;
        }, $utenti);
        $totalItems = count($utenti);
        $totalPages = ceil($totalItems / $args['limit']);

        $headers = [
            [
                'label' => 'ID',
                'sortable' => true,
                'sortUrl' => 'assistiti',
                'sortKey' => 'id'
            ],
            [
                'label' => 'Nome',
                'sortable' => true,
                'sortUrl' => 'assistiti',
                'sortKey' => 'nome'
            ],
            [
                'label' => 'Email',
                'sortable' => true,
                'sortUrl' => 'assistiti',
                'sortKey' => 'email'
            ],
            [
                'label' => 'Ruolo',
                'sortable' => true,
                'sortUrl' => 'assistiti',
                'sortKey' => 'id_ruolo'
            ],
            [
                'label' => 'Azioni',
                'sortable' => false
            ],
        ];
        $rows = [];
        foreach ($utenti as $utente) {
            $rows[] = [
                'cells' => [
                    ['content' => $utente->getId()],
                    ['content' => $utente->getAnagrafica()->getNome() . ' ' . $utente->getAnagrafica()->getCognome() . ' ' . $utente->getAnagrafica()->getDenominazione()],
                    ['content' => $utente->getEmail()],
                    ['content' => $utente->getRuolo()->getNome()],
                    ['content' => $this->createActionsCell($utente)],
                ]
            ];
        }

        echo $this->view->render('utenti.html.twig', [
            'utenti' => $utenti,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $args['currentPage'],
            'headers' => $headers,
            'rows' => $rows
        ]);

    }


}