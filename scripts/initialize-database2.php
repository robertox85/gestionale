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

    // DROP TABLES

    // CREATE TABLES



    $pdo->exec("
    CREATE TABLE Utenti (
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cognome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('admin', 'dipendente', 'guest') NOT NULL DEFAULT 'guest',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Crea Menu Ristorante per Utente --
CREATE TABLE MenuRistorante (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    nome_menu VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_utente_menu` FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente)
);

-- Crea Piatto per Menu Ristorante --
CREATE TABLE Piatto (
    id_piatto INT AUTO_INCREMENT PRIMARY KEY,
    id_menu INT NOT NULL,
    nome_piatto VARCHAR(255) NOT NULL,
    prezzo DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_menu_piatto` FOREIGN KEY (id_menu) REFERENCES MenuRistorante(id_menu)
);


");






    echo "Database inizializzato correttamente!";
} catch (PDOException $e) {
    echo "Errore durante l'inizializzazione del database: " . $e->getMessage();
    exit();
}



