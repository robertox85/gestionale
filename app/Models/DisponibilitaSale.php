<?php

namespace App\Models;

class DisponibilitaSale extends BaseModel
{

    private int $id_disponibilita = 0;

    public function getIdDisponibilita()
    {
        return $this->id_disponibilita;
    }

    public function setIdDisponibilita($id_disponibilita)
    {
        $this->id_disponibilita = $id_disponibilita;
    }

    private int $id_sala = 0;

    public function getIdSala()
    {
        return $this->id_sala;
    }

    public function setIdSala($id_sala)
    {
        $this->id_sala = $id_sala;
    }

    private array $giorni_disponibili = [];

    public function getGiorniDisponibili()
    {
        return $this->giorni_disponibili;
    }

    public function setGiorniDisponibili($giorni_disponibili)
    {
        $this->giorni_disponibili = $giorni_disponibili;
    }

    private mixed $orario_apertura = null;

    public function getOrarioApertura()
    {
        return $this->orario_apertura;
    }

    public function setOrarioApertura($orario_apertura)
    {
        $this->orario_apertura = $orario_apertura;
    }

    private mixed $orario_chiusura = null;

    public function getOrarioChiusura()
    {
        return $this->orario_chiusura;
    }

    public function setOrarioChiusura($orario_chiusura)
    {
        $this->orario_chiusura = $orario_chiusura;
    }


    private int $durata_slot = 0;

    public function getDurataSlot()
    {
        return $this->durata_slot;
    }

    public function setDurataSlot($durata_slot)
    {
        $this->durata_slot = $durata_slot;
    }


    private mixed $created_at = null;

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    private mixed $updated_at = null;

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }
}
