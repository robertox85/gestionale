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


class DisponibilitaSale extends BaseModel {

	#[PrimaryKey, Hidden]

	protected int $id_disponibilita;

	#[ForeignKey, Required]

	protected int $id_sala;

	#[DateFormat("H:i:s")]

	protected ?\DateTime $orario_apertura;

	#[DateFormat("H:i:s")]

	protected ?\DateTime $orario_chiusura;

	#[DropDown]

	protected array $durata_slot_minuti = ['15', '30', '45', '60', '90', '120'];

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $created_at;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $updated_at;
}
