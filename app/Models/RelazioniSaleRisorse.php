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


class RelazioniSaleRisorse extends BaseModel {

	#[PrimaryKey, Hidden]

	protected int $id_relazione;

	#[ForeignKey, Required]

	protected int $id_sala;

	#[ForeignKey, Required]

	protected int $id_risorsa;

	protected int $quantita;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $created_at;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $updated_at;
}
