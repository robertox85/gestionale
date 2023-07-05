<?php

namespace App\Models;


class Utente extends BaseModel
{
    protected int $id_utente;
    protected string $email;
    protected string $password;
    protected int $id_ruolo;


    private string $ruolo;

    public function setRuolo($ruolo)
    {
        $this->ruolo = $ruolo;
    }

   // constructor
    public function getId()
    {
        return $this->id_utente;
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
    public function setId($id_utente)
    {
        $this->id_utente = $id_utente;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setIdRuolo($id_ruolo)
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

}
