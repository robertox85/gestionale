<?php

namespace App\Libraries;

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfToken;

class Helper
{

    // start session if not started
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function generateToken($tokenId): string
    {
        $tokenManager = new CsrfTokenManager();
        $token = $tokenManager->getToken($tokenId);
        return $token->getValue();
    }

    // validateToken
    public static function validateToken(string $action, string $token): bool
    {
        $csrfTokenManager = new CsrfTokenManager();
        $csrfToken = new CsrfToken($action, $token);
        return $csrfTokenManager->isTokenValid($csrfToken);
    }

    public static function breadcrumb($url)
    {
        $crumbs = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
        $breadcrumb = array();
        $link = '';
        // add home if not empty
        if (!empty($crumbs)) {
            $item = array(
                'name' => 'Home',
                'link' => $_ENV['BASE_URL']
            );
            array_push($breadcrumb, $item);
        }
        foreach ($crumbs as $crumb) {
            $link .= '/' . $crumb;
            $name = ucwords(str_replace(array('.php', '_'), array('', ' '), $crumb));
            $name = str_replace('-', ' ', $name);
            $item = array(
                'name' => $name,
                'link' => $link
            );
            array_push($breadcrumb, $item);
        }

        // if last item is empty or numeric, remove it
        if (empty(end($breadcrumb)['name']) || is_numeric(end($breadcrumb)['name'])) {
            array_pop($breadcrumb);
        }
        // if last item is random string (token), remove it
        if (preg_match('/^[a-zA-Z0-9]+$/', end($breadcrumb)['name'])) {
            array_pop($breadcrumb);
        }

        return $breadcrumb;
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

    /**
     * @throws TwoFactorAuthException
     */
    public static function generateSecret(): string
    {
        $tfa = new TwoFactorAuth();
        return $tfa->createSecret();
    }

    public static function extractString(): void
    {
        $dir = dirname(__DIR__, 2);
        $directory = new RecursiveDirectoryIterator($dir . '/views');
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.twig$/i', RecursiveRegexIterator::GET_MATCH);

        $translationKeys = [];

        foreach ($regex as $file) {
            $fileContent = file_get_contents($file[0]);

            preg_match_all('/{% trans %}(.*?){% endtrans %}/s', $fileContent, $matches);

            foreach ($matches[1] as $match) {
                $translationKeys[] = $match;
            }
        }

        $translationKeys = array_unique($translationKeys);

        print_r($translationKeys);
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
        exit();
    }

}