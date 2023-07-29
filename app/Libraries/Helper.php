<?php

namespace App\Libraries;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfToken;
use function PHPUnit\Framework\exactly;

class Helper
{
    public static function generateToken($tokenId): string
    {
        $tokenManager = new CsrfTokenManager();
        $token = $tokenManager->getToken($tokenId);
        return $token->getValue();
    }

    public static function validateToken(string $action, string $token): bool
    {
        $csrfTokenManager = new CsrfTokenManager();
        $csrfToken = new CsrfToken($action, $token);
        return $csrfTokenManager->isTokenValid($csrfToken);
    }

    public static function addSuccess(string $message): void
    {
        $_SESSION['alerts']['success'][] = $message;
    }

    public static function addError(string $message): void
    {
        $_SESSION['alerts']['error'][] = $message;
    }

    public static function addWarning(string $message): void
    {
        $_SESSION['alerts']['warning'][] = $message;
    }

    public static function addInfo(string $message): void
    {
        $_SESSION['alerts']['info'][] = $message;
    }

    public static function getCurrentPage(): string
    {
        $queryString = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
        $lastPage = basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));
        if ($queryString) {
            $lastPage .= '?' . $queryString;
        }
        return $lastPage;
    }

    public static function redirect(string $page = ''): void
    {
        // TODO: refactor this
        $page = str_replace($_ENV['BASE_URL'], '', $page);

        // remove / from $page if exists and is not the only character
        if (strlen($page) > 1)
            $page = rtrim($page, '/');


        // remove base url from $page if exists
        $redirect = $page ?: Helper::getCurrentPage();

        header("Location: $redirect");
        exit;
    }

    static function generateBreadcrumb()
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        $breadcrumbs = [];
        $breadcrumbPath = '';

        foreach ($segments as $segment) {
            $label = ucfirst($segment);
            // remove - and _ from label
            $label = str_replace(['-', '_'], ' ', $label);
            // Uppercase first letter of each word
            $label = ucwords($label);
            $breadcrumbPath .= '/' . $segment;
            $breadcrumbs[] = [
                'label' => $label, // Puoi personalizzare il modo in cui viene visualizzato il segmento
                'url' => $breadcrumbPath, // Puoi personalizzare la generazione dell'URL
            ];

            // if is a number, remove it
            if (is_numeric($segment)) {
                array_pop($breadcrumbs);
            }
        }

        // Aggiungi la pagina corrente alla fine del breadcrumb
        $breadcrumbs[count($breadcrumbs) - 1]['url'] = '';
        // Aggiungi la home all'inizio del breadcrumb
        array_unshift($breadcrumbs, [
            'label' => 'Home',
            'url' => '/',
        ]);

        return $breadcrumbs;
    }

    public static function getPageTitle()
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        $pageTitle = '';

        foreach ($segments as $segment) {
            $pageTitle .= ucfirst($segment) . ' - ';
        }

        $pageTitle = rtrim($pageTitle, ' - ');

        // if PageTitle is empty, set it to Home
        if (empty($pageTitle)) {
            $pageTitle = 'Home';
        }

        return $pageTitle;
    }

    public static function getSingularName(string $pluralClassName)
    {
        // Espressioni regolari per individuare le terminazioni comuni dei nomi plurali
        $pluralEndings = [
            '/i$/' => 'e',         // es. "Utenti" diventa "Utente"
            '/che$/' => 'ca',      // es. "DisponibilitaSale" diventa "DisponibilitaSala"
            '/zi$/' => 'zio',         // es. "Servizi" diventa "Servizio"
            '/le$/' => 'la',         // es. "DisponibilitaSale" diventa "DisponibilitaSala"
            '/e$/' => 'a',         // es. "DisponibilitaSale" diventa "DisponibilitaSala"
            // Aggiungi altre regole se necessario
        ];

        // Verifica se il nome plurale corrisponde a una delle regole
        foreach ($pluralEndings as $pattern => $singularEnding) {
            if (preg_match($pattern, $pluralClassName)) {
                return preg_replace($pattern, $singularEnding, $pluralClassName);
            }
        }

        // Se nessuna corrispondenza con le regole, rimuovi una "s" finale (implementazione fallback)
        if (substr($pluralClassName, -1) === 's') {
            return substr($pluralClassName, 0, -1);
        }

        // Se non è possibile trovare il nome singolare, ritorna semplicemente il nome plurale
        return $pluralClassName;
    }

    public static function getPluralName(string $shortClassName)
    {
        // in Italiano
        if (substr($shortClassName, -2) === 'ca') {
            return substr($shortClassName, 0, -2) . 'che';
        } elseif (substr($shortClassName, -1) === 'o') {
            return substr($shortClassName, 0, -1) . 'i';
        } elseif (substr($shortClassName, -1) === 'e') {
            return substr($shortClassName, 0, -1) . 'i';
        } elseif (substr($shortClassName, -1) === 'a') {
            return substr($shortClassName, 0, -1) . 'e';
        } elseif (substr($shortClassName, -1) === 'i') {
            return substr($shortClassName, 0, -1) . 'i';
        } elseif (substr($shortClassName, -1) === 'u') {
            return substr($shortClassName, 0, -1) . 'i';
        } elseif (substr($shortClassName, -1) === 's') {
            return $shortClassName;
        } else {
            return $shortClassName . 's';
        }
    }

    public static function getFullTableName(string $shortClassName)
    {
        // $shortClassName can be Utenti, or LogOperazioni. When is CamelCase, split into words
        return preg_replace('/(?<!^)([A-Z])/', ' $1', $shortClassName);
    }

    public static function getTablePrimaryKeyName($fullTableName): string
    {

        // if $fullTableName is a ReflectionClass, get the short name
        if (is_object($fullTableName)) {
            $fullTableName = $fullTableName->getShortName();
        }

        $fullTableName = self::getFullTableName($fullTableName);


        $fullTableName = explode(' ', $fullTableName);

        // if the table name is a single word, return the singular name
        if (count($fullTableName) === 1) {
            return self::getSingularName($fullTableName[0]);
        }

        // if the table name is multiple words, return the singular name of the start word
        return self::getSingularName($fullTableName[0]);
    }

    public static function sanificaInput(array|null $dati = null, array $ignore_keys = [])
    {
        if (is_null($dati)) {
            return [];
        }
        $sanitized = [];
        foreach ($dati as $key => $value) {
            if (in_array($key, $ignore_keys)) {
                $sanitized[$key] = $value;
            } else if (is_array($value)) {
                $sanitized[$key] = self::sanificaInput($value);
            } else {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }
        return $sanitized;
    }
}