<?php

namespace App\Models;

use App\Libraries\Database;

class Udienza extends BaseModel
{
    protected int $id;

    protected string $data;
    protected string $descrizione;

    protected int $id_pratica;

    // gettters and setters


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

    public function getDescrizione(): string
    {
        return $this->descrizione;
    }

    public function setDescrizione(string $descrizione): void
    {
        $this->descrizione = $descrizione;
    }

    public function getIdPratica(): int
    {
        return $this->id_pratica;
    }

    public function setIdPratica(int $id_pratica): void
    {
        $this->id_pratica = $id_pratica;
    }

  

}