<?php

namespace App\Controllers;

class ErrorController extends BaseController
{
    public function notFoundView()
    {
        echo $this->view->render('404.html.twig');
    }

    public function notAllowedView()
    {
        echo $this->view->render('405.html.twig');
    }

    public function internalErrorView()
    {
        echo $this->view->render('500.html.twig');
    }

}