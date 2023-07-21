<?php

namespace App\Models;

use App\Libraries\Database;

class BaseModel
{
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->load([':id' => $id]);
        }
    }


    // Load by id
    public function load($params = [])
    {

        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);

        $sql = "SELECT * FROM " . $tableName . " WHERE id = :id";
        $options = [
            'query' => $sql,
            'params' => $params
        ];
        $result = $db->query($options);
        if (isset($result[0])) {
            foreach ($result[0] as $property => $value) {
                // skip id, created_at, updated_at.
                // TODO: rimosso temporaneamente
                /*if ($property === 'created_at' || $property === 'updated_at') {
                    continue;
                }*/

                $setterMethod = 'set' . ucfirst(str_replace('_', '', $property));
                if (method_exists($this, $setterMethod)) {
                    $this->$setterMethod($value);
                } else {
                    $this->$property = $value;
                }
            }
        }
    }

    // delete
    public function delete()
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $sql = "DELETE FROM " . $tableName . " WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $this->getId()];
        $result = $db->query($options);
        return $result;
    }

    // save
    public function save()
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $sql = "INSERT INTO " . $tableName . " (";
        $sql .= implode(',', array_keys($this->getProperties()));
        $sql .= ") VALUES (";
        $sql .= implode(',', array_map(fn($key) => ':' . $key, array_keys($this->getProperties())));
        $sql .= ")";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = $this->getProperties();
        $db->query($options);
        $this->setId($db->lastInsertId());
        return $this->getId();
    }

    // update
    public function update()
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $sql = "UPDATE " . $tableName . " SET ";
        $sql .= implode(',', array_map(fn($key) => $key . ' = :' . $key, array_keys($this->getProperties())));
        $sql .= " WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = $this->getProperties();
        $db->query($options);
        return $this;
    }

    // generate Form
    public function getTableName()
    {
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        return $tableName;
    }

    public function getFields()
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);

        $sql = "SHOW COLUMNS FROM " . $tableName;
        $options = [];
        $options['query'] = $sql;

        $result = $db->query($options);
        // convert to array
        $result = json_decode(json_encode($result), true);
        $array = [];
        $not_allowed = ['id', 'created_at', 'updated_at', 'nr_pratica'];
        foreach ($result as $key => $value) {
            if (in_array($value['Field'], $not_allowed)) {
                continue; // skip the id field
            }

            if (strpos($value['Field'], 'id_') !== false) {
                $relatedTable = str_replace('id_', '', $value['Field']);  // e.g. "other_table"
                $sql = "SELECT * FROM " . self::getPluralName(ucfirst($relatedTable));
                $options = [];
                $options['query'] = $sql;
                $relatedRecords = $db->query($options);
                $options = [];
                foreach ($relatedRecords as $record) {
                    $options[$record->id] = $record->nome;  // Assuming 'name' is a suitable display field
                }
                $array[$value['Field']] = [
                    'type' => 'select',
                    'options' => $options
                ];

                continue;
            }

            if (preg_match("/^enum\(\'(.*)\'\)$/", $value['Type'], $matches)) {
                $enum_values = explode("','", $matches[1]);
                $array[$value['Field']] = [
                    'type' => 'select',
                    'options' => $enum_values
                ];
            } elseif (strpos($value['Type'], 'int') !== false) {
                $array[$value['Field']] = ['type' => 'number'];
            } elseif (strpos($value['Type'], 'varchar') !== false) {
                $array[$value['Field']] = ['type' => 'text'];
            } elseif (strpos($value['Type'], 'text') !== false) {
                $array[$value['Field']] = ['type' => 'textarea'];
            } elseif (strpos($value['Type'], 'date') !== false) {
                $array[$value['Field']] = ['type' => 'date'];
            }
        }
        return $array;
    }


    /**
     * Metodo getAll():
     *
     * Questo metodo accetta un array associativo di argomenti per personalizzare la query SQL utilizzata per ottenere i dati dal database.
     *
     * Argomenti che il metodo può accettare:
     *
     * 'limit' => Numero di record da restituire.
     *     Tipo: Int
     *     Default: Non definito
     *
     * 'currentPage' => Numero della pagina corrente per la paginazione dei risultati.
     *     Tipo: Int
     *     Default: Non definito
     *
     * 'order' => Direzione dell'ordinamento ('ASC' o 'DESC').
     *     Tipo: String
     *     Default: Non definito
     *
     * 'sort' => Campo da utilizzare per l'ordinamento.
     *     Tipo: String
     *     Default: Non definito
     *
     * 'where' => Un array associativo utilizzato per definire le condizioni WHERE della query. Ogni chiave dell'array è il nome del campo
     *            e il valore è un altro array associativo con due chiavi: 'operator' e 'value'. 'operator' è l'operatore da utilizzare
     *            per il confronto (ad es., '=', '!=', '<', '>', '<=', '>=', 'IN', 'NOT IN') e 'value' è il valore da confrontare
     *            (o un array di valori nel caso degli operatori 'IN' e 'NOT IN').
     *     Tipo: Array
     *     Default: Non definito
     *
     * Esempio di uso:
     *
     * $utenti = Utente::getAll([
     *     'where' => [
     *         'is_deleted' => [
     *             'operator' => '!=',
     *             'value' => 1
     *         ],
     *         'id_ruolo' => [
     *             'operator' => 'IN',
     *             'value' => [1, 2, 6]
     *         ]
     *     ]
     * ]);
     *
     *
     * * Esempio 1: Ottieni tutti i record
     *
     * $tutti_i_record = NomeClasse::getAll();

     * Esempio 2: Ottieni i primi 10 record
     *
     * $primi_dieci_record = NomeClasse::getAll(['limit' => 10]);

     * Esempio 3: Ottieni i record dalla pagina 2, 10 record per pagina
     *
     * $record_pagina_2 = NomeClasse::getAll(['limit' => 10, 'currentPage' => 2]);

     * Esempio 4: Ottieni tutti i record ordinati in base al campo 'nome' in ordine ascendente
     *
     * $record_ordinati = NomeClasse::getAll(['sort' => 'nome', 'order' => 'ASC']);

     * Esempio 5: Ottieni tutti i record con 'is_deleted' diverso da 1
     *
     * $record_non_eliminati = NomeClasse::getAll([
     *     'where' => [
     *         'is_deleted' => [
     *             'operator' => '!=',
     *             'value' => 1
     *         ]
     *     ]
     * ]);

     * Esempio 6: Ottieni tutti i record con 'id' in un certo insieme di valori
     *
     * $record_selezionati = NomeClasse::getAll([
     *     'where' => [
     *         'id' => [
     *             'value' => [1, 2, 3, 4, 5],
     *             'operator' => 'IN'
     *         ]
     *     ]
     * ]);
     *
     * Nel codice di esempio sopra, il metodo getAll() restituirà tutti gli utenti che non sono marcati come eliminati e il cui 'id_ruolo'
     * è uno tra 1, 2 e 6.
     *
     * Assicurarsi di utilizzare l'operatore corretto in base al tipo e al numero dei valori che si stanno passando.
     */

    public static function getAll(array $args = [])
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);

        if (isset($args['query']) && !empty($args['query'])) {
            $sql = $args['query'];
        } else {
            $sql = "SELECT * FROM " . $tableName;
        }

        // Aggiunge le clausole JOIN se presenti
        // Check for join conditions
        if (isset($args['joins'])) {
            foreach ($args['joins'] as $join) {
                $sql .= " " . $join['type'] . " " . $join['table'] . " ON " . $join['condition'];
            }
        }

        $options = [];

        // Set options for limit, offset, order and sort
        if (isset($args['limit'])) {
            $options['limit'] = $args['limit'];
        }

        if (isset($args['currentPage'])) {
            $options['offset'] = ($args['currentPage'] - 1) * $options['limit'];
        }

        if (isset($args['order_by'])) {
            $options['order_by'] = $args['order_by'];
        }

        if (isset($args['order'])) {
            $options['order_dir'] = $args['order'];
        }

        if (!empty($args['where'])) {
            $sql .= " WHERE ";
            $whereClauses = [];
            foreach ($args['where'] as $field => $values) {
                if (isset($values['value']) && isset($values['operator'])) {
                    $operator = $values['operator'];
                    $value = $values['value'];
                    if (is_array($value)) {
                        $placeholders = implode(',', array_fill(0, count($value), '?'));
                        $whereClauses[] = "$tableName.$field $operator ($placeholders)";
                        foreach ($value as $val) {
                            $options['params'][] = $val;
                        }
                    } else {
                        $whereClauses[] = "$tableName.$field $operator ?";
                        $options['params'][] = $value;
                    }
                }
            }
            $sql .= implode(' AND ', $whereClauses);
        }

        $options['query'] = $sql;



        // return instance of the class
        $result = $db->query($options);
        $array = [];
        foreach ($result as $record) {
            $array[] = new $className($record->id);
        }
        return $array;
    }

    // count
    /*public static function getTotalCount($args = [])
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);

        $sql = "SELECT COUNT(*) FROM " . $tableName;

        // if there are args add where clause
        if (isset($args['where']['id_ruolo']) && is_array($args['where']['id_ruolo'])) {
            $ruoli = implode(',', array_map('intval', $args['where']['id_ruolo']));
            $sql .= " WHERE $tableName.id_ruolo IN ($ruoli)";
        }

        $options = [];
        $options['query'] = $sql;

        $result = $db->query($options);
        return $result[0]->{'COUNT(*)'};
    }*/

    /**
     * Metodo getTotalCount():
     *
     * Questo metodo accetta un array di argomenti per definire la query SQL per ottenere il conteggio totale delle righe dal database.
     *
     * Gli argomenti sono:
     *
     * 'where' => Questa è un'opzione per aggiungere una clausola WHERE alla query SQL. Deve essere un array associativo,
     *            dove la chiave è il nome del campo nel database e il valore è un array associativo con 'value' e 'operator'.
     *
     * 'value' => Il valore da confrontare con il campo nel database. Può essere una stringa o un array.
     *
     * 'operator' => Questo è l'operatore da utilizzare nella clausola WHERE. Può essere uno dei seguenti:
     *               '=', '!=', '<', '>', '<=', '>=', 'IN', 'NOT IN'.
     *               Tieni presente che '!=' e 'NOT IN' sono per le disuguaglianze.
     *               L'operatore di disuguaglianza '!=' accetta un singolo valore, mentre 'NOT IN' può accettare un array di valori.
     *
     * Esempi di uso:
     *
     * // Ottieni il conteggio totale delle pratiche non eliminate
     * $conteggio = Pratica::getTotalCount([
     *     'where' => [
     *         'is_deleted' => ['value' => 0, 'operator' => '=']
     *     ]
     * ]);
     *
     * // Ottieni il conteggio totale delle pratiche che non sono state eliminate e il cui gruppo è 21 o 22
     * $conteggio = Pratica::getTotalCount([
     *     'where' => [
     *         'is_deleted' => ['value' => 0, 'operator' => '='],
     *         'id_gruppo' => ['value' => [21, 22], 'operator' => 'IN']
     *     ]
     * ]);
     *
     * Assicurati di utilizzare l'operatore corretto in base al tipo e al numero dei valori che stai passando.
     */
    public static function getTotalCount($args = [])
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);

        $sql = "SELECT COUNT(*) FROM " . $tableName;

        $options = [];

        if (isset($args['where']) && is_array($args['where'])) {
            $sql .= " WHERE ";
            $whereClauses = [];

            foreach ($args['where'] as $column => $condition) {
                $operator = $condition['operator'] ?? '=';
                $value = $condition['value'];

                if (is_array($value)) {
                    $placeholders = implode(',', array_fill(0, count($value), '?'));
                    $whereClauses[] = "$tableName.$column IN ($placeholders)";
                    foreach ($value as $v) {
                        $options['params'][] = $v;
                    }
                } else {
                    $whereClauses[] = "$tableName.$column $operator ?";
                    $options['params'][] = $value;
                }
            }

            $sql .= implode(' AND ', $whereClauses);
        }

        $options['query'] = $sql;

        $result = $db->query($options);
        return $result[0]->{'COUNT(*)'};
    }

    // delete by id
    public static function deleteById($id)
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $sql = "DELETE FROM " . $tableName . " WHERE id_" . $tableName . " = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id];

        $result = $db->query($options);

        return $result;
    }

    // to Array
    public static function getPluralName(string $shortClassName)
    {
        // in Italiano
        if (substr($shortClassName, -2) === 'ca') {
            return substr($shortClassName, 0, -2) . 'che';
        } elseif (substr($shortClassName, -1) === 'o') {
            return substr($shortClassName, 0, -1) . 'i';
        } elseif (substr($shortClassName, -1) === 'e') {
            return substr($shortClassName, 0, -1) . 'i';
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

    // get by id
    public static function getById($id)
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $singularLower = strtolower($shortClassName);
        $sql = "SELECT * FROM " . $tableName . " WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $id];

        $result = $db->query($options);

        if (isset($result[0])) {
            return new $className($result[0]->{'id'});
        } else {
            return null;
        }
    }

    // getByPropertyName
    public static function getByPropertyName($property, $value)
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $singularLower = strtolower($shortClassName);
        $sql = "SELECT * FROM " . $tableName . " WHERE " . $property . " = :" . $property;
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':' . $property => $value];

        $result = $db->query($options);

        if (isset($result[0])) {
            $id = 'id_' . $singularLower;
            $id_utente = $result[0]->{'id'};
            return new $className($id_utente);
        } else {
            return null;
        }
    }

    public function toArray()
    {
        $array = get_object_vars($this);
        if (array_key_exists('id_utente', $array)) {
            $array['id'] = $array['id_utente'];
            unset($array['id_utente']);
        }
        return $array;
    }

    private function getProperties()
    {
        $array = get_object_vars($this);
        /*if (array_key_exists('id_utente', $array)) {
            $array['id'] = $array['id_utente'];
            unset($array['id_utente']);
        }*/
        return $array;
    }

    // setProperties
    public function setFieldIfExistInPost($field)
    {
        $setField = str_replace('_', '', $field);
        $setter = 'set' . ucfirst($setField);
        $data = $this->sanificaInput($_POST);
        if (isset($_POST[$field])) {
            $this->$setter($data[$field]);
        }
    }

    public function sanificaInput(array|null $dati = null, array $ignore_keys = [])
    {
        if (is_null($dati)) {
            return [];
        }
        $sanitized = [];
        foreach ($dati as $key => $value) {
            if (in_array($key, $ignore_keys)) {
                $sanitized[$key] = $value;
            } else if (is_array($value)) {
                $sanitized[$key] = $this->sanificaInput($value);
            } else {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }
        return $sanitized;
    }

}