<?php

namespace App\Models;

class EccezioniSale extends BaseModel {

	private int $id_eccezione;

	public function getIdEccezione() {
		return $this->id_eccezione;
	}

	public function setIdEccezione($id_eccezione) {
		$this->id_eccezione = $id_eccezione;
	}

	private int $id_sala;

	public function getIdSala() {
		return $this->id_sala;
	}

	public function setIdSala($id_sala) {
		$this->id_sala = $id_sala;
	}

	private mixed $data_inizio;

	public function getDataInizio() {
		return $this->data_inizio;
	}

	public function setDataInizio($data_inizio) {
		$this->data_inizio = $data_inizio;
	}

	private mixed $data_fine;

	public function getDataFine() {
		return $this->data_fine;
	}

	public function setDataFine($data_fine) {
		$this->data_fine = $data_fine;
	}

	private string $motivo;

	public function getMotivo() {
		return $this->motivo;
	}

	public function setMotivo($motivo) {
		$this->motivo = $motivo;
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
