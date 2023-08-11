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


class Utenti extends BaseModel {

	#[PrimaryKey, Hidden]

	protected int $id_utente;

	#[LabelColumn]

	protected string $nome;

	protected string $cognome;

	protected string $email;

	protected string $password;

	#[DropDown]

	protected array $ruolo = ['admin', 'dipendente', 'guest'];

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $created_at;

	#[Required, Hidden, DateFormat("d/m/Y H:i:s")]

	protected ?\DateTime $updated_at;
}
