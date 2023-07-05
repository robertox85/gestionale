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
        $search = $_GET['query'];
        $search = str_replace(' ', '%', $search);
        $search = '%' . $search . '%';

        $db = Database::getInstance();
        // Make Join query to get all the data from the database Anagrafica and Utenti
        // $sql = "SELECT * FROM Utenti WHERE nome LIKE :search OR cognome LIKE :search OR email LIKE :search";
        $sql = "SELECT * FROM Anagrafiche JOIN Utenti ON Anagrafiche.id_utente = Utenti.id WHERE Anagrafiche.nome LIKE :search OR Anagrafiche.cognome LIKE :search OR Utenti.email LIKE :search";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':search' => $search];
        $result = $db->query($options);

        // return json data
        header('Content-Type: application/json');
        echo json_encode($result);

    }
}