<?php

namespace App\Libraries;

use App\Controllers\BaseController;
use Exception;
use PDOException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ErrorHandler extends BaseController
{

    private static ?ErrorHandler $instance = null;

    private function __construct()
    {
        // Costruttore privato per prevenire la creazione diretta di oggetti
    }

    public static function getInstance(): ErrorHandler
    {
        if (self::$instance === null) {
            self::$instance = new ErrorHandler();
        }
        return self::$instance;
    }

    public static function handle($e)
    {
        $instance = self::getInstance();
        $instance->handleException($e);
    }

    public function handleException($e): void
    {
        if ($e instanceof PDOException) {
            $this->handlePDOException($e);
        } else if ($e instanceof LoaderError || $e instanceof RuntimeError || $e instanceof SyntaxError) {
            $this->handleTwigError($e);
        } else {
            $this->handleGenericException($e);
        }
    }

    private function handlePDOException($e): void
    {
        // Qui gestiamo le eccezioni PDO
        $this->logError('Errore PDO: ' . $this->formatExceptionMessage($e));
        // TODO: aggiungere un messaggio di errore generico
        Helper::addError($this->formatExceptionMessage($e));

        Helper::redirect('/500');
    }

    private function handleTwigError($e): void
    {
        // Qui gestiamo le eccezioni di Twig
        $this->logError('Errore Twig: ' . $this->formatExceptionMessage($e));
        // TODO: aggiungere un messaggio di errore generico
        Helper::addError($this->formatExceptionMessage($e));

        Helper::redirect('/500');
    }

    private function handleGenericException($e): void
    {
        // Qui gestiamo tutte le altre eccezioni
        $this->logError($this->formatExceptionMessage($e));
        // TODO: aggiungere un messaggio di errore generico
        Helper::addError($this->formatExceptionMessage($e));

        Helper::redirect('/500');

    }


    private function formatExceptionMessage($e): string
    {
        // Qui formattiamo il messaggio dell'eccezione
        return sprintf(
            "Eccezione: %s. File: %s. Line: %d.",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
    }

    public function logError($errorMessage): void
    {
        // Aggiungi un try-catch qui per prevenire eventuali errori durante la registrazione degli errori.
        try {
            $dir = dirname(__DIR__, 2);
            $logFile = $dir . '/logs/error.log';
            $errorMessage = '[' . date('Y-m-d H:i:s') . '] ' . $errorMessage . "\n";
            error_log($errorMessage, 3, $logFile);
        } catch (Exception $e) {
            // Se c'è un errore durante la registrazione degli errori, semplicemente fallisci in silenzio.
            // Non dovresti cercare di gestire o registrare l'errore qui, poiché potrebbe causare un loop infinito.
        }
    }

    public function handleNotFound()
    {
        // Qui gestiamo le pagine non trovate
        $this->logError('Pagina non trovata: ' . $_SERVER['REQUEST_URI']);
        Helper::addError('Pagina non trovata.');
        Helper::redirect('/404');
    }

    public function handleNotAllowed(mixed $allowedMethods)
    {
        // Qui gestiamo le pagine non consentite
        $this->logError('Metodo non consentito: ' . $_SERVER['REQUEST_URI']);
        Helper::addError('Metodo non consentito.');
        Helper::redirect('/405');
    }
}
