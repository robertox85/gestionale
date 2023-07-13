<?php

namespace App\Middleware;

use App\Controllers\ErrorController;
use App\Libraries\Helper;
use App\Models\Utente;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthorizationMiddleware
{
    protected $permessi;

    public function __construct($permessi = [])
    {
        if (!is_array($permessi)) {
            $permessi = [$permessi];
        }

        $this->permessi = $permessi;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        // Verifica che l'utente sia autenticato e abbia i permessi richiesti
        $user = $_SESSION['utente'];
        $permessi = Utente::getPermessiUtente($user['id']);
        $authorized = false;
        foreach ($this->permessi as $permesso) {
            if (in_array($permesso, $permessi)) {
                $authorized = true;
                break;
            }
        }

        if ($authorized) {
            // Utente autorizzato, prosegui
            $response = $next($request, $response);
        } else {

            // Utente non autorizzato, mostra un errore
            header('Location: /403');
            exit;
        }
    }
}