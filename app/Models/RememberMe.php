<?php

namespace App\Models;

class RememberMe extends BaseModel {

	private int $id_remember = 0;

	public function getIdRemember() {
		return $this->id_remember;
	}

	public function setIdRemember($id_remember) {
		$this->id_remember = $id_remember;
	}

	private int $id_utente = 0;

	public function getIdUtente() {
		return $this->id_utente;
	}

	public function setIdUtente($id_utente) {
		$this->id_utente = $id_utente;
	}

	private string $token = '';

	public function getToken() {
		return $this->token;
	}

	public function setToken($token) {
		$this->token = $token;
	}

	private mixed $expires_at = null;

	public function getExpiresAt() {
		return $this->expires_at;
	}

	public function setExpiresAt($expires_at) {
		$this->expires_at = $expires_at;
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
