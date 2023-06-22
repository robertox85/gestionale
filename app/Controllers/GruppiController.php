<?php

namespace App\Controllers;

use App\Models\Gruppo;
use App\Models\SottoGruppo;
use App\Models\Utente;

use App\Libraries\Database;

class GruppiController extends BaseController
{
    public function gruppiView()
    {
        $gruppi = Gruppo::getAll();
        $gruppi = array_map(function ($gruppo) {
            $gruppo = new Gruppo($gruppo->id_gruppo);
            $sottogruppi = SottoGruppo::getByGroup($gruppo->getId());
            $gruppo->setSottogruppi($sottogruppi);
            return $gruppo;
        }, $gruppi);

        echo $this->view->render('gruppi.html.twig',
            [
                'gruppi' => $gruppi,
            ]
        );
    }



    public function creaGruppoView()
    {
        $utenti = Utente::getAll();
        echo $this->view->render('crea_gruppo.html.twig',
            [
                'utenti' => $utenti,
            ]
        );
    }

    // editGruppoView
    public function editGruppoView(int $id_gruppo)
    {
        $gruppo = new Gruppo($id_gruppo);
        $sottogruppi = $gruppo->getSottoGruppi();
        // convert array of objects to array of arrays

        $utenti = Utente::getAll();

        echo $this->view->render('edit_gruppo.html.twig',
            [
                'gruppo' => $gruppo,
                'sottogruppi' => $sottogruppi,
                'utenti' => $utenti,
            ]
        );
    }




    public function creaGruppo()
    {
        try {
            // Validazione dei dati
            $nomeGruppo = $_POST['nome_gruppo'];
            $sottogruppi = $_POST['sottogruppi'];
            // Creazione del gruppo
            $gruppo = new Gruppo();
            $gruppo->setNomeGruppo($nomeGruppo);
            $gruppo->save();

            // Avvio della transazione
            Database::beginTransaction();

            // Aggiunta dei sottogruppi al gruppo
            foreach ($sottogruppi as $sottogruppo) {
                $sottogruppo_id = $gruppo->addSottoGruppo($sottogruppo);
                if ($sottogruppo_id) {
                    $sottogruppoObj = new SottoGruppo($sottogruppo_id);
                    $utenti = $sottogruppo['utenti'];
                    // Aggiunta degli utenti al sottogruppo
                    foreach ($utenti as $utente) {
                        $sottogruppoObj->addUtente($utente);
                    }
                } else {
                    // Annullamento della transazione in caso di errore
                    Database::rollBack();
                    throw new \Exception("Errore durante l'aggiunta dei sottogruppi");
                }
            }



            // Conferma della transazione
            Database::commit();

            header('Location: /gruppi');
        } catch (\Exception $e) {
            // Gestione delle eccezioni
            echo $e->getMessage();
        }
    }


    // editGruppo POST
    public function editGruppo() {
        $id = $_POST['id_gruppo'];

        // Validazione dei dati
        $nomeGruppo = $_POST['nome_gruppo'];
        $sottogruppi = $_POST['sottogruppi'];

        // Creazione del gruppo
        $gruppo = new Gruppo($id);
        $gruppo->setNomeGruppo($nomeGruppo);
        $gruppo->update();

        // Avvio della transazione
        Database::beginTransaction();

        // Variabile per tenere traccia degli errori
        $errori = false;

        // Aggiunta dei sottogruppi al gruppo
        foreach ($sottogruppi as $sottogruppo) {
            try {
                $sottogruppo_id = $sottogruppo['id_sottogruppo'];

                $updateSottogruppo = $gruppo->updateSottogruppo($sottogruppo);
                if (!$updateSottogruppo) {
                    $errori = true; // Aggiornamento non riuscito
                }

                $sottogruppoObj = new SottoGruppo($sottogruppo_id);
                $utenti = $sottogruppo['utenti'];

                // Aggiunta degli utenti al sottogruppo
                $sottogruppoObj->clearUtentiSottogruppo();
                foreach ($utenti as $utente) {
                    $sottogruppoObj->addUtente($utente);
                }
            } catch (\Exception $e) {
                Database::rollBack();
                throw new \Exception("Errore durante l'aggiunta dei sottogruppi");
            }
        }

        // Conferma della transazione
        Database::commit();

        // Gestione dei risultati
        if ($errori) {
            // Si sono verificati errori durante l'aggiornamento
            // Esegui le azioni appropriate per gestire gli errori
        } else {
            // Nessun errore, il processo può continuare
            // Esegui le azioni appropriate per il successo dell'aggiornamento
        }

        header('Location: /gruppi/edit/' . $id);
    }


    // deletegruppo
    public function deleteGruppo($id)
    {
        try {
            $gruppo = new Gruppo($id);
            $sottoGruppi = $gruppo->getSottoGruppi();
            foreach ($sottoGruppi as $sottoGruppo) {
                $id = $sottoGruppo['id_sottogruppo'];

                $sottoGruppo = new SottoGruppo($id);

                // clearUtentiSottogruppo
                $sottoGruppo->clearUtentiSottogruppo();

                // clearPraticheSottogruppo
                $sottoGruppo->clearPraticheSottogruppo();

                $sottoGruppo->delete();
            }

            $gruppo->delete();



            header('Location: /gruppi');


        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


}