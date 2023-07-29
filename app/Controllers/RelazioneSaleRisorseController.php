<?php

namespace App\Controllers;

use App\Models\RelazioneSaleRisorse;
use App\Libraries\QueryBuilder;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;

class RelazioneSaleRisorseController extends BaseController {

	public function index() {
		$qb = new QueryBuilder($this->db);
		$qb = $qb->setTable('RelazioneSaleRisorse');
		// Seleziona tutte le colonne dalla tabella con alias per l'ID
		$qb = $qb->select('*');
		$qb = $qb->setAlias('id_relaziona', 'id');
		$rows = $qb->get();
		$pagination = $qb->getPagination();
		$columns = $qb->getColumns();
		// Puoi personalizzare la vista utilizzata per l'elenco
		echo $this->view->render('list.html.twig', compact('columns', 'rows', 'pagination'));
		exit();
	}

	public function create(): void
	{
		$entity = new RelazioneSaleRisorse();
		$formComponent = new DynamicFormComponent($entity);

		$formData = [];
		$formData['action'] = $this->url('relazionesalerisorse/store');
		$formData['csrf_token'] = Helper::generateToken('RelazioneSaleRisorse');
		$formData['button_label'] = 'Crea';

		$formHtml = $formComponent->renderForm($formData);

		// Puoi personalizzare la vista utilizzata per il form di creazione
		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function edit($id)
	{
		$relazionesalerisorse = RelazioneSaleRisorse::find($id);
		if (!$relazionesalerisorse) {
			Helper::addError('Record non trovato.');
			Helper::redirect('/relazionesalerisorse');
			exit();
		}
		$formComponent = new DynamicFormComponent($relazionesalerisorse);

		$formData = [];
		$formData['action'] = $this->url('relazionesalerisorse/update');
		$formData['csrf_token'] = Helper::generateToken('RelazioneSaleRisorse');
		$formData['id_relaziona'] = $id;
		$formData['button_label'] = 'Edit';

		$formHtml = $formComponent->renderEditForm($formData);

		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function store()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('RelazioneSaleRisorse', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/relazionesalerisorse');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = RelazioneSaleRisorse::create($post);

			if ($newId !== false) {
				Helper::addSuccess('Nuovo record creato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/relazionesalerisorse');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/relazionesalerisorse');
			exit();
		}
	}

	public function update()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('RelazioneSaleRisorse', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/relazionesalerisorse');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = RelazioneSaleRisorse::update($post);

			if ($newId !== false) {
				Helper::addSuccess('Record aggiornato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/relazionesalerisorse');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/relazionesalerisorse');
			exit();
		}
	}

	public function delete($id) {
		try {
			(new RelazioneSaleRisorse)->delete($id);
			Helper::addSuccess('Record eliminato con successo!');
			$current_page = Helper::getCurrentPage();
			Helper::redirect('/' . $current_page);
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/RelazioneSaleRisorse');
			exit();
		}
	}

	public function bulkDelete() {
		try {
			$qb = new QueryBuilder($this->db);
			$qb = $qb->setTable('RelazioneSaleRisorse');
			$ids = $_POST['ids'];
			// Turn into array if not already
			if (!is_array($ids)) {
				$ids = explode(',', $ids);
				$ids = array_filter($ids);
				$ids = array_map('intval', $ids);
			}
			$qb = $qb->whereIn('id_relaziona', $ids);
			$qb = $qb->delete();
			$qb->execute();
			Helper::addSuccess('Record eliminati con successo!');
			$current_page = Helper::getCurrentPage();
			Helper::redirect('/' . $current_page);
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			$current_page = Helper::getCurrentPage();
			Helper::redirect('/' . $current_page);
			exit();
		}
	}
}
