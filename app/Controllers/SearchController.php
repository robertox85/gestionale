<?php

namespace App\Controllers;

use App\Libraries\Database;
use App\Models\Anagrafica;
use App\Models\Utente;


class SearchController extends BaseController
{

    public function searchView()
    {
        echo $this->view->render('search.html.twig');
    }

    public function search()
    {
        // check if the query is set, and if it is, call the searchUtenti() method or searchGruppi() method depending on the value of the entity parameter
        if (isset($_GET['query'])) {
            if ($_GET['entity'] == 'utenti') {
                $this->searchUtenti();
            } else if ($_GET['entity'] == 'gruppi') {
                $this->searchGruppi();
            } else if ($_GET['entity'] == 'assistiti') {
                $this->searchUtenti(5);
            }else if ($_GET['entity'] == 'controparti') {
                $this->searchUtenti(6);
            }
        }
    }

    private function searchUtenti($id_ruolo = null)
    {
        $search = $_GET['query'];
        $search = str_replace(' ', '%', $search);
        $search = '%' . $search . '%';

        $db = Database::getInstance();
        // Make Join query to get all the data from the database Anagrafica and Utenti
        $sql = "SELECT Utenti.id, Utenti.id_ruolo, Anagrafiche.nome, Anagrafiche.cognome, Anagrafiche.denominazione, Anagrafiche.tipo_utente FROM Anagrafiche JOIN Utenti ON Anagrafiche.id_utente = Utenti.id WHERE (Anagrafiche.nome LIKE :search OR Anagrafiche.cognome LIKE :search OR Utenti.email LIKE :search)";
        // Search where utente.id_ruoio = 5
        if ($id_ruolo != null) {
            $sql .= " AND Utenti.id_ruolo = :id_ruolo";
        }

        // if id_ruolo is null, always exclude id 6 (controparte)
        if ($id_ruolo == null) {
            $sql .= " AND Utenti.id_ruolo != 6";
        }

        $sql .= " ORDER BY Utenti.id_ruolo";

        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':search' => $search];
        if ($id_ruolo != null) {
            $options['params'][':id_ruolo'] = $id_ruolo;
        }
        $result = $db->query($options);

        // return json data
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    private function searchGruppi()
    {
        $search = $_GET['query'];
        $search = str_replace(' ', '%', $search);
        $search = '%' . $search . '%';

        $db = Database::getInstance();
        // Make Join query to get all the data from the database Anagrafica and Utenti
        $sql = "SELECT * FROM Gruppi WHERE nome LIKE :search";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':search' => $search];
        $result = $db->query($options);

        // return json data
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function create() {
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);
        $entity = $data['entity'];

        switch ($entity) {
            case 'controparti':
            case 'assistiti':
                try {

                    // check if user already exists, by nome, cognome, or denominazione
                    if($this->checkIfUserExists($data['nome'], $data['cognome'], $data['denominazione'])) {
                        echo json_encode(
                            [
                                'error' => true,
                                'message' => 'Analista già esistente con questo nome, cognome o denominazione'
                            ]
                        );
                        return;
                    }



                    $utente = new Utente();

                    $id_ruolo = $entity == 'controparti' ? 6 : 5;
                    $utente->setIdRuolo($id_ruolo);

                    $utente_id = $utente->save();
                    $anagrafica = new Anagrafica();
                    $anagrafica->setIdUtente($utente_id);
                    $anagrafica->setNome($data['nome']);
                    $anagrafica->setCognome($data['cognome']);
                    $anagrafica->setDenominazione($data['denominazione']);
                    $anagrafica->setTipoUtente($data['tipo_utente']);
                    $anagrafica->save();
                    echo json_encode(
                        [
                            'id' => $utente_id,
                            'nome' => $data['nome'],
                            'cognome' => $data['cognome'],
                            'denominazione' => $data['denominazione'],
                            'tipo_utente' => $data['tipo_utente']
                        ]
                    );
                    return;
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                break;
            case 'gruppi':
                $this->createGruppo();
                break;
        }
    }


    private function createUtente($data) {
        $db = Database::getInstance();
        $sql = "INSERT INTO Utenti (email, password, id_ruolo) VALUES (:email, :password, :id_ruolo)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':email' => '', ':password' => '', ':id_ruolo' => $data['id_ruolo']];
        $result = $db->query($options);
        echo $result;
    }

    private function createGruppo() {
        $db = Database::getInstance();
        $sql = "INSERT INTO Gruppi (nome) VALUES (:nome)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':nome' => $_POST['nome']];
        $result = $db->query($options);
        echo $result;
    }

    private function checkIfUserExists(mixed $nome, mixed $cognome, mixed $denominazione)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Anagrafiche WHERE ";

        if ($nome != '' && $cognome != '') {
            $sql .= "nome = :nome AND cognome = :cognome";
        }

        if ($denominazione != '') {
            $sql .= "denominazione = :denominazione";
        }


        $options = [];
        $options['query'] = $sql;

        if ($nome != '' && $cognome != '') {
            $options['params'] = [':nome' => $nome, ':cognome' => $cognome];
        }

        if ($denominazione != '') {
            $options['params'] = [':denominazione' => $denominazione];
        }

        $result = $db->query($options);
        if(count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

}