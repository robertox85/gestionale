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

    // DROP TABLES
    $pdo->exec("DROP TABLE IF EXISTS Anagrafiche");
    $pdo->exec("DROP TABLE IF EXISTS Assistiti");
    $pdo->exec("DROP TABLE IF EXISTS Controparti");
    $pdo->exec("DROP TABLE IF EXISTS Documenti");
    $pdo->exec("DROP TABLE IF EXISTS Note");
    $pdo->exec("DROP TABLE IF EXISTS Ruoli_Permessi");
    $pdo->exec("DROP TABLE IF EXISTS Permessi");
    $pdo->exec("DROP TABLE IF EXISTS Scadenze");
    $pdo->exec("DROP TABLE IF EXISTS Udienze");
    $pdo->exec("DROP TABLE IF EXISTS Pratiche");
    $pdo->exec("DROP TABLE IF EXISTS Utenti_Gruppi");
    $pdo->exec("DROP TABLE IF EXISTS Gruppi");
    $pdo->exec("DROP TABLE IF EXISTS Utenti");
    $pdo->exec("DROP TABLE IF EXISTS Ruoli");


    $pdo->exec("
    
        -- Creazione della tabella Ruoli
        CREATE TABLE IF NOT EXISTS Ruoli (
          id INT AUTO_INCREMENT PRIMARY KEY,
          nome VARCHAR(50),
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        
        -- Creazione della tabella Utenti
        CREATE TABLE IF NOT EXISTS Utenti (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          email VARCHAR(100),
          password VARCHAR(100),
          id_ruolo INT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (id_ruolo) REFERENCES Ruoli(id)
        );
        
        -- Creazione della tabella Anagrafiche
        CREATE TABLE IF NOT EXISTS Anagrafiche (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          nome VARCHAR(50),
          cognome VARCHAR(50),
          denominazione VARCHAR(255),
          indirizzo VARCHAR(100),
          cap VARCHAR(10),
          citta VARCHAR(50),
          provincia VARCHAR(50),
          telefono VARCHAR(50),
          cellulare VARCHAR(50),
          pec VARCHAR(100),
          codice_fiscale VARCHAR(50),
          partita_iva VARCHAR(50),
          note VARCHAR(500),
          tipo_utente ENUM('Persona', 'Azienda') NOT NULL DEFAULT 'Persona',
          
          id_utente INT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_utente) REFERENCES Utenti(id)
        );
        
        -- Creazione della tabella Gruppi
        CREATE TABLE IF NOT EXISTS Gruppi (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          nome VARCHAR(50),
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        
        
        -- Creazione della tabella Pratiche
        CREATE TABLE IF NOT EXISTS Pratiche (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          nr_pratica VARCHAR(50),
          nome VARCHAR(50),
          tipologia VARCHAR(50),
          stato ENUM('aperta', 'chiusa', 'sospesa') NOT NULL DEFAULT 'aperta',
          avvocato VARCHAR(50),
          referente VARCHAR(50),
          competenza VARCHAR(50),
          ruolo_generale VARCHAR(50),
          giudice VARCHAR(50),
          id_gruppo INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (id_gruppo) REFERENCES Gruppi(id)
        );
        
        
        -- Creazione della tabella Controparti
        CREATE TABLE IF NOT EXISTS Controparti (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          id_pratica INT,
          id_utente INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_pratica) REFERENCES Pratiche(id),
            FOREIGN KEY (id_utente) REFERENCES Utenti(id)
            );
        
        
        -- Creazione della tabella Assistiti
        CREATE TABLE IF NOT EXISTS Assistiti (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          id_pratica INT,
          id_utente INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_pratica) REFERENCES Pratiche(id),
            FOREIGN KEY (id_utente) REFERENCES Utenti(id)
            );
        
        
        -- Creazione della tabella Documenti
        CREATE TABLE IF NOT EXISTS Documenti (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          nome VARCHAR(50),
          descrizione VARCHAR(100),
          path VARCHAR(100),
          id_pratica INT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_pratica) REFERENCES Pratiche(id)
            );
        
        
        -- Creazione della tabella Scadenze
        CREATE TABLE IF NOT EXISTS Scadenze (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          data DATE,
          motivo VARCHAR(255),
          id_pratica INT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (id_pratica) REFERENCES Pratiche(id)
        );
        
        -- Creazione della tabella Udienze
        CREATE TABLE IF NOT EXISTS Udienze (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          descrizione VARCHAR(255),
          data DATE,
          id_pratica INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (id_pratica) REFERENCES Pratiche(id)
        );
        
        -- Creazione della tabella Note
        CREATE TABLE IF NOT EXISTS Note (
          id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
          tipologia VARCHAR(255),
          descrizione VARCHAR(255),
          visibilita VARCHAR(50),
          id_pratica INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (id_pratica) REFERENCES Pratiche(id)
        );
        
        -- Creazione della tabella Permessi
        CREATE TABLE IF NOT EXISTS Permessi (
            `id` INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL,
            `nome` VARCHAR(50) NOT NULL,
            `descrizione` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

       
        CREATE TABLE IF NOT EXISTS Ruoli_Permessi (
          id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
          ruolo_id INT NOT NULL,
          permesso_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (ruolo_id) REFERENCES Ruoli(id),
          FOREIGN KEY (permesso_id) REFERENCES Permessi(id)
        );
        
        
        
      CREATE TABLE IF NOT EXISTS Utenti_Gruppi (
          id_utente INT,
          id_gruppo INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (id_utente) REFERENCES Utenti(id),
          FOREIGN KEY (id_gruppo) REFERENCES Gruppi(id),
          PRIMARY KEY (id_utente, id_gruppo)
        );
      
            ");


    $pdo->exec("
    INSERT IGNORE INTO Ruoli (id, nome) VALUES
  (1, 'Amministratore'),
  (2, 'Dominus'),
  (3, 'Referente'),
  (4, 'Segreteria'),
  (5, 'Cliente'),
  (6, 'Controparte');

-- Inserimento di dati nella tabella Utente , crea 20 utenti
INSERT IGNORE INTO Utenti (id, email, password, id_ruolo) VALUES
  (1, 'mario@example.com', 'password123', 1), /* Amministratore  */ 
  (2, 'luca@example.com', 'password456', 3), /* Referente  */ 
  (3, 'laura@example.com', 'password789', 5), /* Cliente  */
  (4, 'matteo@example.com', 'password789', 6), /* Controparte  */
  (5, 'anna@example.com', 'password456', 2),  /* Dominus  */
  (6, 'franco@example.com', 'password789', 4), /* Segreteria  */
  (7, 'chiara@example.com', 'password321', 1),
  (8, 'davide@example.com', 'password654', 3),
  (9, 'elena@example.com', 'password987', 2),
  (10, 'giovanni@example.com', 'password741', 1),
  (11, 'simone@example.com', 'password852', 3),
  (12, 'francesca@example.com', 'password963', 4),
  (13, 'giacomo@example.com', 'password123', 1),
  (14, 'luigi@example.com', 'password456', 3),
  (15, 'sofia@example.com', 'password789', 2),
  (16, 'massimo@example.com', 'password321', 1),
  (17, 'giulia@example.com', 'password654', 4),
  (18, 'antonio@example.com', 'password987', 1),
  (19, 'silvia@example.com', 'password741', 3),
  (20, 'federico@example.com', 'password852', 2);

  


-- Inserimento di dati nella tabella Anagrafiche
INSERT IGNORE INTO Anagrafiche (id, nome, cognome, indirizzo, cap, citta, provincia, telefono, cellulare, pec, codice_fiscale, partita_iva, note, id_utente) VALUES
    (1, 'Mario', 'Rossi', 'Via Roma 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', 'mario.rossi@pec.it', '','','', 1),
    (2, 'Luca', 'Bianchi', 'Via Milano 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 2),
    (3, 'Laura', 'Verdi', 'Via Napoli 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 3),
    (4, 'Matteo', 'Gialli', 'Via Torino 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 4),
    (5, 'Anna', 'Neri', 'Via Firenze 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 5),
    (6, 'Franco', 'Russo', 'Via Venezia 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 6),
    (7, 'Chiara', 'Gallo', 'Via Bologna 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 7),
    (8, 'Davide', 'Conti', 'Via Genova 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 8),
    (9, 'Elena', 'De Luca', 'Via Palermo 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 9),
    (10, 'Giovanni', 'Mancini', 'Via Salerno 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 10),
    (11, 'Simone', 'Costa', 'Via Catania 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 11),
    (12, 'Francesca', 'Giordano', 'Via Messina 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 12),
    (13, 'Giacomo', 'Martini', 'Via Siracusa 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 13),
    (14, 'Luigi', 'Lombardi', 'Via Agrigento 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 14),
    (15, 'Sofia', 'Serra', 'Via Caltanissetta 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 15),
    (16, 'Massimo', 'Rizzo', 'Via Enna 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 16),
    (17, 'Giulia', 'Greco', 'Via Trapani 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 17),
    (18, 'Antonio', 'Fontana', 'Via Agrigento 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 18),
    (19, 'Silvia', 'Santoro', 'Via Caltanissetta 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 19),
    (20, 'Federico', 'Marino', 'Via Enna 1', '00100', 'Roma', 'RM', '06 12345678', '333 1234567', '','','','', 20);
    
    

-- Inserimento di dati nella tabella Permessi
-- Visualizza tutte le pratiche, modifica tutte le pratiche, elimina tutte le pratiche, crea nuova pratica
-- Visualizza le proprie pratiche, modifica le proprie pratiche, elimina le proprie pratiche, crea nuova pratica
-- Visualizza le pratiche del gruppo, modifica le pratiche del gruppo, elimina le pratiche del gruppo, crea nuova pratiche

INSERT IGNORE INTO Permessi (id, nome, descrizione) VALUES
    (1, 'visualizza_pratiche', 'Visualizza tutte le pratiche'),
    (5, 'visualizza_pratiche_gruppo', 'Visualizza le pratiche del gruppo'),
    (41, 'visualizza_pratiche_cliente', 'Visualizza le proprie pratiche');
 
");


    $pdo->exec("

-- Inserimento di un gruppo
INSERT IGNORE INTO Gruppi (id, nome) VALUES 
(1, 'Gruppo A'),
(2, 'Gruppo B'),
(3, 'Gruppo C'),
(4, 'Gruppo D'),
(5, 'Gruppo E'),
(6, 'Gruppo F'),
(7, 'Gruppo G'),
(8, 'Gruppo H'),
(9, 'Gruppo I'),
(10, 'Gruppo L'),
(11, 'Gruppo M'),
(12, 'Gruppo N'),
(13, 'Gruppo O'),
(14, 'Gruppo P'),
(15, 'Gruppo Q'),
(16, 'Gruppo R'),
(17, 'Gruppo S'),
(18, 'Gruppo T'),
(19, 'Gruppo U'),
(20, 'Gruppo V');
-- 



-- Inserimento di una pratica legata al sottogruppo e alla controparte
INSERT IGNORE INTO Pratiche (id, nr_pratica, nome, tipologia, stato, avvocato, referente, competenza, ruolo_generale, giudice, id_gruppo) VALUES 
(1, 'P001', 'Pratica 1', 'Civile', 'Aperta', 'Avvocato 1', 'Referente 1', 'Competenza 1', 'Ruolo Generale 1', 'Giudice 1', 1),
(2, 'P002', 'Pratica 2', 'Penale', 'Aperta', 'Avvocato 2', 'Referente 2', 'Competenza 2', 'Ruolo Generale 2', 'Giudice 2', 2),
(3, 'P003', 'Pratica 3', 'Civile', 'Aperta', 'Avvocato 3', 'Referente 3', 'Competenza 3', 'Ruolo Generale 3', 'Giudice 3', 3),
(4, 'P004', 'Pratica 4', 'Penale', 'Aperta', 'Avvocato 4', 'Referente 4', 'Competenza 4', 'Ruolo Generale 4', 'Giudice 4', 4),
(5, 'P005', 'Pratica 5', 'Civile', 'Aperta', 'Avvocato 5', 'Referente 5', 'Competenza 5', 'Ruolo Generale 5', 'Giudice 5', 5),
(6, 'P006', 'Pratica 6', 'Penale', 'Aperta', 'Avvocato 6', 'Referente 6', 'Competenza 6', 'Ruolo Generale 6', 'Giudice 6', 6),
(7, 'P007', 'Pratica 7', 'Civile', 'Aperta', 'Avvocato 7', 'Referente 7', 'Competenza 7', 'Ruolo Generale 7', 'Giudice 7', 7),
(8, 'P008', 'Pratica 8', 'Penale', 'Aperta', 'Avvocato 8', 'Referente 8', 'Competenza 8', 'Ruolo Generale 8', 'Giudice 8', 8),
(9, 'P009', 'Pratica 9', 'Civile', 'Aperta', 'Avvocato 9', 'Referente 9', 'Competenza 9', 'Ruolo Generale 9', 'Giudice 9', 9),
(10, 'P010', 'Pratica 10', 'Penale', 'Aperta', 'Avvocato 10', 'Referente 10', 'Competenza 10', 'Ruolo Generale 10', 'Giudice 10', 10),
(11, 'P011', 'Pratica 11', 'Civile', 'Aperta', 'Avvocato 11', 'Referente 11', 'Competenza 11', 'Ruolo Generale 11', 'Giudice 11', 11),
(12, 'P012', 'Pratica 12', 'Penale', 'Aperta', 'Avvocato 12', 'Referente 12', 'Competenza 12', 'Ruolo Generale 12', 'Giudice 12', 12),
(13, 'P013', 'Pratica 13', 'Civile', 'Aperta', 'Avvocato 13', 'Referente 13', 'Competenza 13', 'Ruolo Generale 13', 'Giudice 13', 13),
(14, 'P014', 'Pratica 14', 'Penale', 'Aperta', 'Avvocato 14', 'Referente 14', 'Competenza 14', 'Ruolo Generale 14', 'Giudice 14', 14),
(15, 'P015', 'Pratica 15', 'Civile', 'Aperta', 'Avvocato 15', 'Referente 15', 'Competenza 15', 'Ruolo Generale 15', 'Giudice 15', 15),
(16, 'P016', 'Pratica 16', 'Penale', 'Aperta', 'Avvocato 16', 'Referente 16', 'Competenza 16', 'Ruolo Generale 16', 'Giudice 16', 16),
(17, 'P017', 'Pratica 17', 'Civile', 'Aperta', 'Avvocato 17', 'Referente 17', 'Competenza 17', 'Ruolo Generale 17', 'Giudice 17', 17);



-- Inserimento di una scadenza legata alla pratica
INSERT IGNORE INTO Scadenze (id, data, motivo, id_pratica) VALUES 
(1, '2023-06-30', 'Scadenza 1', 1),
(2, '2023-07-31', 'Scadenza 2', 2),
(3, '2023-08-31', 'Scadenza 3', 3),
(4, '2023-09-30', 'Scadenza 4', 4),
(5, '2023-10-31', 'Scadenza 5', 5),
(6, '2023-11-30', 'Scadenza 6', 6),
(7, '2023-12-31', 'Scadenza 7', 7),
(8, '2024-01-31', 'Scadenza 8', 8),
(9, '2024-02-28', 'Scadenza 9', 9),
(10, '2024-03-31', 'Scadenza 10', 10),
(11, '2024-04-30', 'Scadenza 11', 11),
(12, '2024-05-31', 'Scadenza 12', 12),
(13, '2024-06-30', 'Scadenza 13', 13),
(14, '2024-07-31', 'Scadenza 14', 14),
(15, '2024-08-31', 'Scadenza 15', 15),
(16, '2024-09-30', 'Scadenza 16', 16),
(17, '2024-10-31', 'Scadenza 17', 17);


-- Inserimento di un'udienza legata alla pratica
INSERT IGNORE INTO Udienze (id, descrizione, data, id_pratica) VALUES 
(1, 'Udienza 1', '2023-07-15', 1),
(2, 'Udienza 2', '2023-08-15', 2),
(3, 'Udienza 3', '2023-09-15', 3),
(4, 'Udienza 4', '2023-10-15', 4),
(5, 'Udienza 5', '2023-11-15', 5),
(6, 'Udienza 6', '2023-12-15', 6),
(7, 'Udienza 7', '2024-01-15', 7),
(8, 'Udienza 8', '2024-02-15', 8),
(9, 'Udienza 9', '2024-03-15', 9),
(10, 'Udienza 10', '2024-04-15', 10),
(11, 'Udienza 11', '2024-05-15', 11),
(12, 'Udienza 12', '2024-06-15', 12),
(13, 'Udienza 13', '2024-07-15', 13),
(14, 'Udienza 14', '2024-08-15', 14),
(15, 'Udienza 15', '2024-09-15', 15),
(16, 'Udienza 16', '2024-10-15', 16),
(17, 'Udienza 17', '2024-11-15', 17);

-- Inserimento di una nota legata alla pratica
INSERT IGNORE INTO Note 
(id, tipologia, descrizione, visibilita, id_pratica) VALUES (1, 'Nota 1', 'Testo nota 1', 'Privata', 1),
(2, 'Nota 2', 'Testo nota 2', 'Privata', 2),
(3, 'Nota 3', 'Testo nota 3', 'Privata', 3),
(4, 'Nota 4', 'Testo nota 4', 'Privata', 4),
(5, 'Nota 5', 'Testo nota 5', 'Privata', 5),
(6, 'Nota 6', 'Testo nota 6', 'Privata', 6),
(7, 'Nota 7', 'Testo nota 7', 'Privata', 7),
(8, 'Nota 8', 'Testo nota 8', 'Privata', 8),
(9, 'Nota 9', 'Testo nota 9', 'Privata', 9),
(10, 'Nota 10', 'Testo nota 10', 'Privata', 10),
(11, 'Nota 11', 'Testo nota 11', 'Privata', 11),
(12, 'Nota 12', 'Testo nota 12', 'Privata', 12),
(13, 'Nota 13', 'Testo nota 13', 'Privata', 13),
(14, 'Nota 14', 'Testo nota 14', 'Privata', 14),
(15, 'Nota 15', 'Testo nota 15', 'Privata', 15),
(16, 'Nota 16', 'Testo nota 16', 'Privata', 16),
(17, 'Nota 17', 'Testo nota 17', 'Privata', 17);

");

    echo "Database inizializzato correttamente!";
} catch (PDOException $e) {
    echo "Errore durante l'inizializzazione del database: " . $e->getMessage();
    exit();
}



