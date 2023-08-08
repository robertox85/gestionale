<?php

namespace App\Models;

use DateTime;

class EccezioniSale extends BaseModel {
	private int $id_eccezione = 0;
	private int $id_sala = 0;
	private ?DateTime $data_inizio = null;
	private ?DateTime $data_fine = null;
	private string $motivo = '';
	private mixed $created_at;
	private mixed $updated_at;
}
