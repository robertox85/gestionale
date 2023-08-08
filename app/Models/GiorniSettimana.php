<?php

namespace App\Models;

class GiorniSettimana extends BaseModel {

	private int $id_giorno = 0;

	public function getIdGiorno() {
		return $this->id_giorno;
	}

	public function setIdGiorno($id_giorno) {
		$this->id_giorno = $id_giorno;
	}

	private string $nome_giorno = '';

	public function getNomeGiorno() {
		return $this->nome_giorno;
	}

	public function setNomeGiorno($nome_giorno) {
		$this->nome_giorno = $nome_giorno;
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
