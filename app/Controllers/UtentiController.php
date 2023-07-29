<?php

namespace App\Controllers;

use App\Models\Utenti;
use App\Libraries\QueryBuilder;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;

class UtentiController extends BaseController {

	public function index() {
		$qb = new QueryBuilder($this->db);
		$qb = $qb->setTable('Utenti');
		// Seleziona tutte le colonne dalla tabella con alias per l'ID
		$qb = $qb->select('*');
		$qb = $qb->setAlias('id_utente', 'id');
		$rows = $qb->get();
		$pagination = $qb->getPagination();
		$columns = $qb->getColumns();
		// Puoi personalizzare la vista utilizzata per l'elenco
		echo $this->view->render('list.html.twig', compact('columns', 'rows', 'pagination'));
		exit();
	}

	public function create(): void
	{
		$entity = new Utenti();
		$formComponent = new DynamicFormComponent($entity);

		$formData = [];
		$formData['action'] = $this->url('utenti/store');
		$formData['csrf_token'] = Helper::generateToken('Utenti');
		$formData['button_label'] = 'Crea';

		$formHtml = $formComponent->renderForm($formData);

		// Puoi personalizzare la vista utilizzata per il form di creazione
		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function edit($id)
	{
		$utenti = Utenti::find($id);
		if (!$utenti) {
			Helper::addError('Record non trovato.');
			Helper::redirect('/utenti');
			exit();
		}
		$formComponent = new DynamicFormComponent($utenti);

		$formData = [];
		$formData['action'] = $this->url('utenti/update');
		$formData['csrf_token'] = Helper::generateToken('Utenti');
		$formData['id_utente'] = $id;
		$formData['button_label'] = 'Edit';

		$formHtml = $formComponent->renderForm($formData);

		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function store()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('Utenti', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/utenti');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = Utenti::create($post);

			if ($newId !== false) {
				Helper::addSuccess('Nuovo record creato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/utenti');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/utenti');
			exit();
		}
	}

	public function update()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('Utenti', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/utenti');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = Utenti::update($post);

			if ($newId !== false) {
				Helper::addSuccess('Record aggiornato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/utenti');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/utenti');
			exit();
		}
	}

	public function delete($id) {
		try {
			(new Utenti)->delete($id);
			Helper::addSuccess('Record eliminato con successo!');
			$current_page = Helper::getCurrentPage();
			Helper::redirect('/' . $current_page);
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/utenti');
			exit();
		}
	}

	public function bulkDelete() {
		try {
			$qb = new QueryBuilder($this->db);
			$qb = $qb->setTable('Utenti');
			$ids = $_POST['ids'];
			// Turn into array if not already
			if (!is_array($ids)) {
				$ids = explode(',', $ids);
				$ids = array_filter($ids);
				$ids = array_map('intval', $ids);
			}
			$qb = $qb->whereIn('id_utente', $ids);
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
