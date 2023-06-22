<?php

namespace App\Services;

use App\Models\Utente;

class AuthenticationService
{
    private static $instance;
    private $user;

    private function __construct()
    {
        // Inizializza eventuali dipendenze o carica l'utente autenticato
        $this->loadUserFromSession();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function authenticateUser($credentials)
    {
        // Implementa la logica di autenticazione qui
        // Ad esempio, verifica le credenziali dell'utente nel tuo sistema di autenticazione

        // Esempio:
        $email = $credentials['email'];
        $password = $credentials['password'];

        // Effettua la logica di autenticazione specifica del tuo sistema
        // Ad esempio, verifica le credenziali nel database degli utenti
        $user = Utente::getByPropertyName('email', $email);

        if (!$user) {
            return false; // L'autenticazione non ha avuto successo
        }

        if (!$user->verifyPassword($password)) {
            return false; // L'autenticazione non ha avuto successo
        }

        $this->user = $user;
        $this->storeUserInSession();

        return true; // L'autenticazione ha avuto successo
    }

    public function logoutUser()
    {
        // Implementa la logica di logout qui
        // Ad esempio, elimina le informazioni dell'utente dalla sessione o invalida il token di autenticazione

        $this->user = null;
        $this->removeUserFromSession();
    }

    private function loadUserFromSession()
    {
        // Carica l'utente dalla sessione, se presente
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $this->user = Utente::getById($userId);
        }
    }

    private function storeUserInSession()
    {
        // Salva l'ID dell'utente nella sessione
        $_SESSION['user_id'] = $this->user->getId();
    }

    private function removeUserFromSession()
    {
        // Rimuovi l'ID dell'utente dalla sessione
        unset($_SESSION['user_id']);
    }
}
