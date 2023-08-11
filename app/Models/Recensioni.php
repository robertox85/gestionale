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


class Recensioni extends BaseModel {

	#[PrimaryKey, Hidden]

	protected int $id_recensione;

	#[ForeignKey, Required]

	protected int $id_utente;

	#[ForeignKey, Required]

	protected int $id_sala;

	#[DropDown]

	protected array $valutazione = ['1', '2', '3', '4', '5'];

	#[LabelColumn]

	protected string $commento;

	protected mixed $pubblicata;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $created_at;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $updated_at;
}
