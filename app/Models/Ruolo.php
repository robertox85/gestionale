<?php

namespace App\Models;

use App\Libraries\Database;
use App\Libraries\Helper;
use App\Libraries\ErrorHandler;
use PHPUnit\TextUI\Help;

class Ruolo extends BaseModel
{
    protected $id_ruolo;
    protected  $nome_ruolo;


    public function getId()
    {
        return $this->id_ruolo;
    }

    public function getNomeRuolo()
    {
        return $this->nome_ruolo;
    }

    public function possiedePermesso(Permission $permission)
    {
        $permessiRuolo = $this->getPermessiRuolo();
        // Verifica se il ruolo ha il permesso specificato
        foreach ($permessiRuolo as $rolePermission) {
            if ($rolePermission->getId() === $permission->getId()) {
                return true; // Il ruolo ha il permesso specificato
            }
        }

        return false; // Il ruolo non ha il permesso specificato
    }

    // setPermessoRuolo
    public function setPermessoRuolo($permesso_id)
    {
        $db = Database::getInstance();
        // Aggiungo il permesso al ruolo
        $sql = "INSERT INTO Permessi_Ruoli (ruolo_id, permesso_id) VALUES (:ruolo_id, :permesso_id)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':ruolo_id' => $this->getId(), ':permesso_id' => $permesso_id];

        return $db->query($options);
    }

    // getPermessiRuolo
    public function getPermessiRuolo()
    {
        $db = Database::getInstance();
        // Ottengo tutti i permessi del ruolo
        $sql = "SELECT * FROM Permessi_Ruoli WHERE ruolo_id = :ruolo_id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':ruolo_id' => $this->getId()];

        $result = $db->query($options);
        $permissions = [];
        foreach ($result as $permission) {
            $permissions[] = new Permesso($permission->permesso_id);
        }

        return $permissions;
    }

    // clearPermissionsToRole
    public function eliminaPermessi()
    {
        $db = Database::getInstance();
        // Rimuovo tutti i permessi dal ruolo
        $sql = "DELETE FROM Permessi_Ruoli WHERE ruolo_id = :ruolo_id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':ruolo_id' => $this->id];

        return $db->query($options);
    }

    // update
    /*public function update()
    {
        $db = Database::getInstance();
        $sql = "UPDATE Ruoli SET nome_ruolo = :nome_ruolo WHERE id_ruolo = :id_ruolo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':nome_ruolo' => $this->name, ':id_ruolo' => $this->id];

        return $db->query($options);
    }*/
}