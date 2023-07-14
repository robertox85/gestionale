<?php

namespace App\Libraries;

class Auth
{

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['utente']);
    }
}