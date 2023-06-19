<?php

namespace App\Models;

use App\Libraries\Database;

class SottoGruppo extends BaseModel
{

    public $id_sottogruppo;
    public $nome_sottogruppo;
    public $id_gruppo;
    public $nome_gruppo;

    public $utenti;

    public function __construct($id_sottogruppo, $nome_sottogruppo, $id_gruppo, $nome_gruppo, $utenti = [])
    {
        $this->id_sottogruppo = $id_sottogruppo;
        $this->nome_sottogruppo = $nome_sottogruppo;
        $this->id_gruppo = $id_gruppo;
        $this->nome_gruppo = $nome_gruppo;
        $this->utenti = $utenti;
    }

    // getByGroup
    public static function getByGroup($id_gruppo)
    {
        $db = Database::getInstance();

        // get group name and add it to the result
        $sql = "SELECT * FROM Sottogruppi WHERE id_gruppo = :id_gruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_gruppo' => $id_gruppo];

        return $db->query($options);
    }

    // prendi tutti gli utenti in un sottogruppo
    public static function getUtentiInSottogruppo($id_sottogruppo)
    {
        $db = Database::getInstance();

        // Get all Utenti in Sottogruppo. Make a join with Utenti_Sottogruppi table
        $sql = "SELECT Utenti.* FROM Utenti_Sottogruppi JOIN Utenti ON Utenti_Sottogruppi.id_utente = Utenti.id_utente WHERE Utenti_Sottogruppi.id_sottogruppo = :id_sottogruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_sottogruppo' => $id_sottogruppo];

        return $db->query($options);
    }

}