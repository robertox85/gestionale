<?php

namespace App\Models;

use App\Libraries\Database;

class Permesso extends BaseModel
{
    protected int $id_permesso;
    protected string $nome;
    protected string $descrizione;
    protected ?string $created_at;
    protected ?string $updated_at;



    // getter and setter
    public function getId()
    {
        return $this->id_permesso;
    }

    public function setId($id_permesso)
    {
        $this->id_permesso = $id_permesso;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getDescrizione()
    {
        return $this->descrizione;
    }

    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;
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
    // methods
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

    public static function getByRuoloId(mixed $ruolo_id)
    {
        $db = Database::getInstance();
        // recupera ruolo_permesso con ruolo_id = $id e poi recupera i permessi con id in (id dei permessi trovati)
        $sql = "SELECT * FROM Permessi WHERE id IN (SELECT permesso_id FROM Ruoli_Permessi WHERE ruolo_id = :id)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $ruolo_id];

        return $db->query($options);
    }


}