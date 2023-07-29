<?php

namespace App\Models;

class Risorse extends BaseModel {

	private int $id_risorsa;

	public function getIdRisorsa() {
		return $this->id_risorsa;
	}

	public function setIdRisorsa($id_risorsa) {
		$this->id_risorsa = $id_risorsa;
	}

	private string $nome_risorsa;

	public function getNomeRisorsa() {
		return $this->nome_risorsa;
	}

	public function setNomeRisorsa($nome_risorsa) {
		$this->nome_risorsa = $nome_risorsa;
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

    // Definisci un metodo astratto per ottenere il nome del campo da utilizzare come valore per le opzioni del campo select

}
