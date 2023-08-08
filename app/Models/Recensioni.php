<?php

namespace App\Models;

class Recensioni extends BaseModel {

	private int $id_recensione = 0;

	public function getIdRecensione() {
		return $this->id_recensione;
	}

	public function setIdRecensione($id_recensione) {
		$this->id_recensione = $id_recensione;
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

	private int $valutazione = 0;

	public function getValutazione() {
		return $this->valutazione;
	}

	public function setValutazione($valutazione) {
		$this->valutazione = $valutazione;
	}

	private mixed $commento = null;

	public function getCommento() {
		return $this->commento;
	}

	public function setCommento($commento) {
		$this->commento = $commento;
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
