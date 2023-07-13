<?php

namespace App\Models;

use App\Libraries\Database;

class Referente extends BaseModel
{
    protected int $id;
    protected ?int $id_pratica;
    protected ?int $id_utente;

    public static function removeRecordFromReferentiByUtenteId($id)
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Referenti WHERE id_utente = :id_utente";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_utente' => $id
        ];
        return $db->query($options);
    }
}