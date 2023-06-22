<?php

namespace App\Controllers;

use App\Models\Utente;
use App\Models\Pratica;

class PracticeController extends BaseController
{

    protected $praticaModel;

    public function __construct()
    {
        parent::__construct();
        $this->praticaModel = new Pratica();
    }

    public function praticheView()
    {
        echo $this->view->render(
            'listaPratiche.html.twig',
            [
                'totalItems' => 13,
                'itemsPerPage' => 10,
                'currentPage' => 1,
                'totalPages' => 2,
                'pratiche' => [
                    [
                        'id' => 1,
                        'nome' => 'Pratica 1',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 1',
                        'competenza' => 'Competenza 1',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 1',
                    ],
                    [
                        'id' => 2,
                        'nome' => 'Pratica 2',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 2',
                        'competenza' => 'Competenza 2',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 2',
                    ],
                    [
                        'id' => 3,
                        'nome' => 'Pratica 3',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 3',
                        'competenza' => 'Competenza 3',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 3',
                    ],
                    [
                        'id' => 4,
                        'nome' => 'Pratica 4',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 4',
                        'competenza' => 'Competenza 4',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 4',
                    ],
                    [
                        'id' => 5,
                        'nome' => 'Pratica 5',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 5',
                        'competenza' => 'Competenza 5',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 5',
                    ],
                    [
                        'id' => 6,
                        'nome' => 'Pratica 6',
                        'stato' => 'Archiviata',
                        'tipologia' => 'Tipologia 6',
                        'competenza' => 'Competenza 6',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 6',
                    ],
                    [
                        'id' => 7,
                        'nome' => 'Pratica 7',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 7',
                        'competenza' => 'Competenza 7',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 7',
                    ],
                    [
                        'id' => 8,
                        'nome' => 'Pratica 8',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 8',
                        'competenza' => 'Competenza 8',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 8',
                    ],
                    [
                        'id' => 9,
                        'nome' => 'Pratica 9',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 9',
                        'competenza' => 'Competenza 9',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 9',
                    ],
                    [
                        'id' => 10,
                        'nome' => 'Pratica 10',
                        'stato' => 'Archiviata',
                        'tipologia' => 'Tipologia 10',
                        'competenza' => 'Competenza 10',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 10',
                    ],
                    [
                        'id' => 11,
                        'nome' => 'Pratica 11',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 11',
                        'competenza' => 'Competenza 11',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 11',
                    ],
                    [
                        'id' => 12,
                        'nome' => 'Pratica 12',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 12',
                        'competenza' => 'Competenza 12',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 12',
                    ],
                    [
                        'id' => 13,
                        'nome' => 'Pratica 13',
                        'stato' => 'Attiva',
                        'tipologia' => 'Tipologia 13',
                        'competenza' => 'Competenza 13',
                        'prossima_scadenza' => '01/01/2021',
                        'ruolo' => 'Ruolo 13',
                    ]
                ]
            ]
        );
        exit();
    }

    // praticaCreaView
    public function praticaCreaView() {
        echo $this->view->render('creaPratica.html.twig');
    }

    // Controller - Metodo per la visualizzazione di una pratica
    public function showPratica($praticaId) {
        // Ottenere l'istanza della pratica dal database (ad esempio, utilizzando un'istanza di un'API di accesso al database)
        $pratica = $this->praticaModel->getPraticaById($praticaId);

        // Ottenere gli assistiti, le controparti, le scadenze, le udienze associati alla pratica dal database

        // Passare i dati alla vista per la visualizzazione
        // ...

        // Caricare la vista per la visualizzazione della pratica
        // ...
    }

