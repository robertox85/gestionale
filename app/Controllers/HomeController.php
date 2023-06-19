<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function homeView(): void
    {
        echo $this->view->render('home.html.twig');
    }

    public function publicView()
    {
        echo 'Public View';
    }

}