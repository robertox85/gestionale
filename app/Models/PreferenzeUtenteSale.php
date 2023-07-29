<?php

namespace App\Models;

class PreferenzeUtenteSale extends BaseModel {

	private int $id_preferenza;

	public function getIdPreferenza() {
		return $this->id_preferenza;
	}

	public function setIdPreferenza($id_preferenza) {
		$this->id_preferenza = $id_preferenza;
	}

	private int $id_utente;

	public function getIdUtente() {
		return $this->id_utente;
	}

	public function setIdUtente($id_utente) {
		$this->id_utente = $id_utente;
	}

	private int $id_sala;

	public function getIdSala() {
		return $this->id_sala;
	}

	public function setIdSala($id_sala) {
		$this->id_sala = $id_sala;
	}

	private mixed $created_at;

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}

	private mixed $updated_at;

	public function getUpdatedAt() {
		return $this->updated_at;
	}

	public function setUpdatedAt($updated_at) {
		$this->updated_at = $updated_at;
	}
}
