<?php

namespace App\Models;

use App\Libraries\Database;
use App\Libraries\Helper;
use App\Libraries\ErrorHandler;
use PHPUnit\TextUI\Help;

class Ruolo extends BaseModel
{
    protected $id;
    protected  $nome;
    protected ?string $created_at;
    protected ?string $updated_at;

    // getCreatedAd
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    // getUpdatedAd
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    // setCreatedAd
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    // setUpdatedAd
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNome()
    {
        return $this->nome;
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
        $sql = "INSERT INTO Ruoli_Permessi (ruolo_id, permesso_id) VALUES (:ruolo_id, :permesso_id)";
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
        $sql = "SELECT * FROM Ruoli_Permessi WHERE ruolo_id = :ruolo_id";
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
        $sql = "DELETE FROM Ruoli_Permessi WHERE ruolo_id = :ruolo_id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':ruolo_id' => $this->id];

        return $db->query($options);
    }


}