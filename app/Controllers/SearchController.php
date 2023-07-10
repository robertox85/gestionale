<?php

namespace App\Controllers;

use App\Libraries\Database;


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
        $sql = "SELECT Utenti.id, Utenti.email, Utenti.id_ruolo, Anagrafiche.id, Anagrafiche.nome, Anagrafiche.cognome, Anagrafiche.denominazione, Anagrafiche.indirizzo, Anagrafiche.cap, Anagrafiche.citta, Anagrafiche.provincia, Anagrafiche.telefono, Anagrafiche.cellulare, Anagrafiche.pec, Anagrafiche.codice_fiscale, Anagrafiche.partita_iva, Anagrafiche.note, Anagrafiche.tipo_utente, Anagrafiche.id_utente FROM Anagrafiche JOIN Utenti ON Anagrafiche.id_utente = Utenti.id WHERE (Anagrafiche.nome LIKE :search OR Anagrafiche.cognome LIKE :search OR Utenti.email LIKE :search)";
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

    private function searchAssistiti()
    {
        $search = $_GET['query'];
        $search = str_replace(' ', '%', $search);
        $search = '%' . $search . '%';

        $db = Database::getInstance();
        // Make Join query to get all the data from the database Anagrafica and Utenti
        //$sql = "SELECT Utenti.id, Utenti.email, Utenti.id_ruolo, Anagrafiche.id, Anagrafiche.nome, Anagrafiche.cognome, Anagrafiche.denominazione, Anagrafiche.indirizzo, Anagrafiche.cap, Anagrafiche.citta, Anagrafiche.provincia, Anagrafiche.telefono, Anagrafiche.cellulare, Anagrafiche.pec, Anagrafiche.codice_fiscale, Anagrafiche.partita_iva, Anagrafiche.note, Anagrafiche.tipo_utente, Anagrafiche.id_utente FROM Anagrafiche JOIN Utenti ON Anagrafiche.id_utente = Utenti.id WHERE Anagrafiche.nome LIKE :search OR Anagrafiche.cognome LIKE :search OR Utenti.email LIKE :search";
        // Search where utente.id_ruoio = 5
        $sql = "SELECT * FROM Anagrafiche JOIN Utenti ON Anagrafiche.id_utente = Utenti.id WHERE (Anagrafiche.nome LIKE :search OR Anagrafiche.cognome LIKE :search OR Utenti.email LIKE :search) AND Utenti.id_ruolo = 5";

        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':search' => $search];
        $result = $db->query($options);



        // return json data
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    private function searchControparti()
    {

    }
}