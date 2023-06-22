<?php

namespace App\Models;

use App\Libraries\Database;

class SottoGruppo extends BaseModel
{

    protected $id;
    protected $nome_sottogruppo;
    protected $id_gruppo;

    protected $utenti;

    // getter and setter
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNomesottogruppo()
    {
        return $this->nome_sottogruppo;
    }

    public function setNomesottogruppo($nome_sottogruppo)
    {
        $this->nome_sottogruppo = $nome_sottogruppo;
    }

    public function getIdgruppo()
    {
        return $this->id_gruppo;
    }

    public function setIdgruppo($id_gruppo)
    {
        $this->id_gruppo = $id_gruppo;
    }


    public function getUtenti()
    {
        $db = Database::getInstance();

        // Get all Utenti in Sottogruppo. Make a join with Utenti_Sottogruppi table
        $sql = "SELECT Utenti.* FROM Utenti_Sottogruppi JOIN Utenti ON Utenti_Sottogruppi.id_utente = Utenti.id WHERE Utenti_Sottogruppi.id_sottogruppo = :id_sottogruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_sottogruppo' => $this->getId()];
        $this->utenti = $db->query($options);
        return $this->utenti;

    }

    public function setUtenti($utenti)
    {
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
        $sql = "SELECT Utenti.* FROM Utenti_Sottogruppi JOIN Utenti ON Utenti_Sottogruppi.id_utente = Utenti.id WHERE Utenti_Sottogruppi.id_sottogruppo = :id_sottogruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_sottogruppo' => $id_sottogruppo];

        return $db->query($options);
    }


    // addUtente
    public function addUtente($id_utente)
    {
        $db = Database::getInstance();

        $sql = "INSERT INTO Utenti_Sottogruppi (id_utente, id_sottogruppo) VALUES (:id_utente, :id_sottogruppo)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_utente' => $id_utente, ':id_sottogruppo' => $this->id];
        $db->query($options);

        return $db->lastInsertId();
    }

    // removeUtente
    public function removeUtente($id_utente)
    {
        $db = Database::getInstance();

        $sql = "DELETE FROM Utenti_Sottogruppi WHERE id_utente = :id_utente AND id_sottogruppo = :id_sottogruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_utente' => $id_utente, ':id_sottogruppo' => $this->id];
        $db->query($options);

        return $db->lastInsertId();
    }

    // clearUtentiSottogruppo
    public function clearUtentiSottogruppo()
    {
        $db = Database::getInstance();

        $sql = "DELETE FROM Utenti_Sottogruppi WHERE id_sottogruppo = :id_sottogruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_sottogruppo' => $this->id];
        $db->query($options);

        return $db->lastInsertId();
    }

    // delete
   /* public function delete()
    {

        // clearUtentiSottogruppo
        $this->clearUtentiSottogruppo();

        // clearPraticheSottogruppo
        $this->clearPraticheSottogruppo();

        $db = Database::getInstance();

        $sql = "DELETE FROM Sottogruppi WHERE id_sottogruppo = :id_sottogruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_sottogruppo' => $this->id_sottogruppo];

        return $db->query($options);
    }*/

    // clearPraticheSottogruppo
    public function clearPraticheSottogruppo()
    {
        $db = Database::getInstance();

        $sql = "UPDATE Pratiche SET id_sottogruppo = NULL WHERE id_sottogruppo = :id_sottogruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_sottogruppo' => $this->getId()];
        $db->query($options);

        return $db->lastInsertId();
    }
}