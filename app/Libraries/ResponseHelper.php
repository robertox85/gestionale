<?php

namespace App\Libraries;

class ResponseHelper
{

    public static function jsonResponse($data, $statusCode = 200): string|bool
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: http://api.local:8000');
        header('Access-Control-Allow-Origin: http://localhost:8000');


        http_response_code($statusCode);
        return json_encode($data);

    }

}