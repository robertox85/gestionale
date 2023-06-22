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

    //TODO: Devo rinominare gli id delle tabelle da id_tabella a id perchè altrimenti non funziona il load dei dati

    $pdo->exec("
        -- Creazione della tabella Ruoli
        CREATE TABLE IF NOT EXISTS Ruoli (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          nome_ruolo VARCHAR(50)
        );
        
        -- Creazione della tabella Utenti
        CREATE TABLE IF NOT EXISTS Utenti (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          nome VARCHAR(50),
          cognome VARCHAR(50),
          email VARCHAR(100),
          password VARCHAR(100),
          id_ruolo INT UNSIGNED,
          FOREIGN KEY (id_ruolo) REFERENCES Ruoli(id)
        );
        
        -- Creazione della tabella Gruppi
        CREATE TABLE IF NOT EXISTS Gruppi (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          nome_gruppo VARCHAR(50)
        );
        
        -- Creazione della tabella Sottogruppi
        CREATE TABLE IF NOT EXISTS Sottogruppi (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          nome_sottogruppo VARCHAR(50),
          id_gruppo INT,
          FOREIGN KEY (id_gruppo) REFERENCES Gruppi(id) ON DELETE CASCADE
        );
       
        
        -- Creazione della tabella Controparti
        CREATE TABLE IF NOT EXISTS Controparti (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          nome VARCHAR(50),
          cognome VARCHAR(50)
        );
        
        -- Creazione della tabella Pratiche
        CREATE TABLE IF NOT EXISTS Pratiche (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          nr_pratica VARCHAR(50),
          nome VARCHAR(50),
          tipologia VARCHAR(50),
          stato VARCHAR(50),
          avvocato VARCHAR(50),
          referente VARCHAR(50),
          competenza VARCHAR(50),
          ruolo_generale VARCHAR(50),
          giudice VARCHAR(50),
          id_sottogruppo INT,
          FOREIGN KEY (id_sottogruppo) REFERENCES Sottogruppi(id)
        );
        
        -- Creazione della tabella Scadenze
        CREATE TABLE IF NOT EXISTS Scadenze (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          data DATE,
          motivo VARCHAR(100),
          id_pratica INT,
          FOREIGN KEY (id_pratica) REFERENCES Pratiche(id)
        );
        
        -- Creazione della tabella Udienze
        CREATE TABLE IF NOT EXISTS Udienze (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          tipo VARCHAR(50),
          data DATE,
          id_pratica INT,
          FOREIGN KEY (id_pratica) REFERENCES Pratiche(id)
        );
        
        -- Creazione della tabella Note
        CREATE TABLE IF NOT EXISTS Note (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          tipologia VARCHAR(50),
          testo VARCHAR(500),
          visibilita VARCHAR(50),
          id_pratica INT,
          FOREIGN KEY (id_pratica) REFERENCES Pratiche(id)
        );
        
        -- Creazione della tabella Permessi
        CREATE TABLE IF NOT EXISTS Permessi (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
            `nome_permesso` VARCHAR(50) NOT NULL,
            `descrizione_permesso` VARCHAR(255)
        );

       
        CREATE TABLE IF NOT EXISTS Ruoli_Permessi (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
          ruolo_id INT UNSIGNED NOT NULL,
          permesso_id INT UNSIGNED NOT NULL,
          FOREIGN KEY (ruolo_id) REFERENCES Ruoli(id),
          FOREIGN KEY (permesso_id) REFERENCES Permessi(id)
        );
        
        CREATE TABLE IF NOT EXISTS Utenti_Sottogruppi (
          id_utente INT,
          id_sottogruppo INT,
          FOREIGN KEY (id_utente) REFERENCES Utenti(id),
          FOREIGN KEY (id_sottogruppo) REFERENCES Sottogruppi(id),
          PRIMARY KEY (id_utente, id_sottogruppo)
        );
            ");


    $pdo->exec("
    INSERT IGNORE INTO Ruoli (id, nome_ruolo) VALUES
  (1, 'Amministratore'),
  (2, 'Dominus'),
  (3, 'Referente'),
  (4, 'Segreteria'),
  (5, 'Cliente');

-- Inserimento di dati nella tabella Utente
INSERT IGNORE INTO Utenti (id, nome, cognome, email, password, id_ruolo) VALUES
  (1, 'Mario', 'Rossi', 'mario@example.com', 'password123', 1), /* Amministratore  */ 
  (2, 'Luca', 'Bianchi', 'luca@example.com', 'password456', 3), /* Referente  */ 
  (3, 'Laura', 'Verdi', 'laura@example.com', 'password789', 5); /* Cliente */ 

-- Inserimento di dati nella tabella Permessi
INSERT IGNORE INTO Permessi (id, nome_permesso, descrizione_permesso) VALUES
  (1, 'visualizza_pratiche', 'Visualizza le pratiche'),
  (2, 'modifica_pratiche', 'Modifica le pratiche'),
  (3, 'elimina_pratiche', 'Elimina le pratiche'),
  (4, 'crea_pratica', 'Crea nuova pratica'),
    (5, 'visualizza_scadenze', 'Visualizza le scadenze'),
    (6, 'modifica_scadenze', 'Modifica le scadenze'),
    (7, 'elimina_scadenze', 'Elimina le scadenze'),
    (8, 'crea_scadenza', 'Crea nuova scadenza'),
    (9, 'visualizza_udienze', 'Visualizza le udienze'),
    (10, 'modifica_udienze', 'Modifica le udienze'),
    (11, 'elimina_udienze', 'Elimina le udienze'),
    (12, 'crea_udienza', 'Crea nuova udienza'),
    (13, 'visualizza_note', 'Visualizza le note'),
    (14, 'modifica_note', 'Modifica le note'),
    (15, 'elimina_note', 'Elimina le note'),
    (16, 'crea_nota', 'Crea nuova nota'),
    (17, 'visualizza_controparti', 'Visualizza le controparti'),
    (18, 'modifica_controparti', 'Modifica le controparti'),
    (19, 'elimina_controparti', 'Elimina le controparti'),
    (20, 'crea_controparte', 'Crea nuova controparte'),
    (21, 'visualizza_gruppi', 'Visualizza i gruppi'),
    (22, 'modifica_gruppi', 'Modifica i gruppi'),
    (23, 'elimina_gruppi', 'Elimina i gruppi'),
    (24, 'crea_gruppo', 'Crea nuovo gruppo'),
    (25, 'visualizza_sottogruppi', 'Visualizza i sottogruppi'),
    (26, 'modifica_sottogruppi', 'Modifica i sottogruppi'),
    (27, 'elimina_sottogruppi', 'Elimina i sottogruppi'),
    (28, 'crea_sottogruppo', 'Crea nuovo sottogruppo'),
    (29, 'visualizza_utenti', 'Visualizza gli utenti'),
    (30, 'modifica_utenti', 'Modifica gli utenti'),
    (31, 'elimina_utenti', 'Elimina gli utenti'),
    (32, 'crea_utente', 'Crea nuovo utente'),
    (33, 'visualizza_ruoli', 'Visualizza i ruoli'),
    (34, 'modifica_ruoli', 'Modifica i ruoli'),
    (35, 'elimina_ruoli', 'Elimina i ruoli'),
    (36, 'crea_ruolo', 'Crea nuovo ruolo'),
    (37, 'visualizza_permessi', 'Visualizza i permessi'),
    (38, 'modifica_permessi', 'Modifica i permessi'),
    (39, 'elimina_permessi', 'Elimina i permessi'),
    (40, 'crea_permesso', 'Crea nuovo permesso');

-- Inserimento di dati nella tabella Ruoli_Permessi (Amministratore) e Descrizione permessi
INSERT IGNORE INTO Ruoli_Permessi (id, ruolo_id, permesso_id) VALUES
  (1, 1, 1), /* Amministratore - visualizza_pratiche  */ 
  (2, 1, 2), /* Amministratore - modifica_pratiche  */ 
  (3, 1, 3), /* Amministratore - elimina_pratiche  */ 
  (4, 1, 4), /* Amministratore - crea_pratica  */ 
  (5, 1, 5), /* Amministratore - visualizza_scadenze  */ 
  (6, 1, 6), /* Amministratore - modifica_scadenze  */ 
  (7, 1, 7), /* Amministratore - elimina_scadenze  */ 
  (8, 1, 8), /* Amministratore - crea_scadenza  */ 
  (9, 1, 9), /* Amministratore - visualizza_udienze  */ 
  (10, 1, 10), /* Amministratore - modifica_udienze  */ 
  (11, 1, 11), /* Amministratore - elimina_udienze  */ 
  (12, 1, 12), /* Amministratore - crea_udienza  */ 
  (13, 1, 13), /* Amministratore - visualizza_note  */ 
  (14, 1, 14), /* Amministratore - modifica_note  */ 
  (15, 1, 15), /* Amministratore - elimina_note  */ 
  (16, 1, 16), /* Amministratore - crea_nota  */ 
  (17, 1, 17), /* Amministratore - visualizza_controparti  */ 
  (18, 1, 18), /* Amministratore - modifica_controparti  */ 
  (19, 1, 19), /* Amministratore - elimina_controparti  */ 
  (20, 1, 20), /* Amministratore - crea_controparte  */ 
  (21, 1, 21), /* Amministratore - visualizza_gruppi  */ 
  (22, 1, 22), /* Amministratore - modifica_gruppi  */ 
  (23, 1, 23), /* Amministratore - elimina_gruppi  */ 
  (24, 1, 24), /* Amministratore - crea_gruppo  */ 
  (25, 1, 25), /* Amministratore - visualizza_sottogruppi  */ 
  (26, 1, 26), /* Amministratore - modifica_sottogruppi  */ 
  (27, 1, 27), /* Amministratore - elimina_sottogruppi  */ 
  (28, 1, 28), /* Amministratore - crea_sottogruppo  */ 
  (29, 1, 29), /* Amministratore - visualizza_utenti  */ 
  (30, 1, 30), /* Amministratore - modifica_utenti  */ 
  (31, 1, 31), /* Amministratore - elimina_utenti  */ 
  (32, 1, 32), /* Amministratore - crea_utente  */ 
  (33, 1, 33), /* Amministratore - visualizza_ruoli  */ 
  (34, 1, 34), /* Amministratore - modifica_ruoli  */ 
  (35, 1, 35), /* Amministratore - elimina_ruoli  */ 
  (36, 1, 36), /* Amministratore - crea_ruolo  */ 
  (37, 1, 37), /* Amministratore - visualizza_permessi  */ 
  (38, 1, 38), /* Amministratore - modifica_permessi  */ 
  (39, 1, 39), /* Amministratore - elimina_permessi  */ 
  (40, 1, 40); /* Amministratore - crea_permesso  */

-- Inserimento di dati nella tabella Ruoli_Permessi (Avvocato) e Descrizione permessi
INSERT IGNORE  INTO Ruoli_Permessi (id, ruolo_id, permesso_id) VALUES
    (41, 2, 1), /* Avvocato - visualizza_pratiche  */ 
    (42, 2, 2), /* Avvocato - modifica_pratiche  */ 
    (43, 2, 3), /* Avvocato - elimina_pratiche  */ 
    (44, 2, 4); /* Avvocato - crea_pratica  */ 
");


    $pdo->exec("

-- Inserimento di un gruppo
INSERT IGNORE INTO Gruppi (id, nome_gruppo) VALUES (1, 'Gruppo A');

-- Inserimento di un sottogruppo legato al gruppo precedente
INSERT IGNORE INTO Sottogruppi (id, nome_sottogruppo, id_gruppo) VALUES (1, 'Sottogruppo 1', 1);

-- Inserimento di una controparte
INSERT IGNORE INTO Controparti (id, nome, cognome) VALUES (1, 'Controparte 1', 'Rossi');

-- Inserimento di una pratica legata al sottogruppo e alla controparte
INSERT IGNORE INTO Pratiche (id, nr_pratica, nome, tipologia, stato, avvocato, referente, competenza, ruolo_generale, giudice, id_sottogruppo) VALUES (1, 'P001', 'Pratica 1', 'Civile', 'Aperta', 'Avvocato 1', 'Referente 1', 'Competenza 1', 'Ruolo Generale 1', 'Giudice 1', 1);

-- Inserimento di una scadenza legata alla pratica
INSERT IGNORE INTO Scadenze (id, data, motivo, id_pratica) VALUES (1, '2023-06-30', 'Scadenza 1', 1);

-- Inserimento di un'udienza legata alla pratica
INSERT IGNORE INTO Udienze (id, tipo, data, id_pratica) VALUES (1, 'Udienza 1', '2023-07-15', 1);

-- Inserimento di una nota legata alla pratica
INSERT IGNORE INTO Note (id, tipologia, testo, visibilita, id_pratica) VALUES (1, 'Nota 1', 'Testo nota 1', 'Privata', 1);

");

    echo "Database inizializzato correttamente!";
} catch (PDOException $e) {
    echo "Errore durante l'inizializzazione del database: " . $e->getMessage();
    exit();
}



