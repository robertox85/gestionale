<?php

namespace App\Models;

use App\Libraries\Database;

class Controparte extends BaseModel
{
    protected int $id;
    protected ?int $id_pratica;
    protected ?int $id_utente;

    public static function removeRecordFromContropartiByUtenteId($id)
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Controparti WHERE id_utente = :id_utente";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_utente' => $id
        ];
        return $db->query($options);
    }
}