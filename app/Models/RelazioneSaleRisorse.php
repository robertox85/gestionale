<?php

namespace App\Models;

class RelazioneSaleRisorse extends BaseModel {

	private int $id_relazione;

	public function getIdRelazione() {
		return $this->id_relazione;
	}

	public function setIdRelazione($id_relazione) {
		$this->id_relazione = $id_relazione;
	}

	private int $id_sala;

	public function getIdSala() {
		return $this->id_sala;
	}

	public function setIdSala($id_sala) {
		$this->id_sala = $id_sala;
	}

	private int $id_risorsa;

	public function getIdRisorsa() {
		return $this->id_risorsa;
	}

	public function setIdRisorsa($id_risorsa) {
		$this->id_risorsa = $id_risorsa;
	}

	private int $quantità;

	public function getQuantità() {
		return $this->quantità;
	}

	public function setQuantità($quantità) {
		$this->quantità = $quantità;
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
