<?php

namespace App\Controllers;

use App\Libraries\ResponseHelper;
use App\Models\DisponibilitaSale;
use App\Libraries\QueryBuilder;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;

class DisponibilitaSaleApiController extends BaseController {

	public function index(): void
    {
		$qb = new QueryBuilder($this->db);
		$qb = $qb->setTable('DisponibilitaSale');
		// Seleziona tutte le colonne dalla tabella con alias per l'ID
		$qb = $qb->select('*');
		$qb = $qb->setAlias('id_disponibilita', 'id');
		$rows = $qb->get();
		$pagination = $qb->getPagination();
		$columns = $qb->getColumns();
		// Puoi personalizzare la vista utilizzata per l'elenco
		echo ResponseHelper::jsonResponse([
            'rows' => $rows,
            'pagination' => $pagination,
            'columns' => $columns
        ]);

	}
}
