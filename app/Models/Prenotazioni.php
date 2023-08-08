<?php

namespace App\Models;

class Prenotazioni extends BaseModel {

	private int $id_prenotazione = 0;

	public function getIdPrenotazione() {
		return $this->id_prenotazione;
	}

	public function setIdPrenotazione($id_prenotazione) {
		$this->id_prenotazione = $id_prenotazione;
	}

	private int $id_utente = 0;

	public function getIdUtente() {
		return $this->id_utente;
	}

	public function setIdUtente($id_utente) {
		$this->id_utente = $id_utente;
	}

	private int $id_sala = 0;

	public function getIdSala() {
		return $this->id_sala;
	}

	public function setIdSala($id_sala) {
		$this->id_sala = $id_sala;
	}

	private mixed $data_ora_inizio = null;

	public function getDataOraInizio() {
		return $this->data_ora_inizio;
	}

	public function setDataOraInizio($data_ora_inizio) {
		$this->data_ora_inizio = $data_ora_inizio;
	}

	private mixed $data_ora_fine = null;

	public function getDataOraFine() {
		return $this->data_ora_fine;
	}

	public function setDataOraFine($data_ora_fine) {
		$this->data_ora_fine = $data_ora_fine;
	}

	private mixed $ricorrenza = null;

	public function getRicorrenza() {
		return $this->ricorrenza;
	}

	public function setRicorrenza($ricorrenza) {
		$this->ricorrenza = $ricorrenza;
	}

	private mixed $fine_ricorrenza = null;

	public function getFineRicorrenza() {
		return $this->fine_ricorrenza;
	}

	public function setFineRicorrenza($fine_ricorrenza) {
		$this->fine_ricorrenza = $fine_ricorrenza;
	}

	private mixed $created_at = null;

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}

	private mixed $updated_at = null;

	public function getUpdatedAt() {
		return $this->updated_at;
	}

	public function setUpdatedAt($updated_at) {
		$this->updated_at = $updated_at;
	}
}
