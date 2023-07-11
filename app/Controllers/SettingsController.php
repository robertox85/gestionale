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

    // creaPermesso
    public function creaPermesso()
    {
        try {
            $request_body = file_get_contents('php://input');
            $data = json_decode($request_body, true);
            $permesso = new Permesso();
            $permesso->setNome($data['nome']);
            $permesso->setDescrizione($data['descrizione']);
            $permesso->save();
            echo json_encode(
                [
                    'id' => $permesso->getId(),
                    'nome' => $permesso->getNome(),
                    'descrizione' => $permesso->getDescrizione()
                ]
            );
            return;
        } catch (\Exception $e) {
            echo json_encode(
                [
                    'error' => true,
                    'message' => $e->getMessage()
                ]
            );
            return;
        }
    }

    // eliminaPermesso
    public function eliminaPermesso($id)
    {
        try {
            $permesso = Permesso::getById($id);
            $permesso->delete();

            Helper::addSuccess('Permesso eliminato');
            Helper::redirect('/impostazioni');

        } catch (\Exception $e) {
            Helper::addError($e->getMessage());
            Helper::redirect('/impostazioni');
        }
    }
}