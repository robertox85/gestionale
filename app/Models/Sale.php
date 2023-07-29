<?php

namespace App\Models;

class Sale extends BaseModel {

	private int $id_sala;

	public function getIdSala() {
		return $this->id_sala;
	}

	public function setIdSala($id_sala) {
		$this->id_sala = $id_sala;
	}

	private string $nome_sala;

	public function getNomeSala() {
		return $this->nome_sala;
	}

	public function setNomeSala($nome_sala) {
		$this->nome_sala = $nome_sala;
	}

	private int $capacita;

	public function getCapacita() {
		return $this->capacita;
	}

	public function setCapacita($capacita) {
		$this->capacita = $capacita;
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
