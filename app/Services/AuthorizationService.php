<?php

namespace App\Services;

use App\Models\Permesso;
use App\Models\User;
use App\Models\Ruolo;

class AuthorizationService
{
    public function checkAccess(User $user, $uri)
    {
        // Implementa la logica di autorizzazione qui
        // Puoi utilizzare ruoli, permessi o altre regole di autorizzazione definite nel tuo sistema

        // Recupera i ruoli dell'utente
        $roles = $user->getRoles();

        // Recupera i permessi corrispondenti all'URI
        $permessi = Permesso::getByUri($uri);

        // Verifica se l'utente ha almeno uno dei ruoli consentiti
        foreach ($roles as $role) {
            if ($this->checkRolePermissions($role, $permessi)) {
                return true; // L'utente ha accesso consentito
            }
        }

        return false; // L'accesso non è consentito per l'utente e l'URI specificati
    }

    private function checkRolePermissions(Ruolo $role, $permissions)
    {
        // Verifica se il ruolo ha almeno uno dei permessi consentiti
        foreach ($permissions as $permission) {
            if ($role->hasPermission($permission)) {
                return true; // Il ruolo ha accesso consentito
            }
        }

        return false; // Il ruolo non ha accesso consentito
    }
}
