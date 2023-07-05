<?php

namespace App\Controllers;

use GuzzleHttp\Psr7\Request;
use Twig\Environment;
use App\Libraries\TwigConfigurator;
use App\Libraries\TwigGlobalVars;

abstract class BaseController
{
    protected Environment $view;


    public function __construct()
    {
        $this->view = TwigConfigurator::configure();
        TwigGlobalVars::addGlobals($this->view);
    }
}