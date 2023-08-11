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

CREATE TABLE Sale (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nome_sala VARCHAR(255) NOT NULL,
    capacita INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE DisponibilitaSale (
    id_disponibilita INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    orario_apertura TIME NOT NULL,
    orario_chiusura TIME NOT NULL,
    durata_slot_minuti ENUM('15', '30', '45', '60', '90', '120') NOT NULL DEFAULT '60',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `chk_orario` CHECK ( orario_apertura < orario_chiusura ),
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);

CREATE TABLE `GiorniSettimana` (
  `id_giorno` int NOT NULL AUTO_INCREMENT,
  `nome_giorno` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_giorno`)
);

-- Popolo la tabella GiorniSettimana --
INSERT INTO `GiorniSettimana` (`id_giorno`, `nome_giorno`) VALUES
(1, 'Lunedì'),
(2, 'Martedì'),
(3, 'Mercoledì'),
(4, 'Giovedì'),
(5, 'Venerdì'),
(6, 'Sabato'),
(7, 'Domenica');

CREATE TABLE `DisponibilitaGiorni` (
  `id_disponibilita` int NOT NULL,
  `id_giorno` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_disponibilita`,`id_giorno`),
  KEY `id_giorno` (`id_giorno`),
  CONSTRAINT `disponibilitagiorni_ibfk_1` FOREIGN KEY (`id_disponibilita`) REFERENCES `DisponibilitaSale` (`id_disponibilita`),
  CONSTRAINT `disponibilitagiorni_ibfk_2` FOREIGN KEY (`id_giorno`) REFERENCES `GiorniSettimana` (`id_giorno`)
);

CREATE TABLE Prenotazioni (
    id_prenotazione INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_sala INT NOT NULL,
    giorno DATE NOT NULL,
    data_ora_inizio TIME NOT NULL,
    data_ora_fine TIME NOT NULL,
    ricorrenza ENUM('settimanale', 'mensile', 'nessuna') NOT NULL DEFAULT 'nessuna',
    fine_ricorrenza DATE NULL DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `chk_data_ora` CHECK ( data_ora_inizio < data_ora_fine ),
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente),
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);

CREATE TABLE EccezioniSale (
    id_eccezione INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    motivo VARCHAR(255) NOT NULL DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `chk_data_eccezione` CHECK ( data_inizio <= data_fine ),
    CONSTRAINT `chk_ora_eccezione` CHECK ( ora_inizio <= ora_fine ),
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);

CREATE TABLE Notifiche (
    id_notifica INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    messaggio VARCHAR(255) NOT NULL,
    data_invio DATETIME NOT NULL,
    stato ENUM('letto', 'non letto') NOT NULL DEFAULT 'non letto',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente)
);

CREATE TABLE LogUtente (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    azione VARCHAR(255) NOT NULL,
    data_azione DATETIME NOT NULL,
    dettagli VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente)
);

CREATE TABLE Risorse (
    id_risorsa INT AUTO_INCREMENT PRIMARY KEY,
    nome_risorsa VARCHAR(255) NOT NULL,
    descrizione TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE RelazioniSaleRisorse (
    id_relazione INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    id_risorsa INT NOT NULL,
    quantita INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala),
    FOREIGN KEY (id_risorsa) REFERENCES Risorse(id_risorsa)
);

CREATE TABLE Recensioni (
    id_recensione INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_sala INT NOT NULL,
    valutazione ENUM('1', '2', '3', '4', '5') NOT NULL DEFAULT '1',
    commento TEXT NOT NULL,
    pubblicata BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente),
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);

CREATE TABLE PreferenzeUtenteSale (
    id_preferenza INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_sala INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (id_utente, id_sala),
    FOREIGN KEY (id_utente) REFERENCES Utenti(id_utente),
    FOREIGN KEY (id_sala) REFERENCES Sale(id_sala)
);

create table RememberMe
(
    id_remember INT AUTO_INCREMENT PRIMARY KEY,
    id_utente     int                        not null,
    token         varchar(255)               not null,
    expires_at    datetime                   not null,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    foreign key (id_utente) references Utenti (id_utente)
);


");






    echo "Database inizializzato correttamente!";
} catch (PDOException $e) {
    echo "Errore durante l'inizializzazione del database: " . $e->getMessage();
    exit();
}



