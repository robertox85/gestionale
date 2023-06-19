<?php

namespace App\Models;

use App\Libraries\Database;

class Permesso extends BaseModel
{
    private $id;
    private $name;
    private $description;

    public function __construct($id, $name, $description = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    public static function getByUri($uri)
    {
        // Recupera i permessi corrispondenti all'URI
        // Implementa la logica di recupero dei permessi dal database
        // Puoi utilizzare un ORM o un altro metodo di accesso ai dati
        // In questo esempio, restituiamo un array di permessi statici
        return [
            new Permesso(1, 'view_home'),
            new Permesso(2, 'view_users'),
            new Permesso(3, 'view_roles'),
            new Permesso(4, 'view_permissions'),
            new Permesso(5, 'edit_users'),
            new Permesso(6, 'edit_roles'),
            new Permesso(7, 'edit_permissions'),
        ];
    }

    public static function getByRole(mixed $id)
    {
        $db = Database::getInstance();
        // recupera ruolo_permesso con ruolo_id = $id e poi recupera i permessi con id in (id dei permessi trovati)
        $sql = "SELECT * FROM Permessi WHERE id_permesso IN (SELECT permesso_id FROM Ruoli_Permessi WHERE ruolo_id = :id)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id];

        return $db->query($options);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return strtolower($this->name);
    }

    public function getDescription()
    {
        return $this->description;
    }

}