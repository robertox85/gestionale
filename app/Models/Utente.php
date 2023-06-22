<?php

namespace App\Models;


class Utente extends BaseModel
{
    protected int $id_utente;
    protected string $nome;
    protected string $cognome;
    protected string $email;
    protected string $password;
    protected int $id_ruolo;

    protected string $ruolo;

    public function setRuolo($ruolo)
    {
        $this->ruolo = $ruolo;
    }



   // constructor
    public function getId()
    {
        return $this->id_utente;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getCognome()
    {
        return $this->cognome;
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

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function setCognome($cognome)
    {
        $this->cognome = $cognome;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setRuoloId($id_ruolo)
    {
        $this->id_ruolo = $id_ruolo;
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
            return $permission->getNomePermesso();
        }, $permessi);
    }
}
