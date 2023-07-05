<?php

namespace App\Models;

use App\Libraries\Database;
class Anagrafica extends BaseModel
{
    protected int $id;
    protected string $nome;
    protected string $cognome;
    protected string $indirizzo;
    protected string $cap;
    protected string $citta;
    protected string $provincia;
    protected string $telefono;
    protected string $cellulare;
    protected string $pec;
    protected string $codice_fiscale;
    protected string $partita_iva;
    protected string $note;
    protected int $id_utente;

    public static function getByUserId(int $id_utente)
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM Anagrafiche WHERE id_utente = :id_utente";
        $options = [];
        $options['query'] = $query;
        $options['params'] = [':id_utente' => $id_utente];
        $result = $db->query($options);
        // if result return new Anagrafica else return false
        return $result ? new Anagrafica($result[0]->id) : false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getCognome()
    {
        return $this->cognome;
    }

    public function getIndirizzo()
    {
        return $this->indirizzo;
    }

    public function getCap()
    {
        return $this->cap;
    }

    public function getCitta()
    {
        return $this->citta;
    }

    public function getProvincia()
    {
        return $this->provincia;
    }

    public function getTelefono()
    {
        return $this->telefono;
    }

    public function getCellulare()
    {
        return $this->cellulare;
    }

    public function getPec()
    {
        return $this->pec;
    }

    public function getCodiceFiscale()
    {
        return $this->codice_fiscale;
    }

    public function getPartitaIva()
    {
        return $this->partita_iva;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function getUtenteId()
    {
        return $this->id_utente;
    }

    // setter
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function setCognome($cognome)
    {
        $this->cognome = $cognome;
    }

    public function setIndirizzo($indirizzo)
    {
        $this->indirizzo = $indirizzo;
    }

    public function setCap($cap)
    {
        $this->cap = $cap;
    }

    public function setCitta($citta)
    {
        $this->citta = $citta;
    }

    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;
    }

    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    }

    public function setCellulare($cellulare)
    {
        $this->cellulare = $cellulare;
    }

    public function setPec($pec)
    {
        $this->pec = $pec;
    }

    public function setCodiceFiscale($codice_fiscale)
    {
        // $this->codice_fiscale = $codice_fiscale;
        $this->codice_fiscale = $codice_fiscale === null ? '' : $codice_fiscale;
    }

    public function setPartitaIva($partita_iva)
    {
        $this->partita_iva = $partita_iva;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function setUtenteId($id_utente)
    {
        $this->id_utente = $id_utente;
    }
}