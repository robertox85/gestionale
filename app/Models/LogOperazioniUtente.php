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


class LogOperazioniUtente extends BaseModel {

	#[PrimaryKey, Hidden]

	protected int $id_log;

	#[ForeignKey, Required]

	protected int $id_utente;

	#[LabelColumn]

	protected string $azione;

	#[DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $data_azione;

	protected string $dettagli;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $created_at;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $updated_at;
}
