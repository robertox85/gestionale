<?php
namespace App\Models;

use App\Libraries\Database;
class Nota extends BaseModel{
    protected int $id;
    protected string $tipologia;
    protected string $testo;
    protected string $visiblità;

    protected int $id_pratica;

    // gettters and setters
    private static function createObjectFromDb(mixed $nota)
    {
        $obj = new self();
        $obj->setId($nota['id']);
        $obj->setTipologia($nota['tipologia']);
        $obj->setTesto($nota['testo']);
        $obj->setVisiblità($nota['visiblità']);
        $obj->setIdPratica($nota['id_pratica']);
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

    public function getTipologia(): string
    {
        return $this->tipologia;
    }

    public function setTipologia(string $tipologia): void
    {
        $this->tipologia = $tipologia;
    }

    public function getTesto(): string
    {
        return $this->testo;
    }

    public function setTesto(string $testo): void
    {
        $this->testo = $testo;
    }

    public function getVisiblità(): string
    {
        return $this->visiblità;
    }

    public function setVisiblità(string $visiblità): void
    {
        $this->visiblità = $visiblità;
    }

    public function getIdPratica(): int
    {
        return $this->id_pratica;
    }

    public function setIdPratica(int $id_pratica): void
    {
        $this->id_pratica = $id_pratica;
    }


    // methods
    // get nota by id_pratica
    public static function getNotaByIdPratica(int $id_pratica): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM note WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $id_pratica];
        // return $db->query($options) ?? [];
        // return new Nota($db->query($options)[0]->id);
        if (count($db->query($options)) == 0) {
            return [];
        }

        $note = [];
        foreach ($db->query($options) as $nota) {
            $note[] = new Nota($nota->id);
        }

        return $note;
    }
}