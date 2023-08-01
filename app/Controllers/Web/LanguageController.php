<?php

namespace App\Controllers\Web;

use App\Controllers\Web\BaseController;

class LanguageController extends BaseController
{
    public function setLanguage(): void
    {
        $locale = $_GET['_locale'] ?? 'it';
        $_SESSION['language'] = $locale;
        $returnUrl = $_GET['returnUrl'] ?? '/';
        header("Location: $returnUrl");

    }
}