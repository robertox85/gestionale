<?php

namespace App\Controllers;

use App\Libraries\Database;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;
use App\Libraries\Pagination;
use App\Libraries\QueryBuilder;
use App\Models;
use App\Models\DisponibilitaSala;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends BaseController
{
    public function homeView(): void

    {
        $qb = new QueryBuilder($this->db);
        $qb = $qb->setTable('Utenti');
        $qb = $qb->select('id_utente as id, nome, cognome, email');

        echo $this->view->render('list.html.twig', [
            'columns' => ['ID', 'Nome', 'Cognome', 'Email'],
            'rows' => $qb->get(),
            'pagination' => $qb->getPagination()
        ]);
        exit();
    }

    public function delete($id)
    {
        $qb = new QueryBuilder($this->db);
        $qb = $qb->setTable('Utenti');
        $qb = $qb->where('id_utente', $id);
        $qb = $qb->delete();
        $qb->execute();
        $current_page = Helper::getCurrentPage();
        Helper::redirect('/' . $current_page);
        exit();
    }


    public function newItem()
    {

        $entity = new DisponibilitaSala();
        $formComponent = new DynamicFormComponent($entity);

        $formHtml = $formComponent->renderForm([
            'action' => 'url_to_handle_form_submission',
            'csrf_token' => 'your_csrf_token_here',
            'id' => '', // Se vuoi modificare un elemento esistente
            'button_label' => 'Save', // Etichetta del pulsante (opzionale)
            // Altri dati del form se necessario
        ]);

        echo $this->view->render('newform.html.twig', [
            'formHtml' => $formHtml,
        ]);
    }

    public function bulkDelete()
    {
        $qb = new QueryBuilder($this->db);
        $qb = $qb->setTable('Utenti');
        $ids = $_POST['ids'];
        // turn into array if not already
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
            $ids = array_filter($ids);
            $ids = array_map('intval', $ids);
        }
        $qb = $qb->whereIn('id_utente', $ids);
        $qb = $qb->delete();
        $qb->execute();
        $current_page = Helper::getCurrentPage();
        Helper::redirect('/' . $current_page);
        exit();
    }

    public function publicView()
    {
        echo 'Public View';
    }

}