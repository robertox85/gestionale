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


class EccezioniSale extends BaseModel {

	#[PrimaryKey, Hidden]

	protected int $id_eccezione;

	#[ForeignKey, Required]

	protected int $id_sala;

	#[DateFormat("d/m/Y")]

	protected ?\DateTime $data_inizio;

	#[DateFormat("d/m/Y")]

	protected ?\DateTime $data_fine;

	#[DateFormat("H:i:s")]

	protected ?\DateTime $ora_inizio;

	#[DateFormat("H:i:s")]

	protected ?\DateTime $ora_fine;

	#[LabelColumn]

	protected string $motivo;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $created_at;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $updated_at;
}
