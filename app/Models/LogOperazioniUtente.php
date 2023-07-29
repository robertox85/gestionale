<?php

namespace App\Models;

class LogOperazioniUtente extends BaseModel {

	private int $id_log;

	public function getIdLog() {
		return $this->id_log;
	}

	public function setIdLog($id_log) {
		$this->id_log = $id_log;
	}

	private int $id_utente;

	public function getIdUtente() {
		return $this->id_utente;
	}

	public function setIdUtente($id_utente) {
		$this->id_utente = $id_utente;
	}

	private string $azione;

	public function getAzione() {
		return $this->azione;
	}

	public function setAzione($azione) {
		$this->azione = $azione;
	}

	private mixed $data_azione;

	public function getDataAzione() {
		return $this->data_azione;
	}

	public function setDataAzione($data_azione) {
		$this->data_azione = $data_azione;
	}

	private string $dettagli;

	public function getDettagli() {
		return $this->dettagli;
	}

	public function setDettagli($dettagli) {
		$this->dettagli = $dettagli;
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
