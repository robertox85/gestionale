<?php

namespace App\Models;

use App\Libraries\Database;
use App\Libraries\Helper;
use App\Libraries\ErrorHandler;
use PHPUnit\TextUI\Help;

class Ruolo extends BaseModel
{
    private $id;
    private $name;

    private $permissions;

    public function __construct($id, $name, $permissions = null)
    {
        $this->id = $id;
        $this->name = $name;

        if ($permissions === null) {
            $this->permissions = Permesso::getByRole($id);
        } else {
            $this->permissions = $permissions;
        }
    }

    /*public static function getById($id_ruolo)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Ruoli WHERE id_ruolo = :id_ruolo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_ruolo' => $id_ruolo];

        $result = $db->query($options);

        if (count($result) === 1) {
            $role = $result[0];
            // convert stdclass to array
            $role = json_decode(json_encode($role), true);
            $permissions = Permesso::getByRole($role['id_ruolo']);
            $permissions = array_map(function ($permission) {
                return $permission->nome_permesso;
            }, $permissions);

            return new Role($role['id_ruolo'], $role['nome_ruolo'], $permissions);
        } else {
            return null;
        }

    }*/

    /*public static function getAll()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Ruoli";
        $options = [];
        $options['query'] = $sql;

        $result = $db->query($options);

        $roles = [];
        foreach ($result as $role) {
            // convert stdclass to array
            $role = json_decode(json_encode($role), true);
            $permissions = Permesso::getByRole($role['id_ruolo']);
            $roles[] = new Role($role['id_ruolo'], $role['nome_ruolo'], $permissions);
        }

        return $roles;
    }*/



    public function hasPermission(Permission $permission)
    {
        // Verifica se il ruolo ha il permesso specificato
        foreach ($this->permissions as $rolePermission) {
            if ($rolePermission->getId() === $permission->getId()) {
                return true; // Il ruolo ha il permesso specificato
            }
        }

        return false; // Il ruolo non ha il permesso specificato
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return strtolower(str_replace(' ', '_', $this->name));
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    // setPermissionsToRole
    public function setPermissionsToRole($permesso_id)
    {
        $db = Database::getInstance();
        // Aggiungo il permesso al ruolo
        $sql = "INSERT INTO Ruoli_Permessi (ruolo_id, permesso_id) VALUES (:ruolo_id, :permesso_id)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':ruolo_id' => $this->id, ':permesso_id' => $permesso_id];

        return $db->query($options);
    }

    // clearPermissionsToRole
    public function removeAllPermissions()
    {
        $db = Database::getInstance();
        // Rimuovo tutti i permessi dal ruolo
        $sql = "DELETE FROM Ruoli_Permessi WHERE ruolo_id = :ruolo_id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':ruolo_id' => $this->id];

        return $db->query($options);
    }

    // update
    public function update()
    {
        $db = Database::getInstance();
        $sql = "UPDATE Ruoli SET nome_ruolo = :nome_ruolo WHERE id_ruolo = :id_ruolo";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':nome_ruolo' => $this->name, ':id_ruolo' => $this->id];

        return $db->query($options);
    }
}