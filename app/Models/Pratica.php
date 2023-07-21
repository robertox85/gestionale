<?php

namespace App\Models;

use App\Libraries\Database;

class Pratica extends BaseModel
{

    protected $id;
    protected ?string $nr_pratica;
    protected ?string $nome;
    protected ?string $tipologia;
    protected ?string $stato;

    protected ?string $competenza;
    protected ?string $ruolo_generale;
    protected ?string $giudice;
    protected ?int $id_gruppo;

    protected int $is_deleted = 0;

    // getter and setter
    private $created_at;
    private $updated_at;


    // getter and setter
    public function getId() { return $this->id; }
    public function setId($id): void { $this->id = $id; }
    public function getNrPratica(): ?string { return $this->nr_pratica;}
    public function setNrPratica($nr_pratica): void { $this->nr_pratica = $nr_pratica; }
    public function getNome(): ?string { return $this->nome; }
    public function setNome($nome): void { $this->nome = $nome; }
    public function getTipologia(): ?string { return $this->tipologia; }
    public function setTipologia($tipologia): void { $this->tipologia = $tipologia;}
    public function getStato(): ?string { return $this->stato; }
    public function setStato($stato): void { $this->stato = $stato; }
    public function getCompetenza(): ?string { return $this->competenza; }
    public function setCompetenza($competenza): void { $this->competenza = $competenza; }
    public function getRuoloGenerale(): ?string { return $this->ruolo_generale; }
    public function setRuoloGenerale($ruolo_generale): void { $this->ruolo_generale = $ruolo_generale; }
    public function getGiudice(): ?string { return $this->giudice; }
    public function setGiudice($giudice): void { $this->giudice = $giudice; }
    public function getIdGruppo(): ?int { return $this->id_gruppo; }
    public function setIdGruppo($id_gruppo): void { $this->id_gruppo = $id_gruppo; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function setCreatedAt($created_at): void { $this->created_at = $created_at; }
    public function setUpdatedAt($updated_at): void { $this->updated_at = $updated_at; }
    public function getGruppoObj(): ?Gruppo { return new Gruppo($this->getIdGruppo()) ?? null; }


    // Actions
    public function deleteNote()
    {
        $db = Database::getInstance();
        // Delete note related to this practice
        $sql = "DELETE FROM Note WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function deleteUdienze()
    {
        $db = Database::getInstance();
        // Delete udienze related to this practice
        $sql = "DELETE FROM Udienze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function deleteScadenze()
    {
        $db = Database::getInstance();
        // Delete scadenze related to this practice
        $sql = "DELETE FROM Scadenze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function clearScadenze()
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Scadenze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function clearUdienze()
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Udienze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function clearNote()
    {
        $db = Database::getInstance();
        $sql = "DELETE FROM Note WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function getUdienze()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Udienze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function getScadenze()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Scadenze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    public function getNote()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Note WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }
    // Static methods
    /*public static function generateNrPratica($groupId)
    {
        $db = Database::getInstance();

        // Nota: assumiamo che 'nr_pratica' sia una stringa e che la struttura sia 'P####GA', 'P####GB', ecc.
        $sql = "SELECT
                MAX(CAST(SUBSTRING(nr_pratica, 2, 4) AS UNSIGNED)) AS max_nr_pratica,
                LEFT(G.nome, 1) AS first_letter
            FROM
                Pratiche P
            LEFT JOIN
                Gruppi G ON P.id_gruppo = G.id
            WHERE
                P.id_gruppo = :id_gruppo";

        $options = [
            'query' => $sql,
            'params' => [':id_gruppo' => $groupId]
        ];
        $result = $db->query($options);
        $maxNrPratica = $result[0]->max_nr_pratica ?? 0;
        $firstLetter = $result[0]->first_letter ?? '';
        $nextNrPratica = $maxNrPratica + 1;

        // Restituisce una stringa formattata con la struttura 'P####G[lettera]'
        return sprintf('P%04dG%s', $nextNrPratica, $firstLetter);
    }*/

    public static function generateNrPratica($groupId)
    {
        $db = Database::getInstance();

        $sql = "SELECT
            MAX(CAST(SUBSTRING(nr_pratica, 2, 4) AS UNSIGNED)) AS max_nr_pratica,
            LEFT(G.nome, 1) AS first_letter 
        FROM 
            Pratiche P 
        LEFT JOIN 
            Gruppi G ON P.id_gruppo = G.id 
        WHERE 
            P.id_gruppo = :id_gruppo
            AND P.is_deleted = 0";

        $options = [
            'query' => $sql,
            'params' => [':id_gruppo' => $groupId]
        ];
        $result = $db->query($options);
        $maxNrPratica = $result[0]->max_nr_pratica ?? 0;
        $firstLetter = $result[0]->first_letter ?? '';
        // Se non ci sono risultati per il gruppo specificato, esegui un'altra query
        // per ottenere la prima lettera del nome del gruppo.
        if ($firstLetter === NULL|| $firstLetter === '') {
            $sql = "SELECT LEFT(G.nome, 1) AS first_letter 
                FROM Gruppi G 
                WHERE G.id = :id_gruppo";
            $options = [
                'query' => $sql,
                'params' => [':id_gruppo' => $groupId]
            ];
            $result = $db->query($options);
            $firstLetter = $result[0]->first_letter ?? '';
        }
        $nextNrPratica = $maxNrPratica + 1;

        // Restituisce una stringa formattata con la struttura 'P####G[lettera]'
        return sprintf('P%04dG%s', $nextNrPratica, $firstLetter);
    }


    public static function getAllPratiche(array $args = [])
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);
        $options = [];
        // Join with Gruppi but distinguish between 'id_gruppo' and Pratiche.'id' (Gruppi.id)
        $sql = "SELECT Pratiche.*, Gruppi.nome AS gruppo, Utenti.username AS utente,";
        $sql .= " Anagrafiche.nome AS nome_anagrafica, Anagrafiche.cognome AS cognome_anagrafica,";
        $sql .= " Anagrafiche.denominazione AS denominazione_anagrafica";
        $sql .= " FROM Pratiche";
        $sql .= " LEFT JOIN Gruppi ON Pratiche.id_gruppo = Gruppi.id";
        $sql .= " LEFT JOIN Utenti_Gruppi ON Pratiche.id_gruppo = Utenti_Gruppi.id_gruppo";
        $sql .= " LEFT JOIN Utenti ON Utenti_Gruppi.id_utente = Utenti.id";
        $sql .= " LEFT JOIN Anagrafiche ON Anagrafiche.id_utente = Utenti.id";
        $sql .= " WHERE Pratiche.is_deleted = false";

        /*$sql = "SELECT
                    Pratiche.*,
                    Gruppi.nome AS gruppo
                FROM 
                    $tableName Pratiche
                LEFT JOIN 
                    Gruppi ON Pratiche.id_gruppo = Gruppi.id";

        // EXCLUDE DELETED PRACTICES
        $sql .= " WHERE Pratiche.is_deleted = false";*/


        if (!empty($args)) {
            $options['limit'] = $args['limit'];
            $options['offset'] = ($args['currentPage'] - 1) * $args['limit'];
            $options['order_dir'] = $args['order'] ?? 'ASC';
            if ($args['order_by'] == 'id') {
                $options['order_by'] = "Pratiche.id";
            } elseif ($args['order_by'] == 'gruppo') {
                $options['order_by'] = "Gruppi.nome";
            } else {
                $options['order_by'] = "Pratiche." . $args['order_by'];
            }
        }


        if (!empty($args['limit'])) {
            $options['limit'] = $args['limit'];
        }

        if (!empty($args['currentPage'])) {
            $options['offset'] = ($args['currentPage'] - 1) * $args['limit'];
        }

        if (!empty($args['order'])) {
            $options['order_dir'] = $args['order'];
        }

        if (!empty($args['sort'])) {
            if ($args['sort'] == 'id') {
                $options['order_by'] = "Pratiche.id";
            } elseif ($args['sort'] == 'gruppo') {
                $options['order_by'] = "Gruppi.nome";
            } else {
                $options['order_by'] = "Pratiche." . $args['sort'];
            }
        }

        if(isset($args['search']) && !empty($args['search'])) {
            $sql .= " AND(Pratiche.nr_pratica LIKE ?";
            $sql .= " OR Pratiche.nome LIKE ?";
            $sql .= " OR Pratiche.giudice LIKE ?";
            $sql .= " OR Gruppi.nome LIKE ?";
            $sql .= " OR Utenti.username LIKE ?";
            $sql .= " OR Utenti.email LIKE ?";
            $sql .= " OR Anagrafiche.nome LIKE ?";
            $sql .= " OR Anagrafiche.cognome LIKE ?";
            $sql .= " OR Anagrafiche.denominazione LIKE ?)";
            $searchTerm = "%{$args['search']}%"; // la percentuale è usata per la ricerca parziale in SQL
            $params = array_fill(0, 9, $searchTerm);
            $options['params'] = $params;
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

    // delete
    public function delete()
    {
        $db = Database::getInstance();
        $sql = "UPDATE Pratiche SET is_deleted = true, nr_pratica = 'DELETED' WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $this->getId()];
        $db->query($options);
    }

    public function restore()
    {
        $db = Database::getInstance();
        // Set is_deleted to false and restore the original nr_pratica
        $sql = "UPDATE Pratiche SET is_deleted = false, nr_pratica = :nr_pratica WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $this->getId()];
        $options['params'][':nr_pratica'] = $this->getNrPratica();
        $db->query($options);
    }
}