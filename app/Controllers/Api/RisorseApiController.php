<?php

namespace App\Controllers\Api;

use App\Controllers\Web\BaseController;
use App\Libraries\QueryBuilder;
use App\Models\Risorse;
use App\Libraries\ResponseHelper;

class RisorseApiController extends BaseController {

	public function index(): void
	{
		$qb = new QueryBuilder($this->db);
		$qb = $qb->setTable('Risorse');
		$rows = $qb->get();
		$pagination = $qb->getPagination();
		$columns = $qb->getColumns();
		echo ResponseHelper::jsonResponse([
			'rows' => $rows,
			'pagination' => $pagination,
			'columns' => $columns,
		]);
	}

	public function edit($id)
	{
		$risorse = Risorse::find($id);
		if (!$risorse) {
			echo ResponseHelper::jsonResponse([
				'error' => 'Record non trovato.',
			]);
			exit();
		}
		echo ResponseHelper::jsonResponse([
			'risorse' => $risorse,
		]);
		exit();
	}

	public function create(): void
	{
		$entity = new Risorse();
		echo ResponseHelper::jsonResponse([
			'entity' => $entity,
		]);
	}

	public function store(): void
	{
		try {
			$post = $_POST;
			// Validazione e altre operazioni per l'inserimento dei dati
			// ...
			$newId = Risorse::create($post);
			if ($newId !== false) {
				echo ResponseHelper::jsonResponse([
					'success' => 'Record creato con successo.',
				]);
			} else {
				echo ResponseHelper::jsonResponse([
					'error' => 'Errore durante la creazione del record.',
				]);
			}
		} catch (\Exception $e) {
			echo ResponseHelper::jsonResponse([
				'error' => $e->getMessage(),
			]);
		}
	}

	public function update(): void
	{
		try {
			$post = $_POST;
			// Validazione e altre operazioni per l'aggiornamento dei dati
			// ...
			$newId = Risorse::update($post);
			if ($newId !== false) {
				echo ResponseHelper::jsonResponse([
					'success' => 'Record aggiornato con successo.',
				]);
			} else {
				echo ResponseHelper::jsonResponse([
					'error' => 'Errore durante l\'aggiornamento del record.',
				]);
			}
		} catch (\Exception $e) {
			echo ResponseHelper::jsonResponse([
				'error' => $e->getMessage(),
			]);
		}
	}

	public function delete($id): void
	{
		$risorse = Risorse::find($id);
		if (!$risorse) {
			echo ResponseHelper::jsonResponse([
				'error' => 'Record non trovato.',
			]);
			exit();
		}
		$risorse->delete();
		echo ResponseHelper::jsonResponse([
			'success' => 'Record eliminato con successo.',
		]);
	}
}
