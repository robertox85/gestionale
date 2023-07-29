<?php

namespace App\Controllers;

use App\Models\PreferenzeUtenteSale;
use App\Libraries\QueryBuilder;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;

class PreferenzeUtenteSaleController extends BaseController {

	public function index() {
		$qb = new QueryBuilder($this->db);
		$qb = $qb->setTable('PreferenzeUtenteSale');
		// Seleziona tutte le colonne dalla tabella con alias per l'ID
		$qb = $qb->select('*');
		$qb = $qb->setAlias('id_preferenza', 'id');
		$rows = $qb->get();
		$pagination = $qb->getPagination();
		$columns = $qb->getColumns();
		// Puoi personalizzare la vista utilizzata per l'elenco
		echo $this->view->render('list.html.twig', compact('columns', 'rows', 'pagination'));
		exit();
	}

	public function create(): void
	{
		$entity = new PreferenzeUtenteSale();
		$formComponent = new DynamicFormComponent($entity);

		$formData = [];
		$formData['action'] = $this->url('preferenzeutentesale/store');
		$formData['csrf_token'] = Helper::generateToken('PreferenzeUtenteSale');
		$formData['button_label'] = 'Crea';

		$formHtml = $formComponent->renderForm($formData);

		// Puoi personalizzare la vista utilizzata per il form di creazione
		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function edit($id)
	{
		$preferenzeutentesale = PreferenzeUtenteSale::find($id);
		if (!$preferenzeutentesale) {
			Helper::addError('Record non trovato.');
			Helper::redirect('/preferenzeutentesale');
			exit();
		}
		$formComponent = new DynamicFormComponent($preferenzeutentesale);

		$formData = [];
		$formData['action'] = $this->url('preferenzeutentesale/update');
		$formData['csrf_token'] = Helper::generateToken('PreferenzeUtenteSale');
		$formData['id_preferenza'] = $id;
		$formData['button_label'] = 'Edit';

		$formHtml = $formComponent->renderEditForm($formData);

		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function store()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('PreferenzeUtenteSale', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/preferenzeutentesale');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = PreferenzeUtenteSale::create($post);

			if ($newId !== false) {
				Helper::addSuccess('Nuovo record creato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/preferenzeutentesale');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/preferenzeutentesale');
			exit();
		}
	}

	public function update()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('PreferenzeUtenteSale', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/preferenzeutentesale');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = PreferenzeUtenteSale::update($post);

			if ($newId !== false) {
				Helper::addSuccess('Record aggiornato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/preferenzeutentesale');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/preferenzeutentesale');
			exit();
		}
	}

	public function delete($id) {
		try {
			(new PreferenzeUtenteSale)->delete($id);
			Helper::addSuccess('Record eliminato con successo!');
			$current_page = Helper::getCurrentPage();
			Helper::redirect('/' . $current_page);
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/PreferenzeUtenteSale');
			exit();
		}
	}

	public function bulkDelete() {
		try {
			$qb = new QueryBuilder($this->db);
			$qb = $qb->setTable('PreferenzeUtenteSale');
			$ids = $_POST['ids'];
			// Turn into array if not already
			if (!is_array($ids)) {
				$ids = explode(',', $ids);
				$ids = array_filter($ids);
				$ids = array_map('intval', $ids);
			}
			$qb = $qb->whereIn('id_preferenza', $ids);
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
