<?php

namespace App\Models;


use App\Libraries\Database;

class Utente extends BaseModel
{
    protected int $id;
    protected ?string $email;
    protected ?string $password;
    protected ?string $created_at;
    protected ?string $updated_at;
    protected ?int $id_ruolo = 5;


    private string $ruolo;

    public static function getControparti($args = [])
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Utenti WHERE id_ruolo = 6";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [];
        $controparti = $db->query($options);
        return $controparti;
    }

    public static function getAssistiti()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Utenti WHERE id_ruolo = 5";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [];
        $assistiti = $db->query($options);
        return $assistiti;
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

    public function getRuolo()
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

    // getGruppi
    public function getGruppi()
    {
        $db = Database::getInstance();
        $sql = "SELECT Gruppi.id, Gruppi.nome FROM Utenti_Gruppi JOIN Gruppi ON Utenti_Gruppi.id_gruppo = Gruppi.id WHERE Utenti_Gruppi.id_utente = :id_utente";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_utente' => $this->getId()];
        $result = $db->query($options);
        return $result;
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

        if($anagrafica->getTipoUtente() === 'Azienda'){
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
            $gruppo = new Gruppo($gruppo->id);
            $pratiche = array_merge($pratiche, $gruppo->getPratiche());
        }

        return $pratiche;
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

    public function getPraticheAssistito()
    {
        $db = Database::getInstance();
        // Select Pratiche with Join Assistiti with id_utente = $this->getId()
        $sql = "SELECT * FROM Pratiche JOIN Assistiti ON Assistiti.id_utente = :id_utente WHERE Pratiche.id = Assistiti.id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_utente' => $this->getId()];
        $result = $db->query($options);
        return $result;

    }
}
