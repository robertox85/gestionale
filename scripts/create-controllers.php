<?php

// Connessione al database utilizzando PDO
use App\Libraries\Helper;
use App\Libraries\QueryBuilder;
use App\Models\Prenotazioni;

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
    $controllerName = ucfirst(str_replace('_', '', ucwords($tableName, '_'))) . 'Controller';

    // Imposta l'alias per l'id della tabella DisponibilitaSale se necessario
    // $idAlias = ($tableName === 'DisponibilitaSale') ? 'id_disponibilita' : 'id_' . strtolower($singularTableName);
    // Genera l'alias dell'id in base al nome della tabella
    $idAlias = 'id_' . strtolower($singularTableName);
    if (preg_match('/[A-Z]/', $singularTableName)) {
        $words = preg_split('/(?=[A-Z])/', $singularTableName, -1, PREG_SPLIT_NO_EMPTY);
        $action = strtolower(implode('-', $words));
        $string = strtolower(implode('_', $words));
        $array = explode('_', $string);
        $id = getSingularName($array[0]);
        $idAlias = 'id_' . $id;

    }


    // Genera il codice del Controller
    $controllerCode = "<?php\n\nnamespace App\Controllers\Web;\n\nuse App\Models\\" . ucfirst($tableName) . ";\nuse App\Libraries\QueryBuilder;\nuse App\Libraries\DynamicFormComponent;\nuse App\Libraries\Helper;\n\nclass " . $controllerName . " extends BaseController {\n";

    // Azione per la vista generale
    $controllerCode .= "\n\tpublic function index() {\n";
    $controllerCode .= "\t\t\$qb = new QueryBuilder(\$this->db);\n";
    $controllerCode .= "\t\t\$qb = \$qb->setTable('" . ucfirst($tableName) . "');\n";
    $controllerCode .= "\t\t// Seleziona tutte le colonne dalla tabella con alias per l'ID\n";
    $controllerCode .= "\t\t\$qb = \$qb->select('*');\n";
    $controllerCode .= "\t\t\$qb = \$qb->setAlias('{$idAlias}', 'id');\n";
    $controllerCode .= "\t\t\$rows = \$qb->get();\n";
    $controllerCode .= "\t\t\$pagination = \$qb->getPagination();\n";
    $controllerCode .= "\t\t\$columns = \$qb->getColumns();\n";
    $controllerCode .= "\t\t// Puoi personalizzare la vista utilizzata per l'elenco\n";
    //$controllerCode .= "\t\t" . 'echo $this->view->render(\'' . $singularTableName . '/index.twig\', compact(\'columns\', \'rows\', \'pagination\'));' . "\n";
    $controllerCode .= "\t\t" . 'echo $this->view->render(\'list.html.twig\', compact(\'columns\', \'rows\', \'pagination\'));' . "\n";

    $controllerCode .= "\t\texit();\n";
    $controllerCode .= "\t}\n";

    // Azione per la creazione di un nuovo record
    $controllerCode .= "\n\tpublic function create(): void\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\t\$entity = new " . ucfirst($tableName) . "();\n";
    $controllerCode .= "\t\t\$formComponent = new DynamicFormComponent(\$entity);\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\$formData = [];\n";
    $controllerCode .= "\t\t\$formData['action'] = \$this->url('" . strtolower($action) . "/store');\n";
    $controllerCode .= "\t\t\$formData['csrf_token'] = Helper::generateToken('" . ucfirst($tableName) . "');\n";
    $controllerCode .= "\t\t\$formData['button_label'] = 'Crea';\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\$formHtml = \$formComponent->renderForm(\$formData);\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t// Puoi personalizzare la vista utilizzata per il form di creazione\n";
    $controllerCode .= "\t\techo \$this->view->render('newform.html.twig', compact('formHtml'));\n";
    $controllerCode .= "\t}\n";

    // Azione per l'edit di un record
    $controllerCode .= "\n\tpublic function edit(\$id)\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\t\$" . strtolower($tableName) . " = " . ucfirst($tableName) . "::find(\$id);\n";
    $controllerCode .= "\t\tif (!\$" . strtolower($tableName) . ") {\n";
    $controllerCode .= "\t\t\tHelper::addError('Record non trovato.');\n";
    $controllerCode .= "\t\t\tHelper::redirect('/" . strtolower($action) . "');\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t}\n";
    $controllerCode .= "\t\t\$formComponent = new DynamicFormComponent(\$" . strtolower($tableName) . ");\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\$formData = [];\n";
    $controllerCode .= "\t\t\$formData['action'] = \$this->url('" . strtolower($action) . "/update');\n";
    $controllerCode .= "\t\t\$formData['csrf_token'] = Helper::generateToken('" . ucfirst($tableName) . "');\n";
    $controllerCode .= "\t\t\$formData['" . $idAlias . "'] = \$id;\n";
    $controllerCode .= "\t\t\$formData['button_label'] = 'Edit';\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\$formHtml = \$formComponent->renderForm(\$formData);\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\techo \$this->view->render('newform.html.twig', compact('formHtml'));\n";
    $controllerCode .= "\t}\n";

    // Azione per lo store
    $controllerCode .= "\n\tpublic function store()\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\ttry {\n";
    $controllerCode .= "\t\t\t\$post = \$_POST;\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\t// Verifica il token CSRF\n";
    $controllerCode .= "\t\t\tif (!Helper::validateToken('" . ucfirst($tableName) . "', \$post['csrf_token'])) {\n";
    $controllerCode .= "\t\t\t\tHelper::addError('Token CSRF non valido.');\n";
    $controllerCode .= "\t\t\t\tHelper::redirect('/" . strtolower($action) . "');\n";
    $controllerCode .= "\t\t\t\texit();\n";
    $controllerCode .= "\t\t\t}\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\tunset(\$post['csrf_token']);\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\t\$post = Helper::sanificaInput(\$post);\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\t\$newId = " . ucfirst($tableName) . "::create(\$post);\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\tif (\$newId !== false) {\n";
    $controllerCode .= "\t\t\t\tHelper::addSuccess('Nuovo record creato con successo.');\n";
    $controllerCode .= "\t\t\t} else {\n";
    $controllerCode .= "\t\t\t\tHelper::addError('Errore durante la creazione o l\'aggiornamento del record.');\n";
    $controllerCode .= "\t\t\t}\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\tHelper::redirect('/" . strtolower($action) . "');\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t} catch (\Exception \$e) {\n";
    $controllerCode .= "\t\t\tHelper::addError(\$e->getMessage());\n";
    $controllerCode .= "\t\t\tHelper::redirect('/" . strtolower($action) . "');\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t}\n";
    $controllerCode .= "\t}\n";

    // Azione per l'update
    $controllerCode .= "\n\tpublic function update()\n";
    $controllerCode .= "\t{\n";
    $controllerCode .= "\t\ttry {\n";
    $controllerCode .= "\t\t\t\$post = \$_POST;\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\t// Verifica il token CSRF\n";
    $controllerCode .= "\t\t\tif (!Helper::validateToken('" . ucfirst($tableName) . "', \$post['csrf_token'])) {\n";
    $controllerCode .= "\t\t\t\tHelper::addError('Token CSRF non valido.');\n";
    $controllerCode .= "\t\t\t\tHelper::redirect('/" . strtolower($action) . "');\n";
    $controllerCode .= "\t\t\t\texit();\n";
    $controllerCode .= "\t\t\t}\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\tunset(\$post['csrf_token']);\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\t\$post = Helper::sanificaInput(\$post);\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\t\$newId = " . ucfirst($tableName) . "::update(\$post);\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\tif (\$newId !== false) {\n";
    $controllerCode .= "\t\t\t\tHelper::addSuccess('Record aggiornato con successo.');\n";
    $controllerCode .= "\t\t\t} else {\n";
    $controllerCode .= "\t\t\t\tHelper::addError('Errore durante la creazione o l\'aggiornamento del record.');\n";
    $controllerCode .= "\t\t\t}\n";
    $controllerCode .= "\n";
    $controllerCode .= "\t\t\tHelper::redirect('/" . strtolower($action) . "');\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t} catch (\Exception \$e) {\n";
    $controllerCode .= "\t\t\tHelper::addError(\$e->getMessage());\n";
    $controllerCode .= "\t\t\tHelper::redirect('/" . strtolower($action) . "');\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t}\n";
    $controllerCode .= "\t}\n";


    // Azione per il delete
    // Azione per il delete
    $controllerCode .= "\n\tpublic function delete(\$id) {\n";
    $controllerCode .= "\t\ttry {\n";
    $controllerCode .= "\t\t\t(new " . ucfirst($singularTableName) . ")->delete(\$id);\n";
    $controllerCode .= "\t\t\tHelper::addSuccess('Record eliminato con successo!');\n";
    $controllerCode .= "\t\t\t\$current_page = Helper::getCurrentPage();\n";
    $controllerCode .= "\t\t\tHelper::redirect('/' . \$current_page);\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t} catch (\\Exception \$e) {\n";
    $controllerCode .= "\t\t\tHelper::addError(\$e->getMessage());\n";
    $controllerCode .= "\t\t\tHelper::redirect('/{$action}');\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t}\n";
    $controllerCode .= "\t}\n";


    // Azione per il bulk delete
    // Azione per il bulkDelete
    $controllerCode .= "\n\tpublic function bulkDelete() {\n";
    $controllerCode .= "\t\ttry {\n";
    $controllerCode .= "\t\t\t\$qb = new QueryBuilder(\$this->db);\n";
    $controllerCode .= "\t\t\t\$qb = \$qb->setTable('" . ucfirst($tableName) . "');\n";
    $controllerCode .= "\t\t\t\$ids = \$_POST['ids'];\n";
    $controllerCode .= "\t\t\t// Turn into array if not already\n";
    $controllerCode .= "\t\t\tif (!is_array(\$ids)) {\n";
    $controllerCode .= "\t\t\t\t\$ids = explode(',', \$ids);\n";
    $controllerCode .= "\t\t\t\t\$ids = array_filter(\$ids);\n";
    $controllerCode .= "\t\t\t\t\$ids = array_map('intval', \$ids);\n";
    $controllerCode .= "\t\t\t}\n";
    $controllerCode .= "\t\t\t\$qb = \$qb->whereIn('" . $idAlias . "', \$ids);\n";
    $controllerCode .= "\t\t\t\$qb = \$qb->delete();\n";
    $controllerCode .= "\t\t\t\$qb->execute();\n";
    $controllerCode .= "\t\t\tHelper::addSuccess('Record eliminati con successo!');\n";
    $controllerCode .= "\t\t\t\$current_page = Helper::getCurrentPage();\n";
    $controllerCode .= "\t\t\tHelper::redirect('/' . \$current_page);\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t} catch (\\Exception \$e) {\n";
    $controllerCode .= "\t\t\tHelper::addError(\$e->getMessage());\n";
    $controllerCode .= "\t\t\t\$current_page = Helper::getCurrentPage();\n";
    $controllerCode .= "\t\t\tHelper::redirect('/' . \$current_page);\n";
    $controllerCode .= "\t\t\texit();\n";
    $controllerCode .= "\t\t}\n";
    $controllerCode .= "\t}\n";


    // Chiudi la classe del Controller
    $controllerCode .= "}\n";

    // Salva il codice generato in un file
    file_put_contents('app/Controllers/Web/' . $controllerName . '.php', $controllerCode);

    echo "Controller generato con successo: " . $controllerName . ".php\n";
}

