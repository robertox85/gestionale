<?php

namespace App\Services;

use App\Controllers\BaseController;
use App\Models\Nota;
use App\Models\Pratica;
use App\Models\Scadenza;
use App\Models\Udienza;

class PraticaService extends BaseController
{
    protected $praticaRepository;

    public function __construct(Pratica $praticaRepository)
    {
        parent::__construct();
        $this->praticaRepository = $praticaRepository;
    }

    public function createPratica()
    {
        $fields = ['nome', 'tipologia', 'competenza', 'ruolo_generale', 'giudice', 'stato', 'id_gruppo'];
        foreach ($fields as $field) {
            $this->praticaRepository->setFieldIfExistInPost($field);
        }
        $this->praticaRepository->save();
    }

    public function updatePratica()
    {
        $fields = ['nome', 'tipologia', 'competenza', 'ruolo_generale', 'giudice', 'stato', 'id_gruppo'];
        foreach ($fields as $field) {
            $this->praticaRepository->setFieldIfExistInPost($field);
        }
        $this->praticaRepository->update();
    }

    public function updateScadenze()
    {
        $this->praticaRepository->clearScadenze();
        $this->saveScadenze();
    }

    public function updateUdienze()
    {
        $this->praticaRepository->clearUdienze();
        $this->saveUdienze();
    }

    public function updateNote()
    {
        $this->praticaRepository->clearNote();
        $this->saveNote();
    }

    public function saveScadenze() {
        $data = $this->praticaRepository->sanificaInput($_POST);
        $scadenze = $data['scadenze'];
        if (empty($scadenze)) {
            return;
        }
        foreach ($scadenze as $scadenzaData) {
            $scadenza = new Scadenza();
            $scadenza->setData($scadenzaData['data']);
            $scadenza->setMotivo($scadenzaData['motivo']);
            $scadenza->setIdPratica($this->praticaRepository->getId());
            $scadenza->save();
        }
    }

    public function saveUdienze() {
        $data = $this->praticaRepository->sanificaInput($_POST);
        $udienze = $data['udienze'];
        if (empty($udienze)) {
            return;
        }
        foreach ($udienze as $udienzaData) {
            $udienza = new Udienza();
            $udienza->setData($udienzaData['data']);
            $udienza->setDescrizione($udienzaData['descrizione']);
            $udienza->setIdPratica($this->praticaRepository->getId());
            $udienza->save();
        }
    }

    public function saveNote() {
        $data = $this->praticaRepository->sanificaInput($_POST);
        $note = $data['note'];
        if (empty($note)) {
            return;
        }
        foreach ($note as $notaData) {
            $nota = new Nota();
            $nota->setTipologia($notaData['tipologia']);
            $nota->setDescrizione($notaData['descrizione']);
            $nota->setVisibilita($notaData['visibilita']);
            $nota->setIdPratica($this->praticaRepository->getId());
            $nota->save();
        }
    }

    public function createAndSaveNrPratica()
    {
        $this->praticaRepository->setNrPratica(Pratica::generateNrPratica($this->praticaRepository->getIdGruppo()));
        $this->praticaRepository->update();
    }

    public function updateNrPratica(string $newNrPratica)
    {
        $this->praticaRepository->setNrPratica($newNrPratica);
        $this->praticaRepository->update();
    }
}