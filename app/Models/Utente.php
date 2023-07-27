<?php

namespace App\Models;


use App\Libraries\Database;
use PHPMailer\PHPMailer\PHPMailer;

class Utente extends BaseModel
{
    protected int $id_utente;

    protected string $nome;
    protected string $cognome;
    protected string $email;
    protected string $password;
    protected string $ruolo;
    private string $created_at;
    private string $updated_at;


    // GETTERS AND SETTERS
    public function getIdUtente(): int
    {
        return $this->id_utente;
    }

    public function setIdUtente(int $id_utente): void
    {
        $this->id_utente = $id_utente;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getCognome(): string
    {
        return $this->cognome;
    }

    public function setCognome(string $cognome): void
    {
        $this->cognome = $cognome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRuolo(): string
    {
        return $this->ruolo;
    }

    public function setRuolo(string $ruolo): void
    {
        $this->ruolo = $ruolo;
    }


    // METHODS
    
}
