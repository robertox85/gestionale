<?php

namespace App\Models;

use App\Libraries\Database;

class Scadenza extends BaseModel
{
    protected int $id;
    protected string $data;
    protected string $motivo;
    protected string $id_pratica;

    // gettters and setters
    private static function createObjectFromDb(mixed $scadenza)
    {
        $obj = new self();
        $obj->setId($scadenza['id']);
        $obj->setData($scadenza['data']);
        $obj->setMotivo($scadenza['motivo']);
        $obj->setIdPratica($scadenza['id_pratica']);
        return $obj;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function getMotivo(): string
    {
        return $this->motivo;
    }

    public function setMotivo(string $motivo): void
    {
        $this->motivo = $motivo;
    }

    public function getIdPratica(): string
    {
        return $this->id_pratica;
    }

    public function setIdPratica(string $id_pratica): void
    {
        $this->id_pratica = $id_pratica;
    }


    public static function getByPraticaId(string $id_pratica)
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM Scadenze WHERE id_pratica = :id_pratica";
        $options = [];
        $options[':id_pratica'] = $id_pratica;
        $result = $db->execute($query, $options);
        $scadenze = [];
        foreach ($result as $scadenza) {
            $scadenze[] = new Scadenza($scadenza->id);
        }
        return $scadenze;
    }

}