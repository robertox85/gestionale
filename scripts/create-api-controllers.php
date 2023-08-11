<?php

// Connessione al database utilizzando PDO
use App\Libraries\Helper;
use App\Libraries\QueryBuilder;
use App\Libraries\DynamicFormComponent;
use App\Libraries\ResponseHelper; // Nuova libreria per le risposte delle API
use App\Models\{NomeModel1, NomeModel2, /* Aggiungi altri modelli */};

$pdo = new PDO('mysql:host=127.0.0.1;dbname=gestionale', 'root', '');

// Query per ottenere l'elenco delle tabelle nel database
$sql = "SHOW TABLES";
$stmt = $pdo->query($sql);

// Ottieni l'elenco delle tabelle
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Genera tutti i Controller per le tabelle nel database
foreach ($tables as $tableName) {
    $singularTableName = $tableName;
    // Nome della tabella in formato CamelCase
    $controllerName = ucfirst(str_replace('_', '', ucwords($tableName, '_'))) . 'ApiController';

    // Imposta l'alias per l'id della tabella DisponibilitaSale se necessario
    // $idAlias = ($tableName === 'DisponibilitaSale') ? 'id_disponibilita' : 'id_' . strtolower($singularTableName);
    // Genera l'alias dell'id in base al nome della tabella
    $idAlias = 'id_' . strtolower($singularTableName);
    if (preg_match('/[A-Z]/', $singularTableName)) {
        // ... (rimuovi la parte che calcola l'id in base al nome della tabella)
    }

    // Genera il codice del Controller per le API
    $controllerCode = "<?php\n\nnamespace App\Controllers\Api;\n\nuse App\Controllers\Web\BaseController;\nuse App\Libraries\QueryBuilder;\nuse App\Models\\" . ucfirst($tableName) . ";\nuse App\Libraries\ResponseHelper;\n\nclass " . $controllerName . " extends BaseController {\n";

    // Azione per l'index (elenco dei record)
    $controllerCode .= "\n\tpublic function index(): void\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\t\$qb = new QueryBuilder(\$this->db);\n";
    $controllerCode .= "\t\t\$qb = \$qb->setTable('" . ucfirst($tableName) . "');\n";
    $controllerCode .= "\t\t\$qb = \$qb->select('*');\n";
    $controllerCode .= "\t\t\$rows = \$qb->get();\n";
    $controllerCode .= "\t\t\$pagination = \$qb->getPagination();\n";
    $controllerCode .= "\t\t\$columns = \$qb->getColumns();\n";
    $controllerCode .= "\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t'rows' => \$rows,\n";
    $controllerCode .= "\t\t\t'pagination' => \$pagination,\n";
    $controllerCode .= "\t\t\t'columns' => \$columns,\n";
    $controllerCode .= "\t\t]);\n";
    $controllerCode .= "\t}\n";

    // Azione per l'edit di un record
    $controllerCode .= "\n\tpublic function edit(\$id)\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\t\$" . strtolower($tableName) . " = " . ucfirst($tableName) . "::get(\$id);\n";
    $controllerCode .= "\t\tif (!\$" . strtolower($tableName) . ") {\n";
    $controllerCode .= "\t\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t\t'error' => 'Record non trovato.',\n";
    $controllerCode .= "\t\t\t]);\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t}\n";
    $controllerCode .= "\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t'" . strtolower($tableName) . "' => \$" . strtolower($tableName) . ",\n";
    $controllerCode .= "\t\t]);\n";
    $controllerCode .= "\t\texit();\n";
    $controllerCode .= "\t}\n";

    // Azione per la creazione di un nuovo record
    $controllerCode .= "\n\tpublic function create(): void\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\t\$entity = new " . ucfirst($tableName) . "();\n";
    $controllerCode .= "\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t'entity' => \$entity,\n";
    $controllerCode .= "\t\t]);\n";
    $controllerCode .= "\t}\n";

    // Azione per lo store (creazione di un nuovo record)
    $controllerCode .= "\n\tpublic function store(): void\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\ttry {\n";
    $controllerCode .= "\t\t\t\$post = \$_POST;\n";
    $controllerCode .= "\t\t\t// Validazione e altre operazioni per l'inserimento dei dati\n";
    $controllerCode .= "\t\t\t// ...\n";
    $controllerCode .= "\t\t\t\$newId = " . ucfirst($tableName) . "::create(\$post);\n";
    $controllerCode .= "\t\t\tif (\$newId !== false) {\n";
    $controllerCode .= "\t\t\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t\t\t'success' => 'Record creato con successo.',\n";
    $controllerCode .= "\t\t\t\t]);\n";
    $controllerCode .= "\t\t\t} else {\n";
    $controllerCode .= "\t\t\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t\t\t'error' => 'Errore durante la creazione del record.',\n";
    $controllerCode .= "\t\t\t\t]);\n";
    $controllerCode .= "\t\t\t}\n";
    $controllerCode .= "\t\t} catch (\\Exception \$e) {\n";
    $controllerCode .= "\t\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t\t'error' => \$e->getMessage(),\n";
    $controllerCode .= "\t\t\t]);\n";
    $controllerCode .= "\t\t}\n";
    $controllerCode .= "\t}\n";

    // Azione per l'update (aggiornamento di un record)
    $controllerCode .= "\n\tpublic function update(): void\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\ttry {\n";
    $controllerCode .= "\t\t\t\$post = \$_POST;\n";
    $controllerCode .= "\t\t\t// Validazione e altre operazioni per l'aggiornamento dei dati\n";
    $controllerCode .= "\t\t\t// ...\n";
    $controllerCode .= "\t\t\t\$newId = " . ucfirst($tableName) . "::update(\$post);\n";
    $controllerCode .= "\t\t\tif (\$newId !== false) {\n";
    $controllerCode .= "\t\t\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t\t\t'success' => 'Record aggiornato con successo.',\n";
    $controllerCode .= "\t\t\t\t]);\n";
    $controllerCode .= "\t\t\t} else {\n";
    $controllerCode .= "\t\t\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t\t\t'error' => 'Errore durante l\'aggiornamento del record.',\n";
    $controllerCode .= "\t\t\t\t]);\n";
    $controllerCode .= "\t\t\t}\n";
    $controllerCode .= "\t\t} catch (\\Exception \$e) {\n";
    $controllerCode .= "\t\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t\t'error' => \$e->getMessage(),\n";
    $controllerCode .= "\t\t\t]);\n";
    $controllerCode .= "\t\t}\n";
    $controllerCode .= "\t}\n";

    // Azione per il delete (eliminazione di un record)
    $controllerCode .= "\n\tpublic function delete(\$id): void\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\t\$" . strtolower($tableName) . " = " . ucfirst($tableName) . "::get(\$id);\n";
    $controllerCode .= "\t\tif (!\$" . strtolower($tableName) . ") {\n";
    $controllerCode .= "\t\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t\t'error' => 'Record non trovato.',\n";
    $controllerCode .= "\t\t\t]);\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t}\n";
    $controllerCode .= "\t\t\$" . strtolower($tableName) . "->delete();\n";
    $controllerCode .= "\t\techo ResponseHelper::jsonResponse([\n";
    $controllerCode .= "\t\t\t'success' => 'Record eliminato con successo.',\n";
    $controllerCode .= "\t\t]);\n";
    $controllerCode .= "\t}\n";

    // Chiudi la classe del Controller
    $controllerCode .= "}\n";

    // Salva il codice generato in un file
    file_put_contents('app/Controllers/Api/' . $controllerName . '.php', $controllerCode);

    echo "Controller generato con successo per le API: " . $controllerName . ".php\n";
}

echo "Generazione di tutti i Controller per le API completata!\n";
