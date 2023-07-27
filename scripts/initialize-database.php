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
    $pdo->exec("DROP TABLE IF EXISTS DisponibilitaSale;");
    $pdo->exec("DROP TABLE IF EXISTS Prenotazioni;");
    $pdo->exec("DROP TABLE IF EXISTS EccezioniSale;");
    $pdo->exec("DROP TABLE IF EXISTS SaleRisorse;");
    $pdo->exec("DROP TABLE IF EXISTS Recensioni;");
    $pdo->exec("DROP TABLE IF EXISTS SalePreferite;");
    $pdo->exec("DROP TABLE IF EXISTS Sale;");
    $pdo->exec("DROP TABLE IF EXISTS Notifiche;");
    $pdo->exec("DROP TABLE IF EXISTS LogOperazioni;");
    $pdo->exec("DROP TABLE IF EXISTS Utenti;");
    $pdo->exec("DROP TABLE IF EXISTS Risorse;");
    // CREATE TABLES



    $pdo->exec("
    CREATE TABLE Utenti (
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    cognome VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    ruolo ENUM('admin', 'dipendente', 'guest'),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE Sale (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nome_sala VARCHAR(255),
    capacita INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE DisponibilitàSale (
    id_disponibilità INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT,
    giorno_settimana ENUM('lun', 'mar', 'mer', 'gio', 'ven', 'sab', 'dom'),
    orario_apertura TIME,
    orario_chiusura TIME,
    durata_slot INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);

CREATE TABLE Prenotazioni (
    id_prenotazione INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT,
    id_sala INT,
    data_ora_inizio DATETIME,
    data_ora_fine DATETIME,
    ricorrenza ENUM('settimanale', 'mensile', 'nessuna'),
    fine_ricorrenza DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente),
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);

CREATE TABLE EccezioniSale (
    id_eccezione INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT,
    data_inizio DATE,
    data_fine DATE,
    motivo VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);

CREATE TABLE Notifiche (
    id_notifica INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT,
    messaggio VARCHAR(255),
    data_invio DATETIME,
    stato ENUM('letto', 'non letto'),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente)
);

CREATE TABLE LogOperazioni (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT,
    azione VARCHAR(255),
    data_azione DATETIME,
    dettagli VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente)
);

CREATE TABLE Risorse (
    id_risorsa INT AUTO_INCREMENT PRIMARY KEY,
    nome_risorsa VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE SaleRisorse (
    id_sala INT,
    id_risorsa INT,
    quantità INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_sala, id_risorsa),
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala),
    FOREIGN KEY (id_risorsa) REFERENCES Risorse(id_risorsa)
);

CREATE TABLE Recensioni (
    id_recensione INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT,
    id_sala INT,
    valutazione INT,
    commento TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente),
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);

CREATE TABLE SalePreferite (
    id_utente INT,
    id_sala INT,
    data_aggiunta DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utente, id_sala),
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente),
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);
");






    echo "Database inizializzato correttamente!";
} catch (PDOException $e) {
    echo "Errore durante l'inizializzazione del database: " . $e->getMessage();
    exit();
}



