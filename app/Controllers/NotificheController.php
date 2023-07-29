<?php

namespace App\Controllers;

use App\Models\Notifiche;
use App\Libraries\QueryBuilder;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;

class NotificheController extends BaseController {

	public function index() {
		$qb = new QueryBuilder($this->db);
		$qb = $qb->setTable('Notifiche');
		// Seleziona tutte le colonne dalla tabella con alias per l'ID
		$qb = $qb->select('*');
		$qb = $qb->setAlias('id_notifica', 'id');
		$rows = $qb->get();
		$pagination = $qb->getPagination();
		$columns = $qb->getColumns();
		// Puoi personalizzare la vista utilizzata per l'elenco
		echo $this->view->render('list.html.twig', compact('columns', 'rows', 'pagination'));
		exit();
	}

	public function create(): void
	{
		$entity = new Notifiche();
		$formComponent = new DynamicFormComponent($entity);

		$formData = [];
		$formData['action'] = $this->url('notifiche/store');
		$formData['csrf_token'] = Helper::generateToken('Notifiche');
		$formData['button_label'] = 'Crea';

		$formHtml = $formComponent->renderForm($formData);

		// Puoi personalizzare la vista utilizzata per il form di creazione
		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function edit($id)
	{
		$notifiche = Notifiche::find($id);
		if (!$notifiche) {
			Helper::addError('Record non trovato.');
			Helper::redirect('/notifiche');
			exit();
		}
		$formComponent = new DynamicFormComponent($notifiche);

		$formData = [];
		$formData['action'] = $this->url('notifiche/update');
		$formData['csrf_token'] = Helper::generateToken('Notifiche');
		$formData['id_notifica'] = $id;
		$formData['button_label'] = 'Edit';

		$formHtml = $formComponent->renderForm($formData);

		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function store()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('Notifiche', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/notifiche');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = Notifiche::create($post);

			if ($newId !== false) {
				Helper::addSuccess('Nuovo record creato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/notifiche');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/notifiche');
			exit();
		}
	}

	public function update()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('Notifiche', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/notifiche');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = Notifiche::update($post);

			if ($newId !== false) {
				Helper::addSuccess('Record aggiornato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/notifiche');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/notifiche');
			exit();
		}
	}

	public function delete($id) {
		try {
			(new Notifiche)->delete($id);
			Helper::addSuccess('Record eliminato con successo!');
			$current_page = Helper::getCurrentPage();
			Helper::redirect('/' . $current_page);
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/notifiche');
			exit();
		}
	}

	public function bulkDelete() {
		try {
			$qb = new QueryBuilder($this->db);
			$qb = $qb->setTable('Notifiche');
			$ids = $_POST['ids'];
			// Turn into array if not already
			if (!is_array($ids)) {
				$ids = explode(',', $ids);
				$ids = array_filter($ids);
				$ids = array_map('intval', $ids);
			}
			$qb = $qb->whereIn('id_notifica', $ids);
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
