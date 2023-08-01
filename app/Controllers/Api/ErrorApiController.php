<?php

namespace App\Controllers\Api;

use App\Controllers\Web\BaseController;
use App\Libraries\ResponseHelper;

class ErrorApiController extends BaseController
{
    public function notFoundView()
    {
        header('HTTP/1.1 404 Not Found');
        echo ResponseHelper::jsonResponse([
            'error' => '404 - Not Found',
        ]);
        exit();
    }

    public function notAllowedView()
    {
        header('HTTP/1.1 405 Not Allowed');
        echo ResponseHelper::jsonResponse([
            'error' => '405 - Not Allowed',
        ]);
        exit();
    }

    public function internalErrorView()
    {
        header('HTTP/1.1 500 Internal Error');
        echo ResponseHelper::jsonResponse([
            'error' => '500 - Internal Error',
        ]);
        exit();
    }

    public function forbiddenView()
    {
        header('HTTP/1.1 403 Forbidden');
        echo ResponseHelper::jsonResponse([
            'error' => '403 - Forbidden',
        ]);
        exit();
    }

}