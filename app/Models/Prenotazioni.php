<?php

namespace App\Models;

use App\Attributes\Checboxes;
use App\Attributes\DateFormat;
use App\Attributes\DropDown;
use App\Attributes\ForeignKey;
use App\Attributes\Hidden;
use App\Attributes\LabelColumn;
use App\Attributes\PrimaryKey;
use App\Attributes\Required;


class Prenotazioni extends BaseModel {

	#[PrimaryKey, Hidden]

	protected int $id_prenotazione;

	#[ForeignKey, Required]

	protected int $id_utente;

	#[ForeignKey, Required]

	protected int $id_sala;

	#[DateFormat("d/m/Y")]

	protected ?\DateTime $giorno;

	#[DateFormat("H:i:s")]

	protected ?\DateTime $data_ora_inizio;

	#[DateFormat("H:i:s")]

	protected ?\DateTime $data_ora_fine;

	#[DropDown]

	protected array $ricorrenza = ['settimanale', 'mensile', 'nessuna'];

	#[Required, DateFormat("d/m/Y")]

	protected ?\DateTime $fine_ricorrenza;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $created_at;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $updated_at;
}
