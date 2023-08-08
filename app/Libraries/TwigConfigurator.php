<?php

namespace App\Libraries;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Twig\TwigFunction;

class TwigConfigurator
{
    public static function configure(): Environment
    {
        $directoryViews = self::getDirectoryViews();

        $loader = new FilesystemLoader($directoryViews);

        $twig = new Environment($loader, [
            'cache' => false,
            'debug' => false,
        ]);

        self::initTranslation($twig);

        self::addGlobals($twig);

        self::addTwigFunctions($twig);

        return $twig;
    }

    private static function initTranslation(Environment $twig): void
    {
        $translationsDir = dirname(__DIR__, 2) . '/translations';

        // if $translationsDir does not exist, create it
        if (!is_dir($translationsDir)) {
            mkdir($translationsDir);
        }

        $locale = $_SESSION['language'] ?? 'it';
        $translator = new Translator($locale);
        $translator->addLoader('yaml', new YamlFileLoader());

        $languages = array_diff(scandir($translationsDir), ['..', '.']);

        $availableLanguages = [];
        foreach ($languages as $language) {
            $lang = pathinfo($language, PATHINFO_FILENAME);
            $translator->addResource('yaml', $translationsDir . '/' . $language, $lang);
            $availableLanguages[$lang] = $lang;
        }

        $twig->addExtension(new TranslationExtension($translator));
        $twig->addGlobal('availableLanguages', $availableLanguages);
    }

    private static function getDirectoryViews($dir = null, $level = 0): string|array
    {
        if ($dir === null) {
            $dir = dirname(__DIR__, 1) . '/Views';
        }

        if ($level >= 4) {
            return [$dir];
        }

        $subDirs = array_filter(scandir($dir), function ($subDir) use ($dir) {
            return $subDir !== '.' && $subDir !== '..' && is_dir($dir . '/' . $subDir);
        });

        $paths = [$dir];

        foreach ($subDirs as $subDir) {
            $paths = array_merge($paths, self::getDirectoryViews($dir . '/' . $subDir, $level + 1));
        }

        return $paths;
    }

    public static function addGlobals(Environment $twig): void
    {
        if (!empty($_SESSION)) {
            $twig->addGlobal('session', $_SESSION);
        }


        // add app variables
        $twig->addGlobal('app', [
            'name' => $_ENV['APP_NAME'],
            'env' => $_ENV['APP_ENV'],
            'url' => $_ENV['BASE_URL'],
            'version' => $_ENV['APP_VERSION'],
            'debug' => $_ENV['APP_DEBUG'],
            'page_title' => Helper::getPageTitle()
        ]);


        // add breadcrumbs
        $twig->addGlobal('breadcrumbs', Helper::generateBreadcrumb());

        // if is localhost, add the global variable 'isLocalhost' to the twig template
        $twig->addGlobal('isLocalhost', $_ENV['APP_ENV'] === 'local');

        // if $_GET['s'] is set, add the global variable 'search' to the twig template
        if (isset($_GET['s'])) {
            $twig->addGlobal('search', $_GET['s']);
        } else {
            $twig->addGlobal('search', '');
        }

        // add the global variable 'path' to the twig template, but remove the query string
        $twig->addGlobal('path', explode('?', $_SERVER['REQUEST_URI'])[0]);
        // add query global variable, Traversability
        $twig->addGlobal('query', $_GET);

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
            'get_current_url' => function ($parms = '') {
                // current url without query string , and attach the query string if $parms is not empty
                //return explode('?', $_SERVER['REQUEST_URI'])[0];
                $url = explode('?', $_SERVER['REQUEST_URI'])[0];

                // if params is 'create' or 'delete', and url is / return BASE_URL
                if (in_array($parms, ['create', 'delete']) && $url === '/') {
                    return $_ENV['BASE_URL'] . '/' . $parms;
                }

                if ($parms) {
                    $url .= '/' . $parms;
                }




                return $url;

            },
            'url' => function (string $routeName = '', array $params = []) {
                // return public url + params
                $routeName = str_starts_with($routeName, '/') ? $routeName : "/$routeName";
                $query = http_build_query($params);
                $query = $query ? "?$query" : '';
                return $_ENV['BASE_URL'] . $routeName . $query;
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
                return $tfa->getQRCodeImageAsDataUri('Demo site: ' . $username, $secret);
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
            'getLabelClass' => function () {
                return 'block mb-2 text-sm font-medium text-gray-900 dark:text-white';
            },
            'getInputClass' => function () {
                return 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500';
            },
            'getSubmitClass' => function () {
                return 'inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-primary-700 rounded-lg focus:ring-4 focus:ring-primary-200 dark:focus:ring-primary-900 hover:bg-primary-80';
            },
            'getCheckboxClass' => function () {
                return 'w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600 dark:ring-offset-gray-800';
            },
        ];

        foreach ($twigFunctions as $name => $function) {
            $twig->addFunction(new TwigFunction($name, $function));
        }
    }
}