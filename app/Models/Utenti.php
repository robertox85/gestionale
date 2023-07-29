<?php

namespace App\Models;

class Utenti extends BaseModel {

	private int $id_utente;

	public function getIdUtente() {
		return $this->id_utente;
	}

	public function setIdUtente($id_utente) {
		$this->id_utente = $id_utente;
	}

	private string $nome;

	public function getNome() {
		return $this->nome;
	}

	public function setNome($nome) {
		$this->nome = $nome;
	}

	private string $cognome;

	public function getCognome() {
		return $this->cognome;
	}

	public function setCognome($cognome) {
		$this->cognome = $cognome;
	}

	private string $email;

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	private string $password;

	public function getPassword() {
		return $this->password;
	}

	public function setPassword($password) {
		$this->password = $password;
	}

	private mixed $ruolo;

	public function getRuolo() {
		return $this->ruolo;
	}

	public function setRuolo($ruolo) {
		$this->ruolo = $ruolo;
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
