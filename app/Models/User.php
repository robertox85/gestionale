<?php

namespace App\Models;


class User extends BaseModel
{
    protected int $id_utente;
    protected string $email;
    protected string $password;
    protected int $id_ruolo;

    public function __construct($id_utente, $email, $password, $id_ruolo)
    {
        $this->id_utente = $id_utente;
        $this->email = $email;
        $this->password = $password;
        $this->id_ruolo = $id_ruolo;
    }

    public static function getUserByUsernameOrEmail(mixed $email)
    {
        // Implementa la logica di recupero dell'utente dal database qui
        // Ad esempio, recupera l'utente dal database in base all'email
        // e restituisci un'istanza di User
        // Esempio:
        return new User(1, 'mario@example.com', 'password', '1');
    }

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

    public function verifyPassword($password)
    {
        // Implementa la logica di verifica della password qui
        // Ad esempio, verifica la password in base al valore hash
        // Esempio:
        if ($this->password !== $password) {
            return false;
        }

        return true;
    }

    public function getRoles(): array
    {
        $roles = [];

        // ottieni i ruoli dalla tabella ruoli con id == id_ruolo
        // Esempio:
        $roles[] = Ruolo::getById($this->role);

        return $roles;

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

    public function setRole($id_ruolo)
    {
        $this->id_ruolo = $id_ruolo;
    }


    public static function findByIdUtente($id_utente)
    {
        // Implementa la logica di recupero dell'utente dal database qui
        // Ad esempio, recupera l'utente dal database in base all'ID
        // e restituisci un'istanza di User
        // Esempio:
        return new User(1, 'mario@example.com', 'password', '1');
    }

    public static function findByEmail($email)
    {
        // Implementa la logica di recupero dell'utente dal database qui
        // Ad esempio, recupera l'utente dal database in base all'email
        // e restituisci un'istanza di User
        // Esempio:
        return new User(1, 'mario@example.com', 'password', '1');
    }

    /*public function toArray(): array
    {
        return [
            'id' => $this->id_utente
        ];
    }*/

    private function getRoleName()
    {
        // Recupera il nome del ruolo dell'utente
        // Esempio:
        $role = Ruolo::getById($this->getRuoloId());

        return $role->getName();
    }

    public static function getUserPermissions(int $id_utente = null): array
    {
        if ($id_utente === null) {
            $id_utente = $_SESSION['user']['id'] ?? null;
        }

        if ($id_utente === null) {
            return [];
        }

        $user = self::findByIdUtente($id_utente);
        $role = Ruolo::getById($user->getRuoloId());


        return $role->getPermissions();
    }
}