echo "Generazione di tutti i Controller completata!\n";


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

function getSingularName(string $pluralClassName)
{
    // Espressioni regolari per individuare le terminazioni comuni dei nomi plurali
    $pluralEndings = [
        '/i$/' => 'e',         // es. "Utenti" diventa "Utente"
        '/che$/' => 'ca',      // es. "DisponibilitaSale" diventa "DisponibilitaSala"
        '/zi$/' => 'zio',         // es. "Servizi" diventa "Servizio"
        '/le$/' => 'la',         // es. "DisponibilitaSale" diventa "DisponibilitaSala"
        '/e$/' => 'a',         // es. "DisponibilitaSale" diventa "DisponibilitaSala"
        // Aggiungi altre regole se necessario
    ];

    // Verifica se il nome plurale corrisponde a una delle regole
    foreach ($pluralEndings as $pattern => $singularEnding) {
        if (preg_match($pattern, $pluralClassName)) {
            return preg_replace($pattern, $singularEnding, $pluralClassName);
        }
    }

    // Se nessuna corrispondenza con le regole, rimuovi una "s" finale (implementazione fallback)
    if (substr($pluralClassName, -1) === 's') {
        return substr($pluralClassName, 0, -1);
    }

    // Se non è possibile trovare il nome singolare, ritorna semplicemente il nome plurale
    return $pluralClassName;
}