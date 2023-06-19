<?php

namespace App\Middleware;

use App\Models\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthorizationMiddleware
{
    protected $permessi;

    public function __construct($permessi)
    {
        $this->permessi = $permessi;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        // Verifica che l'utente sia autenticato e abbia i permessi richiesti
        $user = $_SESSION['user'];
        $permessi = User::getUserPermissions($user['id']);
        $permessi = array_map(function ($permission) {
            return $permission->nome_permesso;
        }, $permessi);

        if (isset($_SESSION['user']) && in_array($this->permessi, $permessi)) {
            return $next($request, $response);
        }

        // Ruolo o permesso non autorizzato, reindirizza o restituisci una risposta di errore
        // ...

        echo "Non hai i permessi per accedere a questa pagina";

        return $response;
    }
}