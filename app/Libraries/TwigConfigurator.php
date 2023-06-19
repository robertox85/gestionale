<?php

namespace App\Libraries;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class TwigConfigurator
{
    public static function configure(): Environment
    {
        $directoryViews = self::getDirectoryViews();

        $loader = new FilesystemLoader($directoryViews);

        $twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
        ]);

        self::initTranslation($twig);

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
}