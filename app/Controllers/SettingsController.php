<?php

namespace App\Controllers;

use App\Libraries\Helper;
use App\Models\Ruolo;
use App\Models\Permesso;
class SettingsController extends BaseController
{
    public function impostazioniView()
    {
        $ruoli = Ruolo::getAll();
        // add permessi to each ruolo
        $ruoli = array_map(function ($ruolo) {
            $permessi = Permesso::getByRuoloId($ruolo->id);
            $ruolo->permessi = $permessi;
            return $ruolo;
        }, $ruoli);
        $permessi = Permesso::getAll();
        $ruoloId = (isset($_GET['ruolo'])) ? (int) $_GET['ruolo'] : null;
        $ruoloSelezionato = null;
        foreach ($ruoli as $ruolo) {
            if ($ruolo->id === $ruoloId) {
                $ruoloSelezionato = $ruolo;
                break;
            }
        }
        echo $this->view->render('impostazioni.html.twig', [
            'ruoli' => $ruoli,
            'permessi' => $permessi,
            'ruoloSelezionato' => $ruoloSelezionato
        ]);
    }

    public function aggiornaRuolo()
    {
        try {
            $ruoloId = (int) $_POST['id_ruolo'] ?? 0;
            $permessi = $_POST['permessi'] ?? [];
            $ruolo = Ruolo::getById($ruoloId);
            if ($ruolo === null) {
                Helper::addError('Ruolo non trovato');
                Helper::redirect('/impostazioni');
                return;
            }

            $ruolo->eliminaPermessi();

            foreach ($permessi as $permesso_id) {
                $ruolo->setPermessoRuolo($permesso_id);
            }

            $ruolo->update();

            Helper::addSuccess('Ruolo aggiornato');
            Helper::redirect('/impostazioni?ruolo=' . $ruoloId);
        } catch (\Exception $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handleException($e);
        }
    }
}