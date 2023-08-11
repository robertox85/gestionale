<?php


// Connessione al database utilizzando PDO
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gestionale', 'root', '');

// Query per ottenere l'elenco delle tabelle nel database
$sql = "SHOW TABLES";
$stmt = $pdo->query($sql);

// Ottieni l'elenco delle tabelle
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// read app/Attributes folder
$attributesFolder = scandir('app/Attributes');
$attributesFolder = array_filter($attributesFolder, function ($item) {
    return !in_array($item, ['.', '..']);
});

$hidden_columns = ['created_at', 'updated_at', 'deleted_at'];
$arr = [];
foreach ($tables as $tableName) {
    $arr[$tableName] = [];
    // Query per ottenere i dettagli delle colonne della tabella
    $sql = "DESCRIBE $tableName";
    $stmt = $pdo->query($sql);
    $columnData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Genera il codice del modello per la tabella corrente
    $modelCode = "<?php\n\nnamespace App\Models;\n\n";
    // Include attributi personalizzati
    foreach ($attributesFolder as $attribute) {
        $modelCode .= "use App\Attributes\\" . str_replace('.php', '', $attribute) . ";\n";
    }
    $modelCode .= "\n\nclass " . ucfirst($tableName) . " extends BaseModel {\n";


    $isPrimaryKeyDefined = false;
    $isLabelColumnDefined = false;

    foreach ($columnData as $column) {
        $arr[$tableName][] = [
            $column
        ];
        // Ottieni il nome e il tipo di colonna
        $columnName = $column['Field'];
        $columnType = $column['Type'];

        $propertyName = $columnName;
        $propertyVal = getPropertyValue($column);
        $propertyType = getPropertyType($column);
        $propertyAttr = getPropertyAttribute($column);
        $attributes = [];

        // add required if column is not nullable
        if ($column['Null'] === 'YES') {
            $attributes[] = 'Required';
        }

        // Aggiungi l'attributo PrimaryKey alla prima colonna
        if ($column['Key'] == 'PRI') {
            $attributes[] = 'PrimaryKey';
            $attributes[] = 'Hidden';
        }

        if ($column['Key'] != 'PRI' && !$isLabelColumnDefined) {
            if (str_starts_with($column['Field'], 'id_')) {
                $attributes[] = 'ForeignKey';
                $attributes[] = 'Required';
            } else {
                // Altrimenti, marcala come LabelColumn
                if ($propertyType == 'string') {
                    $attributes[] = 'LabelColumn';
                    $isLabelColumnDefined = true;
                }
            }
        }

        if (in_array($column['Field'], $hidden_columns)) {
            $attributes[] = 'Hidden';
        }

        if (!empty($propertyAttr)) {
            $attributes[] = $propertyAttr;
        }

        if (!empty($attributes)) {
            $modelCode .= "\n\t#[" . implode(', ', $attributes) . "]\n";
        }

        // Genera la proprietà e il metodo getter nel modello, inizializzando la proprietà con il valore predefinito in base al tipo di dato
        if ($propertyType == 'array') {
            // each value must be quoted
            $propertyVal = array_map(function ($value) {
                return "'$value'";
            }, $propertyVal);
            $modelCode .= "\n\tprotected $propertyType \$$propertyName = [" . implode(', ', $propertyVal) . "];\n";
        } else {
            if ($propertyVal == '') {
                $modelCode .= "\n\tprotected $propertyType \$$propertyName;\n";
            } else {
                $modelCode .= "\n\tprotected $propertyType \$$propertyName = " . $propertyVal . ";\n";
            }
        }

        // CamelCase
        $getterName = str_replace('_', '', lcfirst(ucwords($columnName, '_')));


        //$modelCode .= "\n\tpublic function get" . ucfirst($getterName) . "() {\n";
        //$modelCode .= "\t\treturn \$this->$propertyName;\n";
        //$modelCode .= "\t}\n";

        //// Genera il metodo setter nel modello
        //$modelCode .= "\n\tpublic function set" . ucfirst($getterName) . "(\$$propertyName) {\n";
        //$modelCode .= "\t\t\$this->$propertyName = \$$propertyName;\n";
        //$modelCode .= "\t}\n";
    }

    // Chiudi la classe del modello
    $modelCode .= "}\n";


    // Salva il codice generato in un file
    file_put_contents('app/Models/' . ucfirst($tableName) . '.php', $modelCode);

    echo "Modello generato con successo: " . ucfirst($tableName) . ".php\n";

}

function getPropertyType($column)
{
    $columnType = $column['Type'];
    if (strpos($columnType, 'int') === 0) {
        $propertyType = 'int';
    } elseif (strpos($columnType, 'varchar') === 0) {
        $propertyType = 'string';
    } elseif (strpos($columnType, 'text') === 0) {
        $propertyType = 'string';
    } elseif (strpos($columnType, 'longtext') === 0) {
        $propertyType = 'string';
    } elseif (strpos($columnType, 'datetime') === 0) {
        $propertyType = '?\DateTime';
    } elseif (strpos($columnType, 'date') === 0) {
        $propertyType = '?\DateTime';
    } elseif (strpos($columnType, 'time') === 0) {
        $propertyType = '?\DateTime';
    } elseif (strpos($columnType, 'enum') === 0) {
        $propertyType = 'array';
    } elseif (strpos($columnType, 'bool') === 0) {
        $propertyType = 'bool';
    } else {
        $propertyType = 'mixed';
    }
    return $propertyType;
}

function getPropertyAttribute(mixed $column)
{
    $columnType = $column['Type'];
    if (strpos($columnType, 'enum') === 0) {
        return 'DropDown';
    }

    if (strpos($columnType, 'datetime') === 0) {
        return 'DateFormat("d/m/Y H:i:s")';
    }

    if (strpos($columnType, 'date') === 0) {
        return 'DateFormat("d/m/Y")';
    }

    if (strpos($columnType, 'time') === 0) {
        return 'DateFormat("H:i:s")';
    }

    return null;
}

function getPropertyValue(mixed $column)
{
    $columnType = $column['Type'];
    if (strpos($columnType, 'enum') === 0) {
        preg_match_all("/'([^']+)'/", $columnType, $matches);
        return $matches[1];
    }

    $propertyValue = $column['Default'];

    return null;
}


echo "Generazione di tutti i modelli completata!\n";