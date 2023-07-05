<?php

namespace App\Models;

use App\Libraries\Database;

class Udienza extends BaseModel
{
    protected int $id;

    protected string $data;
    protected string $tipo;

    protected int $id_pratica;

    // gettters and setters
    private static function createObjectFromDb(mixed $udienza)
    {
        $obj = new self();
        $obj->setId($udienza['id']);
        $obj->setData($udienza['data']);
        $obj->setTipo($udienza['tipo']);
        $obj->setIdPratica($udienza['id_pratica']);
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

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function getIdPratica(): int
    {
        return $this->id_pratica;
    }

    public function setIdPratica(int $id_pratica): void
    {
        $this->id_pratica = $id_pratica;
    }

    public static function getByPraticaId(int $id_pratica)
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM Udienze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $query;
        $options['params'] = [':id_pratica' => $id_pratica];
        $result = $db->query($options);

        $udienze = [];
        foreach ($result as $udienza) {
            $udienze[] = new Udienza($udienza->id);
        }
        return $udienze;
    }

}