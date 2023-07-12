<?php

namespace App\Libraries;

use Twig\Environment;
use Twig\TwigFunction;

class TwigGlobalVars
{
    public static function addGlobals(Environment $twig): void
    {
        if (!empty($_SESSION)) {
            $twig->addGlobal('session', $_SESSION);
        }

        // add breadcrumbs
        $twig->addGlobal('breadcrumbs', Helper::generateBreadcrumb());

        // if is localhost, add the global variable 'isLocalhost' to the twig template
        $twig->addGlobal('isLocalhost', $_ENV['APP_ENV'] === 'local');



        self::addTwigFunctions($twig);

    }

    private static function addTwigFunctions(Environment $twig): void
    {
        $twigFunctions = [
            'isLoginPage' => function () {
                return str_contains($_SERVER['REQUEST_URI'], 'sign-in');
            },
            'assets' => function (string $filename) {
                // return full server path to the public folder
                return $_ENV['BASE_URL'] . '/' . $filename;
            },
            'navigationUrl' => function (string $routeName) {
                // remove duplcate // from the $routeName
                $routeName = str_starts_with($routeName, '/') ? $routeName : "/$routeName";
                return $_ENV['BASE_URL'] . $routeName;

            },
            'url' => function (string $routeName, array $params = []) {

                // get the current query string
                $currentQueryString = $_SERVER['QUERY_STRING'] ?? '';


                $routeName = str_starts_with($routeName, '/') ? $routeName : "/$routeName";

                // if the current query string is not empty, append it to the new query string
                if ($currentQueryString) {
                    // remove duplicate from $currentQueryString the parameters that are already in $params
                    $currentQueryString = http_build_query(array_diff_key($_GET, $params));
                }

                $query = http_build_query($params);
                $query = $query ? "$currentQueryString&$query" : $currentQueryString;
                $url = $_ENV['BASE_URL'] . $routeName . ($query ? "?$query" : '');


                // remove ?route={*} from the query string if exists, and if i'm not in login or register page
                if (str_contains($url, '?route=') && !str_contains($url, 'sign-in') && !str_contains($url, 'register')) {
                    // unset the route parameter from the query string
                    $url = preg_replace('/&?route=[^&]+/', '', $url);
                }

                return $url;
            },
            'csrf_token' => function (string $tokenId = 'authenticate') {
                return Helper::generateToken($tokenId) ?? '';
            },
            'is_active' => function (string $routeName) {
                return str_starts_with($_SERVER['REQUEST_URI'], $routeName);
            },
            'add_active_class' => function (string $routeName) {
                return str_starts_with($_SERVER['REQUEST_URI'], $routeName) ? 'active' : '';
            },
            'add_bold_class' => function (string $routeName) {
                return str_starts_with($_SERVER['REQUEST_URI'], $routeName) ? 'font-weight-bold' : '';
            },
            'clear_notifications' => function () {
                if (isset($_SESSION['alerts'])) {
                    unset($_SESSION['alerts']);
                }
            },
            'dump' => function ($var) {
                echo '<pre>';
                var_dump($var);
                echo '</pre>';
            },
            'getAuthQRCode' => function (string $username, string $secret = '') {
                $tfa = new \RobThree\Auth\TwoFactorAuth();
                return $tfa->getQRCodeImageAsDataUri('Demo site: '.$username, $secret);
            },
            'svg' => function (string $filename) {

                // check if file exists
                if (!file_exists(__DIR__ . "/../../public/images/flags/$filename.svg")) {
                    return '';
                }

                $svg = file_get_contents(__DIR__ . "/../../public/images/flags/$filename.svg");

                return str_replace('<svg', '<svg class="rounded-full"', $svg);

            },
            'getSortDirection' => function (string $column) {
                $sort = $_GET['sort'] ?? '';
                $direction = $_GET['direction'] ?? '';

                if ($sort === $column) {
                    return $direction === 'asc' ? 'desc' : 'asc';
                }

                return 'desc';
            },
            'getQueryParams' => function (string $param = '') {
                $params = $_GET;

                if ($param) {
                    return $params[$param] ?? '';
                }

                return $params;
            },
            // add PageClass to the body tag
            'getPageClass' => function () {
                $routeName = $_SERVER['REQUEST_URI'] ?? '';
                // remove id from the route name (e.g. /pratiche/edit/12)
                $routeName = preg_replace('/\/\d+/', '', $routeName);
                $routeName = str_starts_with($routeName, '/') ? $routeName : "/$routeName";
                $routeName = str_replace('/', '-', $routeName);
                // remove first '-' from the route name
                $routeName = substr($routeName, 1);
                // remove query string from the route name
                $routeName = explode('?', $routeName)[0];

                return $routeName;
            },
        ];

        foreach ($twigFunctions as $name => $function) {
            $twig->addFunction(new TwigFunction($name, $function));
        }
    }
}
