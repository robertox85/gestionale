<?php

namespace App\Models;

class Prenotazione extends BaseModel
{

    protected int $id_prenotazione;
    protected int $id_utente;
    protected int $id_sala;
    protected string $data_ora_inizio;
    protected string $data_ora_fine;
    protected string $ricorrenza;
    protected string $fine_ricorrenza;

    private string $created_at;
    private string $updated_at;
    // gettters and setters

    public function getIdPrenotazione(): int
    {
        return $this->id_prenotazione;
    }

    public function setIdPrenotazione(int $id_prenotazione): void
    {
        $this->id_prenotazione = $id_prenotazione;
    }

    public function getIdUtente(): int
    {
        return $this->id_utente;
    }

    public function setIdUtente(int $id_utente): void
    {
        $this->id_utente = $id_utente;
    }

    public function getIdSala(): int
    {
        return $this->id_sala;
    }

    public function setIdSala(int $id_sala): void
    {
        $this->id_sala = $id_sala;
    }

    public function getDataOraInizio(): string
    {
        return $this->data_ora_inizio;
    }

    public function setDataOraInizio(string $data_ora_inizio): void
    {
        $this->data_ora_inizio = $data_ora_inizio;
    }

    public function getDataOraFine(): string
    {
        return $this->data_ora_fine;
    }

    public function setDataOraFine(string $data_ora_fine): void
    {
        $this->data_ora_fine = $data_ora_fine;
    }

    public function getRicorrenza(): string
    {
        return $this->ricorrenza;
    }

    public function setRicorrenza(string $ricorrenza): void
    {
        $this->ricorrenza = $ricorrenza;
    }

    public function getFineRicorrenza(): string
    {
        return $this->fine_ricorrenza;
    }

    public function setFineRicorrenza(string $fine_ricorrenza): void
    {
        $this->fine_ricorrenza = $fine_ricorrenza;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}