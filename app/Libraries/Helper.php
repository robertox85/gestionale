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
        $queryString = (isset($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY) : '');
        $lastPage = (isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH) : '');
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
        {
            $page = rtrim($page, '/');
        }


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

    public static function getTableNameFromForeignKey(string $tableName, string $foreignKey)
    {
        $sql = "SELECT REFERENCED_TABLE_NAME ";
        $sql .= "FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE ";
        $sql .= "WHERE TABLE_NAME = '$tableName' ";
        $sql .= "AND COLUMN_NAME = '$foreignKey' ";
        $sql .= "AND REFERENCED_TABLE_NAME IS NOT NULL;";

        $qb = new QueryBuilder(Database::getInstance());
        $qb->setTable('INFORMATION_SCHEMA.KEY_COLUMN_USAGE');
        $qb->select('REFERENCED_TABLE_NAME');
        $qb->where('TABLE_NAME', $tableName, '=');
        $qb->where('COLUMN_NAME', $foreignKey, '=');
        $qb->where('REFERENCED_TABLE_NAME', 'NULL', 'IS NOT');
        $result = $qb->rawSQL($sql);


        return (count($result) > 0) ? $result[0]['REFERENCED_TABLE_NAME'] : null;
    }
}