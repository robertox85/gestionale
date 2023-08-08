<?php

namespace App\Controllers\Web;

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

    public function forbiddenView()
    {
        echo $this->view->render('403.html.twig');
    }

    public function unauthorizedView()
    {
        echo $this->view->render('401.html.twig');
    }
}