    // Controller - Metodo per la creazione di una pratica
    public function createPratica($request)
    {
        // Ottenere i dati inviati dal form
        $nr_pratica = $request['nr_pratica'];
        $titolo = $request['titolo'];
        $nome = $request['nome'];
        $tipologia = $request['tipologia'];
        $avvocato = $request['avvocato'];
        $referente = $request['referente'];
        $competenza = $request['competenza'];
        $ruolo_generale = $request['ruolo_generale'];
        $giudice = $request['giudice'];
        $stato = $request['stato'];
        $id_gruppo = $request['id_gruppo'];
        $id_sottogruppo = $request['id_sottogruppo'];

        // Creare un'istanza del modello Pratica e assegnare i valori
        /*
        $pratica = new Pratica();
        $pratica->setNrPratica($nr_pratica);
        $pratica->setTitolo($titolo);
        $pratica->setNome($nome);
        $pratica->setTipologia($tipologia);
        $pratica->setAvvocato($avvocato);
        $pratica->setReferente($referente);
        $pratica->setCompetenza($competenza);
        $pratica->setRuoloGenerale($ruolo_generale);
        $pratica->setGiudice($giudice);
        $pratica->setStato($stato);
        $pratica->setIdGruppo($id_gruppo);
        $pratica->setIdSottogruppo($id_sottogruppo);

        // Salvare la pratica nel database (ad esempio, utilizzando un'istanza di un'API di accesso al database)
        $praticaId = $this->praticaModel->create($pratica);

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

        // Ripetere il processo per le controparti, le scadenze e le udienze

        // Reindirizzare l'utente alla pagina di visualizzazione della pratica appena creata
        header("Location: /pratiche/$praticaId");
        */
    }

    // Controller - Metodo per la modifica di una pratica
    public function updatePratica($request, $praticaId)
    {
        // Ottenere i dati inviati dal form
        $nr_pratica = $request['nr_pratica'];
        $titolo = $request['titolo'];
        $nome = $request['nome'];
        $tipologia = $request['tipologia'];
        $avvocato = $request['avvocato'];
        $referente = $request['referente'];
        $competenza = $request['competenza'];
        $ruolo_generale = $request['ruolo_generale'];
        $giudice = $request['giudice'];
        $stato = $request['stato'];
        $id_gruppo = $request['id_gruppo'];
        $id_sottogruppo = $request['id_sottogruppo'];

        // Ottenere l'istanza della pratica dal database (ad esempio, utilizzando un'istanza di un'API di accesso al database)
        $pratica = $this->praticaModel->getPraticaById($praticaId);

        // Aggiornare i valori della pratica
        $pratica->setNrPratica($nr_pratica);
        $pratica->setTitolo($titolo);
        $pratica->setNome($nome);
        $pratica->setTipologia($tipologia);
        $pratica->setAvvocato($avvocato);
        $pratica->setReferente($referente);
        $pratica->setCompetenza($competenza);
        $pratica->setRuoloGenerale($ruolo_generale);
        $pratica->setGiudice($giudice);
        $pratica->setStato($stato);
        $pratica->setIdGruppo($id_gruppo);
        $pratica->setIdSottogruppo($id_sottogruppo);

        // Salvare le modifiche della pratica nel database
        $this->praticaModel->update($pratica);

        // Aggiornare gli assistiti, le controparti, le scadenze, le udienze associati alla pratica
        $assistiti = $request['assistiti'];
        $controparti = $request['controparti'];
        $scadenze = $request['scadenze'];
        $udienze = $request['udienze'];

        // Rimuovere gli assistiti, le controparti, le scadenze, le udienze esistenti associati alla pratica dal database

        // Creare o aggiornare gli assistiti, le controparti, le scadenze, le udienze aggiornati nel database

        // Reindirizzare l'utente alla pagina di visualizzazione della pratica appena modificata
        header("Location: /pratiche/$praticaId");
    }

    // Controller - Metodo per l'eliminazione di una pratica
    public function deletePratica($praticaId) {
        // Ottenere l'istanza della pratica dal database (ad esempio, utilizzando un'istanza di un'API di accesso al database)
        $pratica = $this->praticaModel->getPraticaById($praticaId);

        // Eliminare gli assistiti, le controparti, le scadenze, le udienze associati alla pratica dal database

        // Eliminare la pratica dal database
        $this->praticaModel->delete($pratica);

        // Reindirizzare l'utente a una pagina di conferma o alla lista delle pratiche
        // ...
    }
}