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

    // getter and setter
    private $created_at;
    private $updated_at;


    // getter and setter
    public function getId()
    {
        return $this->id;
    }
    public function setId($id): void
    {
        $this->id = $id;
    }
    public function getNrPratica(): ?string
    {
        return $this->nr_pratica;
    }
    public function setNrPratica($nr_pratica): void
    {
        $this->nr_pratica = $nr_pratica;
    }
    public function getNome(): ?string
    {
        return $this->nome;
    }
    public function setNome($nome): void
    {
        $this->nome = $nome;
    }
    public function getTipologia(): ?string
    {
        return $this->tipologia;
    }
    public function setTipologia($tipologia): void
    {
        $this->tipologia = $tipologia;
    }
    public function getStato(): ?string
    {
        return $this->stato;
    }
    public function setStato($stato): void
    {
        $this->stato = $stato;
    }
    public function getCompetenza()
    {
        return $this->competenza;
    }
    public function setCompetenza($competenza)
    {
        $this->competenza = $competenza;
    }
    public function getRuoloGenerale()
    {
        return $this->ruolo_generale;
    }
    public function setRuoloGenerale($ruolo_generale)
    {
        $this->ruolo_generale = $ruolo_generale;
    }
    public function getGiudice()
    {
        return $this->giudice;
    }
    public function setGiudice($giudice)
    {
        $this->giudice = $giudice;
    }
    public function getIdGruppo()
    {
        return $this->id_gruppo;
    }
    public function setIdGruppo($id_gruppo)
    {
        $this->id_gruppo = $id_gruppo;
    }
    public function getCreatedAt()
    {
        return $this->created_at;
    }
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }


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
    public function getGruppoObj(): ?Gruppo
    {
        return new Gruppo($this->getIdGruppo()) ?? null;
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

    public function getUdienze() {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Udienze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }
    public function getScadenze() {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Scadenze WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }
    public function getNote(){
        $db = Database::getInstance();
        $sql = "SELECT * FROM Note WHERE id_pratica = :id_pratica";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_pratica' => $this->getId()];
        return $db->query($options);
    }

    // Static methods
    public static function generateNrPratica($groupId)
    {
        $db = Database::getInstance();

        $sql = "SELECT MAX(CAST(nr_pratica AS UNSIGNED)) AS max_nr_pratica, LEFT(G.nome, 1) AS first_letter 
            FROM Pratiche P 
            LEFT JOIN Gruppi G ON P.id_gruppo = G.id 
            WHERE P.id_gruppo = :id_gruppo";

        $options = [
            'query' => $sql,
            'params' => [':id_gruppo' => $groupId]
        ];
        $result = $db->query($options);
        $maxNrPratica = $result[0]->max_nr_pratica ?? 0;
        $firstLetter = $result[0]->first_letter ?? '';
        $nextNrPratica = $maxNrPratica + 1;
        return 'P'.sprintf('%04d', $nextNrPratica).'G'.$firstLetter;
    }
    public static function getAllPratiche(array $args = [])
    {
        $db = Database::getInstance();
        $className = static::class;
        $shortClassName = (new \ReflectionClass($className))->getShortName();
        $tableName = self::getPluralName($shortClassName);

        $sql = "SELECT * FROM " . $tableName;

        $options = [];

        if (!empty($args)) {
            $options['limit'] = $args['limit'];
            $options['offset'] = ($args['currentPage'] - 1) * $args['limit'];
            $options['order_dir'] = $args['order'] ?? 'ASC';
            if ($args['sort'] == 'id') {
                $options['order_by'] = "Pratiche.id";
            } elseif($args['sort'] == 'gruppo') {
                $options['order_by'] = "Gruppi.nome";
            } else {
                $options['order_by'] = "Pratiche." . $args['sort'];
            }
        }

        if(!empty($args['limit'])) {
            $options['limit'] = $args['limit'];
        }

        if(!empty($args['currentPage'])) {
            $options['offset'] = ($args['currentPage'] - 1) * $args['limit'];
        }

        if(!empty($args['order'])) {
            $options['order_dir'] = $args['order'];
        }

        if(!empty($args['sort'])) {
            if ($args['sort'] == 'id') {
                $options['order_by'] = "Pratiche.id";
            } elseif($args['sort'] == 'gruppo') {
                $options['order_by'] = "Gruppi.nome";
            } else {
                $options['order_by'] = "Pratiche." . $args['sort'];
            }
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
}