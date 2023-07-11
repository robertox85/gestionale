<?php

require_once 'vendor/autoload.php';


use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Finder\Finder;

$viewsPath = __DIR__ . '/app/views';
$translationsPath = __DIR__ . '/translations';

// get all files in the translations directory
$translationDirectory = new RecursiveDirectoryIterator($translationsPath);
$translationIterator = new RecursiveIteratorIterator($translationDirectory);

$directory = new RecursiveDirectoryIterator($viewsPath);
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.twig$/i', RecursiveRegexIterator::GET_MATCH);

$phrases = [];

foreach ($regex as $file) {
    $contents = file_get_contents($file[0]);
    //$regex = '/{{\s*\'(.*?)\'\s*\|trans }}/s';
    $regex = '/{{\s*\'(.*?)\'\s*\|trans\s*(\(.*?\)\s*\|\s*raw)? }}/s';

    if (preg_match_all($regex, $contents, $matches)) {
        foreach ($matches[1] as $phrase) {
            $cleanedPhrase = preg_replace('/\s+/', ' ', $phrase);
            $phrases[trim($cleanedPhrase)] = '';  // usa trim per rimuovere spazi bianchi all'inizio e alla fine
        }
    }
}

// Read existing translations and merge them with the new ones
try {
    foreach ($translationIterator as $file) {
        if ($file->isDir()) {
            continue;
        }

        $existingTranslations = Yaml::parseFile($file->getPathname());

        // if is en file, skip
        if (strpos($file->getPathname(), 'en') !== false) {
            continue;
        }

        foreach ($phrases as $key => $value) {
            if (!array_key_exists($key, $existingTranslations)) {
                $existingTranslations[$key] = $value;
            }
        }
        $yaml = '';
        foreach ($existingTranslations as $key => $value) {
            // Dump strings manually with double quotes
            $yaml .= '"' . str_replace('"', '\\"', $key) . '": "' . str_replace('"', '\\"', $value) . '"' . PHP_EOL;
        }
        file_put_contents($file->getPathname(), $yaml);
    }



} catch (ParseException $exception) {
    printf('Unable to parse the YAML string: %s', $exception->getMessage());
}

