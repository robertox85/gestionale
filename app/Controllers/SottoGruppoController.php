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
        $sottogruppi = ( $id !== null ) ? SottoGruppo::getByGroup($id) : SottoGruppo::getAll();
        // add NomeGruppo to the result
        $sottogruppi = array_map(function ($sottogruppo) {
            $sottogruppo->nome_gruppo = Gruppo::getNomeGruppoById($sottogruppo->id_gruppo);
            return $sottogruppo;
        }, $sottogruppi);

        $sottogruppi = array_map(function ($sottogruppo) {
            $utenti = SottoGruppo::getUtentiInSottogruppo($sottogruppo->id_sottogruppo);
            return new SottoGruppo($sottogruppo->id_sottogruppo, $sottogruppo->nome_sottogruppo, $sottogruppo->id_gruppo, Gruppo::getNomeGruppoById($sottogruppo->id_gruppo), $utenti);
        }, $sottogruppi);

        echo $this->view->render('sottogruppi.html.twig', ['sottogruppi' => $sottogruppi ]);
        exit;
    }

}