<?php

namespace App\Controllers;

use App\Libraries\Database;
use App\Libraries\Helper;
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


    public function praticheView()
    {
        $args = $this->createViewArgs();
        $headers = [
            [
                'label' => 'ID',
                'sortable' => true,
                'sortUrl' => 'pratiche',
                'sortKey' => 'id'
            ],
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
        ];

        switch ($args['sort']) {
            case 'gruppo':
                $pratiche = $this->getPraticheSortedByGruppo($args);

                break;
            default:
                $pratiche = Pratica::getAll($args);

                break;
        }


        $totalPratiche = Pratica::getAll();
        $totalPratiche = count($totalPratiche);
        $totalPages = ceil($totalPratiche / $args['limit']);


        $rows = [];
        foreach ($pratiche as $pratica) {
            $rows[] = [
                'cells' => [
                    ['content' => $pratica->getId()],
                    ['content' => $pratica->getNrPratica()],
                    ['content' => '<a class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-500" href="/gruppi/edit/' . $pratica->getGruppo()->getId() . '">' . $pratica->getGruppo()->getNome() . '</a>'],
                    ['content' => $pratica->getNome()],
                    ['content' => $pratica->getStato()],
                    ['content' => $this->createActionsCell($pratica)],
                ]
            ];
        }

        echo $this->view->render(
            'pratiche.html.twig',
            [
                'pratiche' => $pratiche,
                'entity' => 'pratiche',
                'totalPages' => $totalPages,
                'totalItems' => $totalPratiche,
                'itemsPerPage' => $args['limit'],
                'currentPage' => $args['currentPage'],
                'headers' => $headers,
                'rows' => $rows,
            ]
        );
        exit();
    }

    public function miePraticheView()
    {
        $args = $this->createViewArgs();
        $headers = [
            [
                'label' => 'ID',
                'sortable' => true,
                'sortUrl' => 'mie_pratiche',
                'sortKey' => 'id'
            ],
            [
                'label' => 'Nr. pratica',
                'sortable' => true,
                'sortUrl' => 'mie_pratiche',
                'sortKey' => 'nr_pratica'
            ],
            [
                'label' => 'Gruppo',
                'sortable' => true,
                'sortUrl' => 'mie_pratiche',
                'sortKey' => 'gruppo'
            ],
            [
                'label' => 'Nome',
                'sortable' => true,
                'sortUrl' => 'mie_pratiche',
                'sortKey' => 'nome'
            ],
            [
                'label' => 'Stato',
                'sortable' => true,
                'sortUrl' => 'mie_pratiche',
                'sortKey' => 'stato'
            ]
        ];
        $utente = Utente::getCurrentUser();
        switch ($args['sort']) {
            case 'gruppo':
                $pratiche = $this->getPraticheSortedByGruppo($args);

                break;
            default:
                $pratiche = $utente->getPraticheUtente();

                break;
        }


        $totalPratiche = $utente->getPraticheUtente();
        $totalPratiche = count($totalPratiche);
        $totalPages = ceil($totalPratiche / $args['limit']);

        $rows = [];
        foreach ($pratiche as $pratica_id) {
            $pratica = new Pratica($pratica_id);

            $rows[] = [
                'cells' => [
                    ['content' => $pratica->getId()],
                    ['content' => $pratica->getNrPratica()],
                    ['content' => $pratica->getGruppo()->getNome()],
                    ['content' => $pratica->getNome()],
                    ['content' => $pratica->getStato()],
                ]
            ];
        }

        echo $this->view->render(
            'pratiche.html.twig',
            [
                'pratiche' => $pratiche,
                'entity' => 'pratiche',
                'totalPages' => $totalPages,
                'totalItems' => $totalPratiche,
                'itemsPerPage' => $args['limit'],
                'currentPage' => $args['currentPage'],
                'headers' => $headers,
                'rows' => $rows,
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
            ]
        );
    }

    //editPratica
    public function editPratica()
    {
        $id_pratica = $_POST['id_pratica'];
        $pratica = new Pratica($id_pratica);
        $pratica->setNome($_POST['nome']);
        $pratica->setTipologia($_POST['tipologia']);


        $pratica->setCompetenza($_POST['competenza']);
        $pratica->setRuoloGenerale($_POST['ruolo_generale']);
        $pratica->setGiudice($_POST['giudice']);
        $pratica->setStato($_POST['stato']);
        $pratica->setIdGruppo($_POST['id_gruppo']);


        //$assistiti = $_POST['assistiti'];
        //$controparti = $_POST['controparti'];

        $scadenze = $_POST['scadenze'];
        $udienze = $_POST['udienze'];
        $note = $_POST['note'];

        /*$pratica->clearAssistiti();
        foreach ($assistiti as $assistitoData) {
            $pratica->addAssistito($assistitoData);
        }

        $pratica->clearControparti();
        foreach ($controparti as $controparteData) {
            $pratica->addControparte($controparteData);
        }*/

        $pratica->clearScadenze();
        foreach ($scadenze as $scadenzaData) {
            $scadenza = new Scadenza();
            $scadenza->setData($scadenzaData['data']);
            $scadenza->setMotivo($scadenzaData['motivo']);
            $scadenza->setIdPratica($id_pratica);
            $scadenza->save();
        }

        $pratica->clearUdienze();
        foreach ($udienze as $udienzaData) {
            $udienza = new Udienza();
            $udienza->setData($udienzaData['data']);
            $udienza->setDescrizione($udienzaData['descrizione']);
            $udienza->setIdPratica($id_pratica);
            $udienza->save();
        }

        $pratica->clearNote();
        foreach ($note as $notaData) {
            $nota = new Nota();
            $nota->setTipologia($notaData['tipologia']);
            $nota->setDescrizione($notaData['descrizione']);
            $nota->setVisibilita($notaData['visibilita']);
            $nota->setIdPratica($id_pratica);
            $nota->save();
        }


        $pratica->update();

        Helper::addSuccess('Pratica aggiornata con successo');

        header('Location: /pratiche/edit/' . $pratica->getId());

    }

    // praticaCreaView
    public function praticaCreaView()
    {

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
        $pratica->setNrPratica(Pratica::generateNrPratica());
        if (isset($_POST['nome'])) $pratica->setNome($_POST['nome']);
        if (isset($_POST['tipologia'])) $pratica->setTipologia($_POST['tipologia']);


        if (isset($_POST['competenza'])) $pratica->setCompetenza($_POST['competenza']);
        if (isset($_POST['ruolo_generale'])) $pratica->setRuoloGenerale($_POST['ruolo_generale']);
        if (isset($_POST['giudice'])) $pratica->setGiudice($_POST['giudice']);
        if (isset($_POST['stato'])) $pratica->setStato($_POST['stato']);
        if (isset($_POST['id_gruppo'])) $pratica->setIdGruppo($_POST['id_gruppo']);


        // Salvare la pratica nel database (ad esempio, utilizzando un'istanza di un'API di accesso al database)
        $praticaId = $pratica->save();


        // Creare o aggiornare gli assistiti, le controparti, le scadenze, le udienze associati alla pratica
        //$assistiti = $_POST['assistiti'];
        //$controparti = $_POST['controparti'];
        $scadenze = $_POST['scadenze'];
        $udienze = $_POST['udienze'];

        /*
        foreach ($assistiti as $assistitoData) {
            $pratica->addAssistito($assistitoData);
        }

        foreach ($controparti as $controparteData) {
            $pratica->addControparte($controparteData);
        }
        */

        foreach ($scadenze as $scadenzaData) {
            $scadenza = new Scadenza();
            $scadenza->setData($scadenzaData['data']);
            $scadenza->setMotivo($scadenzaData['motivo']);
            $scadenza->setIdPratica($praticaId);
            $scadenza->save();
        }

        foreach ($udienze as $udienzaData) {
            $udienza = new Udienza();
            $udienza->setData($udienzaData['data']);
            $udienza->setDescrizione($udienzaData['descrizione']);
            $udienza->setIdPratica($praticaId);
            $udienza->save();
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
        try {

            $pratica->deleteNote();
            //$pratica->clearAssistiti();
            //$pratica->clearControparti();
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

    private function getPraticheSortedByGruppo(array $args){

        $args['sort'] = 'id';
        $pratiche = Pratica::getAll($args);
        $direction = $args['order'] ?? 'asc';
        $compareFunction = $direction === 'asc'
            ? function ($a, $b) {
                $a = new Pratica($a->getId());
                $b = new Pratica($b->getId());
                return $a->getGruppo()->getNome() <=> $b->getGruppo()->getNome();
            }
            :
            function ($a, $b) {
                $a = new Pratica($a->getId());
                $b = new Pratica($b->getId());
                return $b->getGruppo()->getNome() <=> $a->getGruppo()->getNome();
            };

        usort($pratiche, $compareFunction);

        return $pratiche;




    }


}