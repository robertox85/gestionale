<?php

namespace App\Models;

use App\Libraries\Database;

// TODO: refactor this class to use the BaseModel class
class Gruppo extends BaseModel
{

    public $id_gruppo;
    public $nome_gruppo;
    public $sottogruppi;
    public function __construct($id_gruppo = null, $nome_gruppo = null, $sottogruppi = [])
    {
        $this->id_gruppo = $id_gruppo;
        $this->nome_gruppo = $nome_gruppo;
        $this->sottogruppi = $sottogruppi;
    }

    public function save()
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO Gruppi (nome_gruppo) VALUES (:nome_gruppo)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':nome_gruppo' => $this->nome_gruppo];
        $db->query($options);
        $this->id_gruppo = $db->lastInsertId();
    }

    // getter and setter
    public function getIdGruppo()
    {
        return $this->id_gruppo;
    }

    public function setIdGruppo($id_gruppo)
    {
        $this->id_gruppo = $id_gruppo;
    }


    public function getNomeGruppo()
    {
        return $this->nome_gruppo;
    }

    public function setNomeGruppo($nome_gruppo)
    {
        $this->nome_gruppo = $nome_gruppo;
    }

    public function getSottogruppi()
    {
        return $this->sottogruppi;
    }

    // getGroupName
    public static function getNomeGruppoById($id_gruppo)
    {
        // get Property name
        $db = Database::getInstance();
        $sql = "SELECT nome_gruppo FROM Gruppi WHERE id_gruppo = :id_gruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_gruppo' => $id_gruppo];
        $result = $db->query($options);
        return (isset($result[0])) ? $result[0]->nome_gruppo : null;
    }
}