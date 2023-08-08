<?php

namespace App\Models;

class DisponibilitaGiorni extends BaseModel {

	private int $id_disponibilita = 0;

	public function getIdDisponibilita() {
		return $this->id_disponibilita;
	}

	public function setIdDisponibilita($id_disponibilita) {
		$this->id_disponibilita = $id_disponibilita;
	}

	private int $id_giorno = 0;

	public function getIdGiorno() {
		return $this->id_giorno;
	}

	public function setIdGiorno($id_giorno) {
		$this->id_giorno = $id_giorno;
	}
}
