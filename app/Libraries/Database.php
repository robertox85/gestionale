<?php

namespace App\Libraries;

use Exception;
use PDO;
use PDOException;



class Database
{
    private static ?Database $instance = null;
    private ?PDO $db = null;

    private string $db_host;
    private string $db_user;
    private string $db_password;
    private string $db_name;


    public function __construct($db_host = null, $db_user = null, $db_password = null, $db_name = null)
    {
        $this->db_host = $db_host ?? $_ENV['DB_HOST'];
        $this->db_user = $db_user ?? $_ENV['DB_USERNAME'];
        $this->db_password = $db_password ?? $_ENV['DB_PASSWORD'];
        $this->db_name = $db_name ?? $_ENV['DB_DATABASE'];

        try {
            $this->db = new PDO("mysql:host={$this->db_host};dbname={$this->db_name}", $this->db_user, $this->db_password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public static function beginTransaction()
    {
        self::getInstance()->db->beginTransaction();
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public static function rollBack()
    {
        self::getInstance()->db->rollBack();
    }

    public static function commit()
    {
        self::getInstance()->db->commit();
    }

    public function getConnection(): PDO
    {
        return $this->db;
    }


    public function __destruct()
    {
        $this->db = null;
    }


    public function query(array $options): array|bool|int|null|object
    {
        // opzioni predefinite
        $default_options = [
            'query' => '',
            'params' => [],
            'limit' => null,
            'offset' => null,
            'order_by' => null,
            'order_dir' => 'ASC',
            'where' => '1',
        ];

        // unisce le opzioni predefinite con quelle passate dall'utente
        $options = array_merge($default_options, $options);

        // costruisce la query
        $sql = $options['query'];

        /*if ($options['where'] !== null && is_array($options['where'])) {
            // $options['where'] come array associativo
            $whereConditions = [];
            foreach ($options['where'] as $key => $value) {
                $whereConditions[] = "{$key} = '{$value}'";
            }
            $whereClause = implode(' AND ', $whereConditions);
            $sql .= " WHERE {$whereClause}";
        }*/

        if ($options['where'] !== null && is_array($options['where'])) {
            // $options['where'] as associative array
            $whereConditions = [];
            foreach ($options['where'] as $key => $value) {
                if(is_array($value)) {
                    // If the value is an array, we assume the first element is the operator
                    $operator = $value[0];
                    $actualValue = $value[1];
                } else {
                    // If it's not an array, we assume the operator is =
                    $operator = '=';
                    $actualValue = $value;
                }
                $whereConditions[] = "{$key} {$operator} '{$actualValue}'";
            }
            $whereClause = implode(' AND ', $whereConditions);
            $sql .= " WHERE {$whereClause}";
        }



        if ($options['order_by'] !== null) {
            $sql .= " ORDER BY {$options['order_by']} {$options['order_dir']}";
        }

        if ($options['limit'] !== null) {
            $sql .= " LIMIT {$options['limit']}";
        }

        if ($options['offset'] !== null) {
            $sql .= " OFFSET {$options['offset']}";
        }

        try {
            if ($this->db === null) {
                $errorHandler = ErrorHandler::getInstance();
                $errorHandler->handleException(new Exception('Database not found'));
                //throw new Exception('Database not found');
                echo 'Database not found';
                exit;
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute($options['params']);

                // se la query è una SELECT restituisce un array di oggetti. Se seleziona un solo record restituisce un oggetto
                if (explode(' ', $sql)[0] == 'SELECT') {
                    if ($options['limit'] == 1) {
                        return $stmt->fetch(PDO::FETCH_OBJ);
                    }
                    return $stmt->fetchAll(PDO::FETCH_OBJ);
                }

                // se la query è una INSERT restituisce l'id dell'ultimo record inserito
                if (explode(' ', $sql)[0] == 'INSERT') {
                    return $this->db->lastInsertId();
                }

                // se la query è una UPDATE o DELETE restituisce il numero di righe modificate
                if (explode(' ', $sql)[0] == 'UPDATE' || explode(' ', $sql)[0] == 'DELETE') {
                    return $stmt->rowCount();
                }

                return $stmt->fetchAll(PDO::FETCH_OBJ);
            }
            // restituisce il risultato della query come un array di oggetti

        } catch (PDOException $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handleException($e);
        } catch (\Exception $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handleException($e);
        }

        return false;

    }

    public function getLastInsertId(): int
    {
        return $this->db->lastInsertId();
    }

    public function createAdmin(): int|false
    {
        try {
            $this->db->exec("USE {$this->db_name}");

            // DELETE FROM codici_backup WHERE user_id IN (SELECT id FROM utenti);
            $this->db->exec("DELETE FROM codici_backup WHERE user_id IN (SELECT id FROM utenti)");

            $this->db->exec("DELETE FROM utenti");

            return $this->query([
                'query' => "INSERT INTO utenti (username, email, password, role, status) VALUES (:username, :email, :password, :role, :status)",
                'params' => [
                    'username' => 'admin',
                    'email' => 'admin@admin.com',
                    'password' => password_hash('admin', PASSWORD_DEFAULT),

                    'role' => 'admin',
                    'status' => 'active'
                ]
            ]);
        } catch (PDOException $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handleException($e);
        } catch (\Exception $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handleException($e);
        }

        return false;
    }

    public function setup($action): int|false
    {
        if ($action == 'create_admin') {
            // insert admin user
            return $this->createAdmin();
        }

        if ($action == 'drop') {
            // drop database
            $this->db->exec("DROP DATABASE IF EXISTS {$this->db_name}");
            return true;
        }

        // create database
        if ($action == 'create_db') {
            #$this->db->exec("DROP DATABASE IF EXISTS {$this->db_name}");
            try {
                $this->db->exec("CREATE DATABASE {$this->db_name} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                return true;
            } catch (PDOException $e) {
                $errorHandler = ErrorHandler::getInstance();
                $errorHandler->handleException($e);
            } catch (\Exception $e) {
                $errorHandler = ErrorHandler::getInstance();
                $errorHandler->handleException($e);
            }
        }

        // create tables
        if ($action == 'create_tables') {
            $this->db->exec("DROP DATABASE IF EXISTS {$this->db_name}");
            $this->db->exec("CREATE DATABASE {$this->db_name} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->db->exec("USE {$this->db_name}");
            $directory = (dirname(__DIR__, 2));
            $file = $directory . "/src/Database/{$action}.sql";
            $sql = file_get_contents($file);
            try {
                $result = $this->db->exec($sql);

                if ($result === false) {
                    $error = $this->db->errorInfo();
                    $errorHandler = ErrorHandler::getInstance();
                    //$errorHandler->handleGenericError(new Exception($error[2]));
                    $errorHandler->handleException(new Exception($error[2]));
                }

                $this->db->exec("USE {$this->db_name}");

                return true;
            } catch (PDOException $e) {
                $errorHandler = ErrorHandler::getInstance();
                $errorHandler->handleException($e);
            } catch (\Exception $e) {
                $errorHandler = ErrorHandler::getInstance();
                $errorHandler->handleException($e);
            }
        }


        return false;
    }

    public function count(string $TABLE_NAME)
    {
        try {
            $this->db->exec("USE {$this->db_name}");
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$TABLE_NAME}");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handleException($e);
        } catch (\Exception $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handleException($e);
        }
    }

    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
}