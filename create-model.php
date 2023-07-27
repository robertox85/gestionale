<?php

// Dopo aver ottenuto $columnData con i dettagli delle colonne della tabella
// createModel.php

// Verifica se è stato specificato il nome dell'entità come argomento da linea di comando
// createModel.php

// Verifica se è stato specificato il nome dell'entità come argomento da linea di comando


if (isset($argv[1])) {
    $entityName = $argv[1];

    // Connessione al database utilizzando PDO
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gestionale', 'root', '');

    // Query per ottenere i dettagli della tabella corrispondente all'entità
    $sql = "DESCRIBE $entityName";

    try {
        $shortClassName = $entityName;
        $tableName = getPluralName($shortClassName);
        $singularLower = strtolower($tableName);
        $singularUpper = ucfirst($tableName);
        echo "Generazione del modello per l'entità $singularUpper...\n";
    } catch (ReflectionException $e) {
        echo $e->getMessage();
        exit();
    }

    // Esegui la query
    $stmt = $pdo->query($sql);

    // Ottieni i dettagli delle colonne della tabella
    $columnData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ora puoi utilizzare $columnData per generare il codice del modello
    // Esempio: costruire una stringa contenente il codice del modello
    $modelCode = "<?php\n\n namespace App\Models; \n\n class {$singularUpper} extends BaseModel { \n ";

    foreach ($columnData as $column) {
        // Ottieni il nome e il tipo di colonna
        $columnName = $column['Field'];
        $columnType = $column['Type'];

        // Formatta il nome della colonna per utilizzarlo come nome della proprietà
        // Puoi personalizzare il formato del nome della proprietà in base alle tue preferenze
        // Ad esempio, utilizzando la convenzione CamelCase
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

        // Genera la proprietà e il metodo setter nel costruttore
        $modelCode .= "\t\tprivate $propertyType \$$propertyName;\n";
    }

    // Genera i metodi getter
    foreach ($columnData as $column) {
        // Ottieni il nome della colonna
        $columnName = $column['Field'];

        // Formatta il nome della colonna per utilizzarlo come nome della proprietà
        $propertyName = lcfirst(str_replace('_', '', ucwords($columnName, '_')));

        // Genera il metodo getter
        $modelCode .= "\n\tpublic function get" . ucfirst($propertyName) . "() {\n";
        $modelCode .= "\t\treturn \$this->$columnName;\n";
        $modelCode .= "\t}\n";
    }

    // Chiudi la classe del modello
    $modelCode .= "}\n";

    // Salva il codice generato in un file
    file_put_contents($singularUpper . '.php', $modelCode);
    // move file to Models folder
    rename($singularUpper . '.php', 'app/Models/' . $singularUpper . '.php');

    echo "Modello generato con successo: {$singularUpper}.php\n";
} else {
    echo "Inserisci il nome dell'entità come argomento (es. php createModel Recensioni)\n";
}

function getPluralName(string $shortClassName)
{
    // in Italiano
    if (substr($shortClassName, -2) === 'ca') {
        return substr($shortClassName, 0, -2) . 'che';
    } elseif (substr($shortClassName, -1) === 'o') {
        return substr($shortClassName, 0, -1) . 'i';
    } elseif (substr($shortClassName, -1) === 'e') {
        return substr($shortClassName, 0, -1) . 'a';
    } elseif (substr($shortClassName, -1) === 'a') {
        return substr($shortClassName, 0, -1) . 'e';
    } elseif (substr($shortClassName, -1) === 'i') {
        return substr($shortClassName, 0, -1) . 'i';
    } elseif (substr($shortClassName, -1) === 'u') {
        return substr($shortClassName, 0, -1) . 'i';
    } elseif (substr($shortClassName, -1) === 's') {
        return $shortClassName;
    } else {
        return $shortClassName . 's';
    }
}