<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function homeView(): void
    {
        // if is not logged in redirect to login page
        if (!$this->auth->isLoggedIn()) {
            header('Location: /sign-in');
            exit;
        }

        echo $this->view->render('home.html.twig');
    }

    public function publicView()
    {
        echo 'Public View';
    }

}