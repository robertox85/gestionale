<?php

namespace App\Models;

use App\Libraries\Database;

// TODO: refactor this class to use the BaseModel class
class Gruppo extends BaseModel
{
    protected int $id = 0;
    protected string $nome = '';

    protected ?string $created_at;
    protected ?string $updated_at;

    //getCreatedAd
    private $count_utenti;
    private array $pratiche;

    // Getter and Setter

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

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }


    // Methods
    public function setPratiche(array $getPratiche)
    {
        $this->pratiche = $getPratiche;
    }

    public function getPratiche()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Pratiche WHERE id_gruppo = :id_gruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_gruppo' => $this->getId()];
        $result = $db->query($options);
        // return only id
        $result = array_map(function ($result) {
            return $result->id;
        }, $result);

        return $result;
    }

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

    public function getCountUtenti()
    {
        $db = Database::getInstance();
        // Ottengo il numero di utenti del ruolo
        $sql = "SELECT COUNT(*) AS count FROM Gruppi JOIN Utenti_Gruppi ON Gruppi.id = Utenti_Gruppi.id_gruppo WHERE Gruppi.id = :id_gruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_gruppo' => $this->getId()];
        $result = $db->query($options);
        $this->setCountUtenti($result[0]->count);
        return $this->count_utenti;
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

    public function setCountUtenti($getCountUtenti)
    {
        $this->count_utenti = $getCountUtenti;
    }

    // Static Methods
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

    public static function getAllGruppi(array $args)
    {
        $db = Database::getInstance();
        $sql = "SELECT Gruppi.*, COUNT(Utenti_Gruppi.id_utente) AS count_utenti, ";
        $sql .= "COUNT(Pratiche.id) AS count_pratiche FROM Gruppi ";
        $sql .= "LEFT JOIN Utenti_Gruppi ON Gruppi.id = Utenti_Gruppi.id_gruppo ";
        $sql .= "LEFT JOIN Pratiche ON Gruppi.id = Pratiche.id_gruppo ";

        $options = [];
        $options['params'] = [];

        if (!empty($args)) {

            if (isset($args['search']) && !empty($args['search'])) {
                $sql .= "WHERE Gruppi.id IN (";
                $sql .= "SELECT Gruppi.id FROM Gruppi ";
                $sql .= "LEFT JOIN Utenti_Gruppi ON Gruppi.id = Utenti_Gruppi.id_gruppo ";
                $sql .= "LEFT JOIN Utenti ON Utenti_Gruppi.id_utente = Utenti.id ";
                $sql .= "LEFT JOIN Anagrafiche ON Utenti.id = Anagrafiche.id_utente ";
                $sql .= "WHERE Gruppi.nome LIKE ? OR Anagrafiche.nome LIKE ? OR Anagrafiche.cognome LIKE ?  OR Anagrafiche.denominazione LIKE ?  OR Utenti.username LIKE ?  OR Utenti.email LIKE ? )";
                $options['params'] = [
                    '%' . $args['search'] . '%',
                    '%' . $args['search'] . '%',
                    '%' . $args['search'] . '%',
                    '%' . $args['search'] . '%',
                    '%' . $args['search'] . '%',
                    '%' . $args['search'] . '%'
                ];
            }

            $options['limit'] = $args['limit'];
            $options['offset'] = ($args['currentPage'] - 1) * $args['limit'];
            $options['order_dir'] = $args['order'] ?? 'ASC';

            if ($args['order_by'] == 'id') {
                $options['order_by'] = "Gruppi.id";
            } elseif ($args['order_by'] == 'utenti') {
                $options['order_by'] = "count_utenti";
            } elseif ($args['order_by'] == 'pratiche') {
                $options['order_by'] = "count_pratiche";
            } else {
                $options['order_by'] = "Gruppi." . $args['order_by'];
            }
        }

        $sql .= " GROUP BY Gruppi.id";

        $options['query'] = $sql;
        $result = $db->query($options);
        $array = [];
        foreach ($result as $key => $value) {
            $array[] = new Gruppo($value->id);
        }

        return $array;
    }

}