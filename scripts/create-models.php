<?php

// Connessione al database utilizzando PDO
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gestionale', 'root', '');

// Query per ottenere l'elenco delle tabelle nel database
$sql = "SHOW TABLES";
$stmt = $pdo->query($sql);

// Ottieni l'elenco delle tabelle
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Genera tutti i modelli per le tabelle nel database
foreach ($tables as $tableName) {
    // Query per ottenere i dettagli delle colonne della tabella
    $sql = "DESCRIBE $tableName";
    $stmt = $pdo->query($sql);
    $columnData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Genera il codice del modello per la tabella corrente
    $modelCode = "<?php\n\nnamespace App\Models;\n\nclass " . ucfirst($tableName) . " extends BaseModel {\n";

    foreach ($columnData as $column) {
        // Ottieni il nome e il tipo di colonna
        $columnName = $column['Field'];
        $columnType = $column['Type'];

        // Formatta il nome della colonna per utilizzarlo come nome della proprietà
        $propertyName = $columnName;

        // Determina il tipo di dato corrispondente per la proprietà
        // Puoi definire una mappatura dei tipi di colonna a tipi di dato nel modello
        if (strpos($columnType, 'int') === 0) {
            $propertyType = 'int';
        } elseif (strpos($columnType, 'varchar') === 0) {
            $propertyType = 'string';
        } else {
            // Tipo di colonna sconosciuto, utilizza "mixed" come tipo di dato della proprietà
            $propertyType = 'mixed';
        }


        // Genera la proprietà e il metodo getter nel modello
        $modelCode .= "\n\tprivate $propertyType \$$propertyName;\n";

        // CamelCase
        $getterName = str_replace('_', '', lcfirst(ucwords($columnName, '_')));


        $modelCode .= "\n\tpublic function get" . ucfirst($getterName) . "() {\n";
        $modelCode .= "\t\treturn \$this->$propertyName;\n";
        $modelCode .= "\t}\n";


        // Genera il metodo setter nel modello
        $modelCode .= "\n\tpublic function set" . ucfirst($getterName) . "(\$$propertyName) {\n";
        $modelCode .= "\t\t\$this->$propertyName = \$$propertyName;\n";
        $modelCode .= "\t}\n";
    }

    // Chiudi la classe del modello
    $modelCode .= "}\n";

    // Salva il codice generato in un file
    file_put_contents('app/Models/' . ucfirst($tableName) . '.php', $modelCode);

    echo "Modello generato con successo: " . ucfirst($tableName) . ".php\n";
}

echo "Generazione di tutti i modelli completata!\n";
