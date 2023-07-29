<?php

namespace App\Libraries;

class ResponseHelper
{

    public static function jsonResponse($data, $statusCode = 200): string|bool
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        return json_encode($data);

    }

}