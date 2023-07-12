<?php

namespace App\Models;

use App\Libraries\Database;

class Assistito extends BaseModel
{
    protected int $id;
    protected ?int $id_pratica;
    protected ?int $id_utente;

    public static function removeRecordFromAssistitiByUtenteId($id)
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Assistiti WHERE id_utente = :id_utente";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_utente' => $id
        ];
        return $db->query($options);
    }

    // get all Pratiche by Assistito id. Join with Pratiche table
    public static function getPraticheByUtenteId($id)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Assistiti WHERE id_utente = :id_utente";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_utente' => $id
        ];
        $result = $db->query($options);

        // get all pratiche by Assistito id
        $pratiche = [];

        foreach ($result as $row) {
            $pratica = Pratica::getById($row['id_pratica']);
            $pratiche[] = $pratica;
        }

        return $pratiche;
    }

}