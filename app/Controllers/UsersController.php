<?php

namespace App\Controllers;

use App\Libraries\Database;
use App\Libraries\Pagination;
use App\Libraries\QueryBuilder;

class UsersController extends BaseController
{
    public function usersView(): void
    {
        $db = Database::getInstance();
        $qb = new QueryBuilder($db->getConnection());

        $totalUsers = $qb->reset()->setTable('Utenti')->select('COUNT(*) AS total')->get()[0]['total'];

        $pagination = new Pagination($this->getPagingParams());
        $pagination->setTotalItems($totalUsers);

        $users = $qb->setTable('Utenti')
            ->select('*')
            ->orderBy($pagination->getOrderBy(), $pagination->getDirection())
            ->limit($pagination->getLimit())
            ->offset($pagination->getOffset())
            ->get();

        echo $this->view->render('utenti.html.twig',
            [
                'columns' => ['ID_utente', 'nome', 'cognome', 'email', 'ruolo'],
                'entities' => $users,
                'pagination' => $pagination->getPagination()
            ]
        );
    }

    public function usersAddView(): void
    {
        echo $this->view->render('users-add.html.twig');
    }

    public function usersEditView(): void
    {
        echo $this->view->render('users-edit.html.twig');
    }

    public function usersDeleteView(): void
    {
        echo $this->view->render('users-delete.html.twig');
    }
}