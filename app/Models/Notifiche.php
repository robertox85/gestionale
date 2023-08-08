<?php

namespace App\Models;

class Notifiche extends BaseModel {

	private int $id_notifica = 0;

	public function getIdNotifica() {
		return $this->id_notifica;
	}

	public function setIdNotifica($id_notifica) {
		$this->id_notifica = $id_notifica;
	}

	private int $id_utente = 0;

	public function getIdUtente() {
		return $this->id_utente;
	}

	public function setIdUtente($id_utente) {
		$this->id_utente = $id_utente;
	}

	private mixed $messaggio = null;

	public function getMessaggio() {
		return $this->messaggio;
	}

	public function setMessaggio($messaggio) {
		$this->messaggio = $messaggio;
	}

	private mixed $data_invio = null;

	public function getDataInvio() {
		return $this->data_invio;
	}

	public function setDataInvio($data_invio) {
		$this->data_invio = $data_invio;
	}

	private mixed $stato = null;

	public function getStato() {
		return $this->stato;
	}

	public function setStato($stato) {
		$this->stato = $stato;
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
