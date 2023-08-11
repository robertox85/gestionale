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


class DisponibilitaGiorni extends BaseModel {

	#[PrimaryKey, Hidden]

	protected int $id_disponibilita;

	#[PrimaryKey, Hidden]

	protected int $id_giorno;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $created_at;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $updated_at;
}
