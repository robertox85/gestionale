<?php

namespace App\Models;

use App\Libraries\Database;

class Pratica extends BaseModel
{

    protected $id;
    protected ?string $nr_pratica;
    protected ?string $nome;
    protected ?string $tipologia;
    protected ?string $stato;
    protected ?string $avvocato;
    protected ?string $referente;
    protected ?string $competenza;
    protected ?string $ruolo_generale;
    protected ?string $giudice;
    protected ?int $id_gruppo;

    // getter and setter
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNrPratica()
    {
        return $this->nr_pratica;
    }

    public function setNrPratica($nr_pratica)
    {
        $this->nr_pratica = $nr_pratica;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getTipologia()
    {
        return $this->tipologia;
    }

    public function setTipologia($tipologia)
    {
        $this->tipologia = $tipologia;
    }

    public function getStato()
    {
        return $this->stato;
    }

    public function setStato($stato)
    {
        $this->stato = $stato;
    }

    public function getAvvocato()
    {
        return $this->avvocato;
    }

    public function setAvvocato($avvocato)
    {
        $this->avvocato = $avvocato;
    }

    public function getReferente()
    {
        return $this->referente;
    }

    public function setReferente($referente)
    {
        $this->referente = $referente;
    }

    public function getCompetenza()
    {
        return $this->competenza;
    }

    public function setCompetenza($competenza)
    {
        $this->competenza = $competenza;
    }

    public function getRuoloGenerale()
    {
        return $this->ruolo_generale;
    }

    public function setRuoloGenerale($ruolo_generale)
    {
        $this->ruolo_generale = $ruolo_generale;
    }

    public function getGiudice()
    {
        return $this->giudice;
    }

    public function setGiudice($giudice)
    {
        $this->giudice = $giudice;
    }

    public function getIdGruppo()
    {
        return $this->id_gruppo;
    }

    public function setIdGruppo($id_gruppo)
    {
        $this->id_gruppo = $id_gruppo;
    }

    public function deleteNote()
    {
        $db = Database::getInstance();
        // Delete note related to this practice
        $sql = "DELETE FROM Note WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function deleteUdienze()
    {
        $db = Database::getInstance();
        // Delete udienze related to this practice
        $sql = "DELETE FROM Udienze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function deleteScadenze()
    {
        $db = Database::getInstance();
        // Delete scadenze related to this practice
        $sql = "DELETE FROM Scadenze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    // getGruppo
    public function getGruppo()
    {
        return new Gruppo($this->getIdGruppo()) ?? null;
    }

    // clearScadenze
    public function clearScadenze()
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Scadenze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }
    public function addScadenza(mixed $scadenza)
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO Scadenze (id_pratica, data, motivo) VALUES (:id_pratica, :data, :motivo)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_pratica' => $this->getId(),
            ':data' => $scadenza->getData(),
            ':motivo' => $scadenza->getMotivo()
        ];
        return $db->query($options);
    }

    public static function generateNrPratica()
    {
        $db = Database::getInstance();
        $sql = "SELECT MAX(CAST(nr_pratica AS UNSIGNED)) AS max_nr_pratica FROM Pratiche";
        $options = [
            'query' => $sql,
            'params' => []
        ];
        $result = $db->query($options);
        $maxNrPratica = $result[0]->max_nr_pratica ?? 0;
        $nextNrPratica = $maxNrPratica + 1;
        return sprintf('%04d', $nextNrPratica); // Formatta il numero come stringa con zeri iniziali
    }

    public function getScadenze()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Scadenze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    // clearUdienze
    public function clearUdienze()
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Udienze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }
    // getUdienze
    public function getUdienze()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Udienze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    // addUdienza
    public function addUdienza(mixed $udienza)
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO Udienze (id_pratica, data, descrizione) VALUES (:id_pratica, :data_udienza, :descrizione)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_pratica' => $this->getId(),
            ':data_udienza' => $udienza->getData(),
            ':descrizione' => $udienza->getDescrizione()
        ];
        return $db->query($options);
    }

    // clearNote
    public function clearNote()
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Note WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }
    // addNote
    public function addNota( mixed $note)
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO Note (id_pratica, tipologia, descrizione, visibilita) VALUES (:id_pratica, :tipologia, :testo, :visibilita)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_pratica' => $this->getId(),
            ':tipologia' => $note['tipologia'],
            ':testo' => $note['testo'],
            ':visibilita' => $note['visibilita']
        ];
        return $db->query($options);
    }

    // getNote
    public function getNote()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Note WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    // addAssistito
    public function addAssistito(mixed $id_utente)
    {
        // Assistito è un Utente con ruolo cliente
        $db = Database::getInstance();
        $sql = "INSERT INTO Assistiti (id_pratica, id_utente) VALUES (:id_pratica, :id_utente)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_pratica' => $this->getId(),
            ':id_utente' => $id_utente
        ];

        return $db->query($options);
    }

    // getAssistiti
    public function getAssistiti()
    {
        $db = Database::getInstance();
        // Make a JOIN with Utenti and Anagrafiche
        $sql = "SELECT Anagrafiche.id_utente, nome,cognome,denominazione,tipo_utente FROM Assistiti INNER JOIN Utenti ON Assistiti.id_utente = Utenti.id INNER JOIN Anagrafiche ON Anagrafiche.id_utente = Utenti.id WHERE Assistiti.id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    // clearAssistiti
    public function clearAssistiti()
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Assistiti WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_pratica' => $this->id
        ];
        return $db->query($options);
    }

    // addControparte
    public function addControparte(mixed $id_utente)
    {
        // Controparte è un Utente con ruolo cliente. Se non esiste, viene prima creato
        $db = Database::getInstance();
        $sql = "INSERT INTO Controparti (id_pratica, id_utente) VALUES (:id_pratica, :id_utente)";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_pratica' => $this->getId(),
            ':id_utente' => $id_utente
        ];

        return $db->query($options);
    }

    // getControparti
    public function getControparti()
    {
        $db = Database::getInstance();
        // Make a JOIN with Utenti and Anagrafiche
        $sql = "SELECT Anagrafiche.id_utente,nome,cognome,denominazione,tipo_utente FROM Controparti INNER JOIN Utenti ON Controparti.id_utente = Utenti.id INNER JOIN Anagrafiche ON Anagrafiche.id_utente = Utenti.id WHERE Controparti.id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->id];
        return $db->query($options);
    }

    // clearControparti
    public function clearControparti()
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Controparti WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [
            ':id_pratica' => $this->id
        ];
        return $db->query($options);
    }

}