<?php

namespace App\Middleware;

use App\Libraries\Database;
use App\Libraries\Helper;
use App\Libraries\QueryBuilder;
use App\Models\Utenti;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthenticationMiddleware
{
    protected mixed $roles;

    public function __construct(string|array $roles = null)
    {
        if (empty($roles)) {
            $roles = ['admin', 'user'];
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $this->roles = $roles;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        if ($this->isAuthenticatedUser() && $this->hasRequiredRole()) {
            return $next($request, $response);
        }

        if ($this->isGuestUser()) {
            return $next($request, $response);
        }

        header('HTTP/1.1 401 Unauthorized');
        Helper::redirect('/401');
        exit;

    }

    private function hasRequiredRole(): bool
    {
        return in_array($_SESSION['utente']['ruolo'], $this->roles);
    }

    /**
     * @throws \Exception
     */
    private function isAuthenticatedUser(): bool
    {
        if (!isset($_SESSION['utente']) && isset($_COOKIE['remember_me'])) {
            if ($this->validateCookie($_COOKIE['remember_me'])) {
                $this->loginWithCookie($_COOKIE['remember_me']);
            }
        }
        return isset($_SESSION['utente']);
    }
    
    private function loginWithCookie(string $cookie): void
    {
        $_SESSION['utente'] = $this->getUserFromCookie($cookie);
    }

    private function validateCookie(string $cookie): bool
    {
        $this->deleteExpiredCookies();

        $db = Database::getInstance();
        $qb = new QueryBuilder($db);
        $qb->setTable('RememberMe');
        $qb->select('token');
        $qb->where('token', $cookie, '=');
        $qb->where('expires_at', date('Y-m-d H:i:s'), '>=');
        $qb->limit(1);

        return (bool)$qb->first();
    }

    private function getUserFromCookie(string $cookie): array
    {
        $db = Database::getInstance();
        $qb = new QueryBuilder($db);
        $qb->setTable('RememberMe');
        $qb->select('id_utente');
        $qb->where('token', $cookie, '=');
        $qb->where('expires_at', date('Y-m-d H:i:s'), '>=');
        $qb->limit(1);
        $result = $qb->first();
        $utente = new Utenti($result['id_utente']);

        return [
            'id' => $utente->getId(),
            'email' => $utente->getEmail(),
            'nome' => $utente->getNome(),
            'cognome' => $utente->getCognome(),
            'ruolo' => $utente->getRuolo(),
        ] ?? [];
    }

    private function deleteExpiredCookies(): void
    {
        $db = Database::getInstance();
        $qb = new QueryBuilder($db);
        $qb->setTable('RememberMe');
        $qb->where('expires_at', date('Y-m-d H:i:s'), '<');
        $qb->delete();
    }

    private function isGuestUser(): bool
    {
        return !$this->isAuthenticatedUser() && in_array('guest', $this->roles);
    }

}
