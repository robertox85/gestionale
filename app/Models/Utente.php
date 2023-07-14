<?php

namespace App\Models;


use App\Libraries\Database;

class Utente extends BaseModel
{
    protected int $id;

    protected ?string $username;
    protected ?string $email;
    protected ?string $password;
    protected ?string $created_at;
    protected ?string $updated_at;
    protected ?int $id_ruolo = 5;


    private string $ruolo;

    private array $errors = [];

    private static function getWhereClause(mixed $filters)
    {
        $where = '';
        $params = [];
        if (isset($filters['filters']) && $filters['filters'] != '') {
            // $filters['filters']['ruoli']
            $where .= ' WHERE ';
            $where .= 'ruoli.id = :ruoli';
            // is an array of ruoli id
            $params[':ruoli'] = $filters['filters']['ruoli'];
        }
        return ['where' => $where, 'params' => $params];
    }

    public function setRuolo($ruolo)
    {
        $this->ruolo = $ruolo;
    }

    // constructor
    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRuoloId()
    {
        return $this->id_ruolo;
    }

    // setter
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setIdRuolo($id_ruolo): void
    {
        $this->id_ruolo = $id_ruolo;
    }

    public function getIdRuolo()
    {
        return $this->id_ruolo;
    }

    // getCreatedAt
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    // getUpdatedAt
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    // setCreatedAt
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    // setUpdatedAt
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    // getErrors
    public function getErrors()
    {
        return $this->errors;
    }

    //setErrors
    public function setErrors($errors)
    {
        // push to errors array
        $this->errors[] = $errors;
    }

    public function setUsername(mixed $username)
    {
        $this->username = $username;
    }

