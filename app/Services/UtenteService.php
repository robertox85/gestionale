<?php

namespace App\Services;

use App\Controllers\BaseController;
use App\Models\Utente;

class UtenteService extends BaseController
{
    protected $utenteRepository;

    public function __construct(Utente $utenteRepository)
    {
        parent::__construct();
        $this->utenteRepository = $utenteRepository;
    }

    public function createUtente()
    {
        $fields = ['nome', 'cognome', 'email', 'password', 'id_ruolo'];
        foreach ($fields as $field) {
            $this->utenteRepository->setFieldIfExistInPost($field);
        }
        $this->utenteRepository->save();
    }

    public function updateUtente()
    {
        $fields = ['nome', 'cognome', 'email', 'password', 'id_ruolo'];
        foreach ($fields as $field) {
            $this->utenteRepository->setFieldIfExistInPost($field);
        }
        $this->utenteRepository->update();
    }

    // createAnagrafica, updateAnagrafica, deleteAnagrafica
    public function createAnagrafica()
    {
        $fields = ['nome', 'cognome', 'codice_fiscale', 'data_nascita', 'luogo_nascita', 'indirizzo', 'citta', 'cap', 'provincia', 'telefono', 'cellulare', 'email', 'pec', 'id_utente'];
        foreach ($fields as $field) {
            $this->utenteRepository->setFieldIfExistInPost($field);
        }
        $this->utenteRepository->save();
    }

    public function updateAnagrafica()
    {
        $fields = ['nome', 'cognome', 'codice_fiscale', 'data_nascita', 'luogo_nascita', 'indirizzo', 'citta', 'cap', 'provincia', 'telefono', 'cellulare', 'email', 'pec', 'id_utente'];
        foreach ($fields as $field) {
            $this->utenteRepository->setFieldIfExistInPost($field);
        }
        $this->utenteRepository->update();
    }

    public function deleteAnagrafica()
    {
        $this->utenteRepository->delete();
    }



}