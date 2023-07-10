<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Inizializza l'ambiente di configurazione
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Connessione al database
$host = $_ENV['DB_HOST'];
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Errore di connessione al database: " . $e->getMessage();
    exit();
}


try {


    // Modifica colonna stato in pratiche, deve essere un enum con valori: 'aperta', 'chiusa', 'sospesa'
    $pdo->exec("ALTER TABLE Pratiche MODIFY COLUMN stato ENUM('aperta', 'chiusa', 'sospesa') NOT NULL DEFAULT 'aperta'");

    // Aggiungi colonna 'Denominazione' in Anagrafica, deve essere un varchar(255) nullable
    $pdo->exec("ALTER TABLE Anagrafiche ADD COLUMN denominazione VARCHAR(255) NULL DEFAULT NULL AFTER cognome");

    // Aggiungi colonna tipo_entita in Anagrafica, dopo id_utente, deve essere un enum con valori: 'Persona', 'Azienda'
    $pdo->exec("ALTER TABLE Anagrafiche ADD COLUMN tipo_utente ENUM('Persona', 'Azienda') NOT NULL DEFAULT 'Persona' AFTER note");


    // Aggiungi colonna tipo_utente in Anagrafiche, deve essere un enum con valori: 'Controparte', 'Cliente', 'Referente'. Default: 'Cliente'
    $pdo->exec("ALTER TABLE Anagrafiche ADD COLUMN tipo_anagrafica ENUM('Controparte', 'Cliente', 'Referente') NOT NULL DEFAULT 'Cliente' AFTER tipo_utente");



    echo "Database inizializzato correttamente!";
} catch (PDOException $e) {
    echo "Errore durante l'inizializzazione del database: " . $e->getMessage();
    exit();
}