    public function getRuolo()
    {
        return $this->ruolo;
    }
    public static function getCurrentUser()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Utenti WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $_SESSION['utente']['id']];
        $user = $db->query($options);
        return new Utente($user[0]->id);
    }

    public function verifyPassword($password)
    {
        if ($this->password !== $password) {
            return false;
        }

        return true;
    }

    public function getRoles(): array
    {
        $roles[] = Ruolo::getById($this->getRuoloId());

        return $roles;

    }

    public function getRuoloObj()
    {
        return Ruolo::getById($this->getRuoloId());
    }

    public function getAnagrafica()
    {
        return Anagrafica::getByUserId($this->getId());
    }

    public static function getPermessiUtente(int $id_utente = null): array
    {
        if ($id_utente === null) {
            $id_utente = $_SESSION['user']['id'] ?? null;
        }

        if ($id_utente === null) {
            return [];
        }

        $user = self::getById($id_utente);
        $role = Ruolo::getById($user->getRuoloId());

        $permessi = $role->getPermessiRuolo();

        return array_map(function ($permission) {
            return $permission->getNome();
        }, $permessi);
    }

    // isValidPassword
    public static function isValidPassword($password)
    {
        if (strlen($password) < 8) {
            return false;
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }


    // isIncomplete

    public function isAnagraficaComplete()
    {
        $anagrafica = $this->getAnagrafica();

        if ($anagrafica === null || $anagrafica === false) {
            return false;
        }

        if ($anagrafica->getTipoUtente() === null) {
            return false;
        }

        if ($anagrafica->getTipoUtente() === 'Azienda') {
            if ($anagrafica->getDenominazione() === null) {
                return false;
            }

            if ($anagrafica->getPartitaIva() === null) {
                return false;
            }
        }

        if ($anagrafica->getTipoUtente() === 'Persona') {
            if ($anagrafica->getNome() === null) {
                return false;
            }

            if ($anagrafica->getCognome() === null) {
                return false;
            }

            if ($anagrafica->getCodiceFiscale() === null) {
                return false;
            }
        }

        if ($anagrafica->getIndirizzo() === null) {
            return false;
        }

        if ($anagrafica->getCap() === null) {
            return false;
        }

        if ($anagrafica->getCitta() === null) {
            return false;
        }

        if ($anagrafica->getProvincia() === null) {
            return false;
        }

        if ($anagrafica->getTelefono() === null) {
            return false;
        }

        if ($anagrafica->getCellulare() === null) {
            return false;
        }


        return true;
    }

    // is LoginDataComplete
    public function isLoginDataComplete()
    {
        if ($this->getEmail() === null) {
            return false;
        }

        if ($this->getPassword() === null) {
            return false;
        }

        return true;
    }


    // getPraticheUtente
    public function getPraticheUtente()
    {

        // get Gruppis and for each gruppo get pratiche
        $gruppi = $this->getGruppi();
        $pratiche = [];

        foreach ($gruppi as $gruppo) {
            $pratiche = array_merge($pratiche, $gruppo->getPratiche());
        }

        // return instances of Pratica
        array_walk($pratiche, function (&$pratica) {
            $pratica = new Pratica($pratica);
        });

        return $pratiche;
    }


    // Questa funzione è usata nei metodi getUtentiTableRows
    public function getPratiche()
    {
        // get Gruppis and for each gruppo get pratiche
        $gruppi = $this->getGruppi();
        $pratiche = [];

        foreach ($gruppi as $gruppo) {
            $praticheIds = $gruppo->getPratiche();
            foreach ($praticheIds as $praticaId) {
                $pratica = new Pratica($praticaId);
                $pratiche[] = $pratica;
            }
        }

        return $pratiche;
    }

    // Questa funzione è usata nei metodi getUtentiTableRows
    public function getGruppi()
    {
        $db = Database::getInstance();
        $sql = "SELECT Gruppi.id, Gruppi.nome FROM Utenti_Gruppi JOIN Gruppi ON Utenti_Gruppi.id_gruppo = Gruppi.id WHERE Utenti_Gruppi.id_utente = :id_utente";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_utente' => $this->getId()];
        $result = $db->query($options);
        // return instance of Gruppo
        /*return array_map(function ($gruppo) {
            return new Gruppo($gruppo->id);
        }, $result);*/
        return $result;
    }
    
    

    public function preparaAggiornamento(array $data)
    {
        // Setta le proprietà dell'utente
        $this->settaProprietaUtente($data);

        // Controlla e setta la password, se presente
        if (!$this->controllaEsettaPassword($data)) {
            return false;
        }

        // Prepara l'anagrafica
        $anagrafica = $this->preparaAnagrafica($data);
        if ($anagrafica === false) {
            return false;
        }

        // Aggiorna le associazioni gruppo
        $this->aggiornaAssociazioniGruppo($data['gruppi']);

        // Aggiorna l'anagrafica
        $anagrafica->update();

        return true;
    }

    private function settaProprietaUtente(array $data)
    {
        $this->setUsername($data['username']);
        $this->setEmail($data['email']);
        $this->setIdRuolo($data['ruolo']);
    }

    public function controllaEsettaPassword(array $data)
    {
        if ($data['password'] != '' && $data['password'] == $data['password_confirm']) {
            if (Utente::isValidPassword($data['password'])) {
                $this->setPassword($data['password']);
                return true;
            } else {
                $this->setErrors('La password deve essere lunga almeno 8 caratteri e deve contenere almeno una lettera maiuscola, una minuscola e un numero');
                return false;
            }
        }

        return true;
    }

    private function preparaAnagrafica(array $data)
    {
        $anagrafica = $this->getAnagrafica();
        if ($anagrafica === false || $anagrafica === null) {
            $this->setErrors('Errore nel caricamento dell\'anagrafica');
            return false;
        }

        // Setta le proprietà dell'anagrafica
        $anagrafica->setProprieta($data);

        return $anagrafica;
    }

    public function aggiornaAssociazioniGruppo(array|null $gruppi, int|null $user_id = null)
    {
        // skip if gruppi is empty, null, or is not an array
        if (empty($gruppi) || !is_array($gruppi)) {
            return;
        }

        // if user_id is null, use $this->getId()
        $user_id = ($user_id === null) ? $this->getId() : $user_id;
        Gruppo::removeRecordFromUtentiGruppiByUtenteId($user_id);
        foreach ($gruppi as $gruppo) {
            Gruppo::addRecordToUtentiGruppi($user_id, $gruppo);
        }
    }


    // getAllUtenti (with Anagrafica, Gruppi and Pratiche)
    public static function getAllUtenti(array $args = [])
    {
        $db = Database::getInstance();
        $sql = "SELECT Utenti.id AS utente_id, Anagrafiche.nome as anagrafica_nome, 
        COUNT(DISTINCT Gruppi.id) as count_gruppi, 
        COUNT(DISTINCT Pratiche.id) as count_pratiche FROM Utenti";
// LEFT JOIN Anagrafica
        $sql .= " LEFT JOIN Anagrafiche ON Utenti.id = Anagrafiche.id_utente";
// LEFT JOIN Utenti_Gruppi
        $sql .= " LEFT JOIN Utenti_Gruppi ON Utenti.id = Utenti_Gruppi.id_utente";
// LEFT JOIN Gruppi
        $sql .= " LEFT JOIN Gruppi ON Utenti_Gruppi.id_gruppo = Gruppi.id";
// LEFT JOIN Pratiche
        $sql .= " LEFT JOIN Pratiche ON Gruppi.id = Pratiche.id_gruppo";

        // WHERE
        // if $args['filters'] is not empty, add WHERE clause
        if (!empty($args['where'])) {
            $sql .= " WHERE ";
            // id_ruolo is in array of ruoli


            if (isset($args['where']['id_ruolo']) && is_array($args['where']['id_ruolo'])) {
                $ruoli = implode(',', array_map('intval', $args['where']['id_ruolo']));
                $sql .= "Utenti.id_ruolo IN ($ruoli)";
            }
        }

        $sql .= " GROUP BY Utenti.id, Anagrafiche.nome, Anagrafiche.denominazione";


        $options = [];

        if (!empty($args)) {
            $options['limit'] = $args['limit'];
            $options['offset'] = ($args['currentPage'] - 1) * $args['limit'];
            $options['order_dir'] = $args['order'] ?? 'ASC';
            if ($args['sort'] == 'id') {
                $options['order_by'] = "Utenti.id";
            } elseif ($args['sort'] == 'nome') {
                $options['order_by'] = "COALESCE(NULLIF(Anagrafiche.nome, ''), NULLIF(Anagrafiche.denominazione, ''))";
            } elseif ($args['sort'] == 'pratiche') {
                $options['order_by'] = 'count_pratiche';
            } elseif ($args['sort'] == 'gruppi') {
                $options['order_by'] = 'count_gruppi';
            } else {
                $options['order_by'] = $args['sort'];
            }

        }


        $options['query'] = $sql;

        // return instance of the class
        $result = $db->query($options);
        $array = [];
        foreach ($result as $record) {
            $array[] = new Utente($record->utente_id);
        }
        // ora però se ci sono meno utenti dei limiti impostati, devo recuperare gli utenti mancanti
        return $array;
    }

    public function getGruppiCount()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM Utenti_Gruppi WHERE id_utente = :id_utente";
        $options = [
            'query' => $sql,
            'params' => [
                ':id_utente' => $this->getId()
            ]
        ];
        $result = $db->query($options);
        return $result[0]->count;
    }

    public function getPraticheCount()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM Pratiche WHERE id_gruppo IN (SELECT id_gruppo FROM Utenti_Gruppi WHERE id_utente = :id_utente)";
        $options = [
            'query' => $sql,
            'params' => [
                ':id_utente' => $this->getId()
            ]
        ];
        $result = $db->query($options);
        return $result[0]->count;
    }


}
