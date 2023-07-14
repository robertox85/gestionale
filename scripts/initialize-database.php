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
          username VARCHAR(50),
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
INSERT IGNORE INTO Utenti (id, username, email, password, id_ruolo) VALUES
  (1, 'admin', 'mario@example.com', 'password123', 1), /* Amministratore  */ 
  (2, 'referente', 'luca@example.com', 'password456', 3), /* Referente  */ 
  (3, 'cliente', 'laura@example.com', 'password789', 5), /* Cliente  */
  (4, 'controparte', 'matteo@example.com', 'password789', 6), /* Controparte  */
  (5, 'dominus', 'anna@example.com', 'password456', 2),  /* Dominus  */
  (6, 'Segreteria', 'franco@example.com', 'password789', 4), /* Segreteria  */
  (7, '', 'chiara@example.com', 'password321', 1),
  (8, '', 'davide@example.com', 'password654', 3),
  (9, '', 'elena@example.com', 'password987', 2),
  (10,'',  'giovanni@example.com', 'password741', 1),
  (11,'',  'simone@example.com', 'password852', 3),
  (12,'',  'francesca@example.com', 'password963', 4),
  (13,'',  'giacomo@example.com', 'password123', 1),
  (14,'',  'luigi@example.com', 'password456', 3),
  (15,'',  'sofia@example.com', 'password789', 2),
  (16,'',  'massimo@example.com', 'password321', 1),
  (17,'',  'giulia@example.com', 'password654', 4),
  (18,'',  'antonio@example.com', 'password987', 1),
  (19,'',  'silvia@example.com', 'password741', 3),
  (20,'',  'federico@example.com', 'password852', 2);

  


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
    (2, 'modifica_pratiche', 'Modifica tutte le pratiche'),
    (3, 'elimina_pratiche', 'Elimina tutte le pratiche'),
    (4, 'crea_pratica', 'Crea nuova pratica'),
    (5, 'visualizza_pratiche_proprie', 'Visualizza le proprie pratiche'),
    (6, 'modifica_pratiche_proprie', 'Modifica le proprie pratiche'),
    (7, 'elimina_pratiche_proprie', 'Elimina le proprie pratiche'),
    (8, 'crea_pratica_propria', 'Crea nuova pratica'),
    (9, 'visualizza_pratiche_gruppo', 'Visualizza le pratiche del gruppo'),
    (10, 'modifica_pratiche_gruppo', 'Modifica le pratiche del gruppo'),
    (11, 'elimina_pratiche_gruppo', 'Elimina le pratiche del gruppo'),
    (12, 'crea_pratica_gruppo', 'Crea nuova pratica');
 
");


    $pdo->exec("

-- Inserimento di un gruppo
INSERT IGNORE INTO Gruppi (id, nome) VALUES 
(1, 'A'),
(2, 'B'),
(3, 'C'),
(4, 'D'),
(5, 'E'),
(6, 'F'),
(7, 'G'),
(8, 'H'),
(9, 'I'),
(10, 'L'),
(11, 'M'),
(12, 'N'),
(13, 'O'),
(14, 'P'),
(15, 'Q'),
(16, 'R'),
(17, 'S'),
(18, 'T'),
(19, 'U'),
(20, 'V');
-- 



-- Inserimento di una pratica legata al sottogruppo e alla controparte


-- Inserimento di un permetto legato all ruolo
INSERT IGNORE INTO Ruoli_Permessi(id, ruolo_id, permesso_id) VALUES
(3,5,1),
(21,1,1),
(22,1,2),
(23,1,3),
(24,1,4);

");

    echo "Database inizializzato correttamente!";
} catch (PDOException $e) {
    echo "Errore durante l'inizializzazione del database: " . $e->getMessage();
    exit();
}



