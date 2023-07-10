<?php

namespace App\Models;

use App\Libraries\Database;

// TODO: refactor this class to use the BaseModel class
class Gruppo extends BaseModel
{
    protected int $id = 0;
    protected string $nome = '';

    // getter and setter
    public static function addRecordToUtentiGruppi(mixed $id, mixed $gruppo)
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO Utenti_Gruppi (id_utente, id_gruppo) VALUES (:id_utente, :id_gruppo)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_utente' => $id, ':id_gruppo' => $gruppo];
        $result = $db->query($options);
        return $result;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }


    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    // getUtenti
    public function getUtenti()
    {
        $db = Database::getInstance();
        // Fai una join con la tabella Utenti_Gruppi e Utenti e tra Utenti e Anagrafica
        //$sql = "SELECT Utenti.id,  Utenti.email FROM Utenti_Gruppi JOIN Utenti ON Utenti_Gruppi.id_utente = Utenti.id WHERE Utenti_Gruppi.id_gruppo = :id_gruppo";
        $sql = "SELECT Utenti.id, Anagrafiche.nome, Anagrafiche.cognome, Anagrafiche.denominazione, Anagrafiche.tipo_utente FROM Utenti_Gruppi JOIN Utenti ON Utenti_Gruppi.id_utente = Utenti.id JOIN Anagrafiche ON Anagrafiche.id_utente = Utenti.id WHERE Utenti_Gruppi.id_gruppo = :id_gruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_gruppo' => $this->getId()];
        return $db->query($options);
    }

    public function addUtente(int $id_utente)
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO Utenti_Gruppi (id_utente, id_gruppo) VALUES (:id_utente, :id_gruppo)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_utente' => $id_utente,
            ':id_gruppo' => $this->getId()
        ];
        $result = $db->query($options);
        return $result;
    }

    public static function removeRecordFromUtentiGruppiByUtenteId($utente_id)
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Utenti_Gruppi WHERE id_utente = :id_utente";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_utente' => $utente_id
        ];
        $result = $db->query($options);
        return $result;
    }

    public function removeRecordFromUtentiGruppiByGruppoId()
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Utenti_Gruppi WHERE id_gruppo = :id_gruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_gruppo' => $this->getId()
        ];
        $result = $db->query($options);
        return $result;
    }

    public function removeGruppoFromPraticheByGruppoId(): bool
    {
        // Update Pratiche set id_gruppo = null where id_gruppo = :id_gruppo
        $db = Database::getInstance();
        $sql = "UPDATE Pratiche SET id_gruppo = null WHERE id_gruppo = :id_gruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_gruppo' => $this->getId()
        ];
        return $db->query($options);

    }


    // getGruppiByUtenteId
    public static function getGruppiByUtenteId(int $id_utente)
    {
        $db = Database::getInstance();
        $sql = "SELECT Gruppi.id, Gruppi.nome FROM Utenti_Gruppi JOIN Gruppi ON Utenti_Gruppi.id_gruppo = Gruppi.id WHERE Utenti_Gruppi.id_utente = :id_utente";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_utente' => $id_utente];
        $result = $db->query($options);
        return $result;
    }

}