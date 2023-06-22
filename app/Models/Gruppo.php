<?php

namespace App\Models;

use App\Libraries\Database;

// TODO: refactor this class to use the BaseModel class
class Gruppo extends BaseModel
{
    protected int $id = 0;
    protected string $nome_gruppo = '';

    protected array $sottogruppi;
    /**
     * @var mixed|null
     */
    private mixed $id_sottogruppo;


    /*public function save()
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO Gruppi (nome_gruppo) VALUES (:nome_gruppo)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':nome_gruppo' => $this->nome_gruppo];
        $db->query($options);
        $this->id_gruppo = $db->lastInsertId();

        return $this;
    }*/
    /*
    public function update()
    {
        $db = Database::getInstance();
        $sql = "UPDATE Gruppi SET nome_gruppo = :nome_gruppo WHERE id_gruppo = :id_gruppo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':nome_gruppo' => $this->nome_gruppo, ':id_gruppo' => $this->id_gruppo];
        $db->query($options);
        return $this;
    }*/

    // getter and setter
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }


    public function getNomegruppo()
    {
        return $this->nome_gruppo;
    }

    public function setNomegruppo($nome_gruppo)
    {
        $this->nome_gruppo = $nome_gruppo;
    }

    public function setSottogruppi($sottogruppi)
    {
        $this->sottogruppi = $sottogruppi;
    }

    public function getSottogruppi($id_gruppo = null)
    {
        if ($id_gruppo == null) {
            $id_gruppo = $this->getId();
        }
        $db = Database::getInstance();
        $sql = "SELECT * FROM SottoGruppi WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id_gruppo];
        $sottogruppi =  $db->query($options);
        $sottogruppi =  array_map(function ($sottogruppo) {
            $sottogruppo = new SottoGruppo($sottogruppo->id);
            $sottogruppo->setUtenti($sottogruppo->getUtenti());
            return $sottogruppo->toArray();
        }, $sottogruppi);

        // return array, so json_encode will return an array of objects
        return $sottogruppi;
    }

    public function getIdsottogruppo()
    {
        return $this->id;
    }

    public function setIdsottogruppo($id)
    {
        $this->idì = $id;
    }


    // getGroupName
    public static function getNomeGruppoById($id)
    {
        // get Property name
        $db = Database::getInstance();
        $sql = "SELECT nome_gruppo FROM Gruppi WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id];
        $result = $db->query($options);
        return (isset($result[0])) ? $result[0]->nome_gruppo : null;
    }


/*    public function delete()
    {
        $db = Database::getInstance();

        $sql = "DELETE FROM Gruppi WHERE id_gruppo = :id_gruppo;";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_gruppo' => $this->getIdGruppo()];
        return $db->query($options);
    }
*/

    public function addSottoGruppo(array $sottogruppo) {
        $db = Database::getInstance();
        $sql = "INSERT INTO SottoGruppi (nome_sottogruppo, id_gruppo) VALUES (:nome_sottogruppo, :id_gruppo)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':nome_sottogruppo' => $sottogruppo['nome_sottogruppo'], ':id_gruppo' => $this->getId()];
        $db->query($options);

        return $db->lastInsertId();
    }




    // DeleteSottogruppo
    public function deleteSottogruppo($id_sottogruppo)
    {
        $db = Database::getInstance();
        $sql = "UPDATE Pratiche SET id_sottogruppo = NULL WHERE id_sottogruppo = :id_sottogruppo;";
        $sql .= "DELETE FROM SottoGruppi WHERE id = :id;";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id_sottogruppo];
        return $db->query($options);
    }

    public function updateSottogruppo($sottogruppo) {
        $db = Database::getInstance();
        $sql = "UPDATE SottoGruppi SET nome_sottogruppo = :nome_sottogruppo WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':nome_sottogruppo' => $sottogruppo['nome_sottogruppo'], ':id' => $sottogruppo['id']];

        $result = $db->query($options);
        if ($result == 0 || $result == 1) {
            return true;
        } else {
            return false;
        }
    }
}