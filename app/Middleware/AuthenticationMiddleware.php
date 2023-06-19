<?php

namespace App\Middleware;

use App\Libraries\Helper;
use App\Models\Ruolo;
use App\Models\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthenticationMiddleware
{
    protected $roles;

    public function __construct(array | string $roles = [])
    {
        // Se è stato passato un singolo ruolo, lo trasforma in un array
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        // Se non è stato passato alcun ruolo, l'utente deve essere autenticato con qualsiasi ruolo
        if (empty($roles)) {
            $roles = Ruolo::getAll();
            $roles = array_map(function ($role) {
                return $role->getName();
            }, $roles);
        }

        $this->roles = $roles;
    }

    public function __invoke(Request $request, Response $response, $next): Response | null
    {
        // Verifica se l'utente è autenticato e ha uno dei ruoli consentiti
        if ($this->isAuthenticatedUser() && $this->hasRequiredRole()) {
            // Utente autenticato e ruolo corretto, passa alla gestione successiva
            return $next($request, $response);
        } elseif (!$this->isAuthenticatedUser() && $this->hasGuestRole()) { // forse questo if non serve, perchè le rotte pubbliche non passano da qui
            // Utente non autenticato ma rotta pubblica, passa alla gestione successiva
            return $next($request, $response);
        } else {
            // Ruolo non autorizzato o utente non autenticato
            Helper::addError('Devi essere autenticato per accedere a questa pagina');
            return Helper::redirect('sign-in?returnUrl=' . urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
        }
    }

    private function isAuthenticatedUser()
    {
        // Aggiungi qui la logica per verificare se l'utente è autenticato
        // Puoi utilizzare qualsiasi metodo di autenticazione tu abbia implementato nel tuo sistema
        // Restituisci true se l'utente è autenticato, altrimenti false
        return isset($_SESSION['user']);
    }

    private function hasRequiredRole()
    {

        // Aggiungi qui la logica per verificare se l'utente ha almeno uno dei ruoli consentiti
        // Puoi utilizzare il ruolo dell'utente memorizzato nella sessione o nel database
        // Restituisci true se l'utente ha il ruolo corretto, altrimenti false
        $user = User::findByIdUtente($_SESSION['user']['id']);
        $userRole = Ruolo::getById($user->getRuoloId())->getName();

        return in_array($userRole, $this->roles);
    }

    private function hasGuestRole()
    {
        // Aggiungi qui la logica per verificare se la rotta è pubblica (guest)
        // Restituisci true se la rotta è pubblica, altrimenti false
        return in_array('guest', $this->roles);
    }
}
