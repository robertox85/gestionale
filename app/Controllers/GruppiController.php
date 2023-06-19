<?php

namespace App\Controllers;

use App\Models\Gruppo;
use App\Models\SottoGruppo;
use App\Models\User;
use App\Models\Utenti;

class GruppiController extends BaseController
{
    public function gruppiView()
    {
        $gruppi = Gruppo::getAll();
        $utenti = Utenti::getAll();
        echo $this->view->render('gruppi.html.twig',
            [
                'gruppi' => $gruppi,
                'utenti' => $utenti,
                'sottogruppi' => [],
            ]
        );
    }

    public function createGruppo()
    {
       // TODO: implementare la creazione di un gruppo
    }



}