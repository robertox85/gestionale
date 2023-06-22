<?php

namespace App\Controllers;

use App\Models\Gruppo;
use App\Models\SottoGruppo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SottoGruppoController extends BaseController
{
    public function sottogruppiView( $id = null )
    {
        // $sottogruppi = ( $id !== null ) ? SottoGruppo::getByGroup($id) : SottoGruppo::getAll();
        $sottogruppi = SottoGruppo::getAll();

        echo $this->view->render('sottogruppi.html.twig',
            [
                'sottogruppi' => $sottogruppi
            ]
        );
        exit;
    }

}