<?php

namespace App\Libraries;

use App\Controllers\Web\BaseController;
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

    private function parseErrorMessage($errorMessage)
    {
        $details = [];

        // Estrai il nome del database e della tabella
        if (preg_match('/`(.+?)`\.`(.+?)`/', $errorMessage, $matches)) {
            $details['database'] = $matches[1];
            $details['table'] = $matches[2];
        }

        // Estrai il nome del vincolo
        if (preg_match('/CONSTRAINT `(.+?)`/', $errorMessage, $matches)) {
            $details['constraint'] = $matches[1];
        }

        // Estrai i dettagli della chiave esterna
        if (preg_match('/FOREIGN KEY \(`(.+?)`\) REFERENCES `(.+?)` \(`(.+?)`\)/', $errorMessage, $matches)) {
            $details['foreign_key'] = $matches[1];
            $details['referenced_table'] = $matches[2];
            $details['referenced_column'] = $matches[3];
        }

        return $details;
    }

    private function parseGeneralErrorMessage($errorMessage)
    {
        $details = [];

        // Estrai il nome del vincolo di controllo
        if (preg_match('/Check constraint \'(.+?)\'/', $errorMessage, $matches)) {
            $details['check_constraint'] = $matches[1];
        }

        return $details;
    }

    private function handlePDOException($e): void
    {
        $errorMessage = $e->getMessage();
        if (strpos($errorMessage, 'chk_durata_slot') !== false) {
            $details = $this->parseErrorMessage($errorMessage);
            $userMessage = "La durata dello slot inserita non è valida. Assicurati che sia compresa tra l'orario di apertura e di chiusura.";
        } elseif (strpos($errorMessage, 'General error: 3819 Check constraint') !== false) {
            $details = $this->parseGeneralErrorMessage($errorMessage);
            $userMessage = "Errore: il vincolo di controllo '{$details['check_constraint']}' è stato violato. Assicurati di inserire dati validi.";
        } elseif (strpos($errorMessage, 'SQLSTATE[23000]: Integrity constraint violation: 1451') !== false) {
            $userMessage = "L'operazione non può essere completata a causa di alcune dipendenze esistenti. Assicurati di non eliminare o modificare elementi collegati ad altri dati.";
        } elseif (strpos($errorMessage, 'SQLSTATE[23000]: Integrity constraint violation: 1452') !== false) {
            $userMessage = "L'elemento a cui stai cercando di fare riferimento non esiste. Assicurati di inserire o aggiornare con un valore valido.";
        } elseif (strpos($errorMessage, 'SQLSTATE[23000]: Integrity constraint violation: 1062') !== false) {
            $userMessage = "L'operazione non può essere completata a causa di un elemento duplicato. Assicurati di non inserire elementi duplicati.";
        } elseif (strpos($errorMessage, 'SQLSTATE[23000]: Integrity constraint violation: 1048') !== false) {
            $userMessage = "L'operazione non può essere completata a causa di un elemento duplicato. Assicurati di non inserire elementi duplicati.";
        } elseif (strpos($errorMessage, 'SQLSTATE[01000]: Warning: 1265') !== false) {
            $userMessage = "Attenzione: alcuni dati inseriti sono stati troncati perché erano troppo lunghi o non compatibili con il formato previsto.";
        } else {
            $userMessage = "Si è verificato un errore. Riprova più tardi.";
        }

        $this->logError('Errore PDO: ' . $this->formatExceptionMessage($e));

        Helper::addError($userMessage);
        Helper::redirect($this->url('/'));
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
        $errorMessage = $e->getMessage();
        if (strpos($errorMessage, 'Required: ') !== false) {
            $userMessage = str_replace('Required: ', '', $errorMessage);
            $errors = explode('<br>', $userMessage);
            foreach ($errors as $error) {
                Helper::addError($error);
            }
        } else {
            $userMessage = "Si è verificato un errore. Riprova più tardi.";
            Helper::addError($userMessage);
        }
        // Qui gestiamo tutte le altre eccezioni
        $this->logError($this->formatExceptionMessage($e));
        Helper::redirect($this->url(''));

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
