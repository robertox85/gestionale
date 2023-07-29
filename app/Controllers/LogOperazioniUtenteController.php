<?php

namespace App\Controllers;

use App\Models\LogOperazioniUtente;
use App\Libraries\QueryBuilder;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;

class LogOperazioniUtenteController extends BaseController {

	public function index() {
		$qb = new QueryBuilder($this->db);
		$qb = $qb->setTable('LogOperazioniUtente');
		// Seleziona tutte le colonne dalla tabella con alias per l'ID
		$qb = $qb->select('*');
		$qb = $qb->setAlias('id_log', 'id');
		$rows = $qb->get();
		$pagination = $qb->getPagination();
		$columns = $qb->getColumns();
		// Puoi personalizzare la vista utilizzata per l'elenco
		echo $this->view->render('list.html.twig', compact('columns', 'rows', 'pagination'));
		exit();
	}

	public function create(): void
	{
		$entity = new LogOperazioniUtente();
		$formComponent = new DynamicFormComponent($entity);

		$formData = [];
		$formData['action'] = $this->url('log-operazioni-utente/store');
		$formData['csrf_token'] = Helper::generateToken('LogOperazioniUtente');
		$formData['button_label'] = 'Crea';

		$formHtml = $formComponent->renderForm($formData);

		// Puoi personalizzare la vista utilizzata per il form di creazione
		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function edit($id)
	{
		$logoperazioniutente = LogOperazioniUtente::find($id);
		if (!$logoperazioniutente) {
			Helper::addError('Record non trovato.');
			Helper::redirect('/log-operazioni-utente');
			exit();
		}
		$formComponent = new DynamicFormComponent($logoperazioniutente);

		$formData = [];
		$formData['action'] = $this->url('log-operazioni-utente/update');
		$formData['csrf_token'] = Helper::generateToken('LogOperazioniUtente');
		$formData['id_log'] = $id;
		$formData['button_label'] = 'Edit';

		$formHtml = $formComponent->renderForm($formData);

		echo $this->view->render('newform.html.twig', compact('formHtml'));
	}

	public function store()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('LogOperazioniUtente', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/log-operazioni-utente');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = LogOperazioniUtente::create($post);

			if ($newId !== false) {
				Helper::addSuccess('Nuovo record creato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/log-operazioni-utente');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/log-operazioni-utente');
			exit();
		}
	}

	public function update()
	{
		try {
			$post = $_POST;

			// Verifica il token CSRF
			if (!Helper::validateToken('LogOperazioniUtente', $post['csrf_token'])) {
				Helper::addError('Token CSRF non valido.');
				Helper::redirect('/log-operazioni-utente');
				exit();
			}

			unset($post['csrf_token']);

			$post = Helper::sanificaInput($post);

			$newId = LogOperazioniUtente::update($post);

			if ($newId !== false) {
				Helper::addSuccess('Record aggiornato con successo.');
			} else {
				Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
			}

			Helper::redirect('/log-operazioni-utente');
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/log-operazioni-utente');
			exit();
		}
	}

	public function delete($id) {
		try {
			(new LogOperazioniUtente)->delete($id);
			Helper::addSuccess('Record eliminato con successo!');
			$current_page = Helper::getCurrentPage();
			Helper::redirect('/' . $current_page);
			exit();
		} catch (\Exception $e) {
			Helper::addError($e->getMessage());
			Helper::redirect('/log-operazioni-utente');
			exit();
		}
	}

	public function bulkDelete() {
		try {
			$qb = new QueryBuilder($this->db);
			$qb = $qb->setTable('LogOperazioniUtente');
			$ids = $_POST['ids'];
			// Turn into array if not already
			if (!is_array($ids)) {
				$ids = explode(',', $ids);
				$ids = array_filter($ids);
				$ids = array_map('intval', $ids);
			}
			$qb = $qb->whereIn('id_log', $ids);
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
