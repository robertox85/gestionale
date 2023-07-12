<?php

namespace App\Models;

use App\Libraries\Database;

class Anagrafica extends BaseModel
{
    protected int $id;
    protected ?string $nome;
    protected ?string $cognome;
    protected ?string $denominazione;
    protected ?string $indirizzo;
    protected ?string $cap;
    protected ?string $citta;
    protected ?string $provincia;
    protected ?string $telefono;
    protected ?string $cellulare;
    protected ?string $pec;
    protected ?string $codice_fiscale;
    protected ?string $partita_iva;
    protected ?string $note;
    protected ?string $tipo_utente;

    protected ?int $id_utente;

    protected ?string $created_at;
    protected ?string $updated_at;

    // getCreatedAd
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    // getUpdatedAd
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    // setCreatedAd
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    // setUpdatedAd
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

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

    public function getDenominazione()
    {
        return $this->denominazione;
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

    public function getTipoUtente()
    {
        return $this->tipo_utente;
    }



    public function getIdUtente()
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

    public function setDenominazione($denominazione)
    {
        $this->denominazione = $denominazione;
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
        $this->codice_fiscale = ($codice_fiscale == '') ? null : strtoupper($codice_fiscale);
    }

    public function setPartitaIva($partita_iva)
    {
        $this->partita_iva = ($partita_iva == '') ? null : strtoupper($partita_iva);
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function setTipoUtente($tipo_utente)
    {
        $this->tipo_utente = $tipo_utente;
    }



    public function setIdUtente($id_utente)
    {
        $this->id_utente = $id_utente;
    }


    public function getGruppo() {
        $db = Database::getInstance();
        $query = "SELECT id_gruppo FROM Utenti_Gruppi WHERE id_utente = :id";
        $options = [];
        $options['query'] = $query;
        $options['params'] = [':id' => $this->getIdUtente()];
        $result = $db->query($options);
        return $result ? $result[0]->id_gruppo : false;
    }
}