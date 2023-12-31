<?php

namespace App\Libraries;

use PhpParser\Node\Expr\Cast\Bool_;

/**
 * QueryBuilder is a SQL query builder class that provides a fluent interface for constructing SQL queries.
 */
class QueryBuilder
{

    // Protected members holding parts of SQL query
    protected Database $db;
    protected mixed $table;
    protected array $selectColumns = [];
    protected array $joins = [];
    protected array $whereClauses = [];
    protected array $orders = [];
    protected array $parameters = [];
    protected array $insertValues = [];
    protected array $updateValues = [];
    protected array $groupBy = [];
    protected array $havingClauses = [];
    protected $limit = 10;
    protected $offset = null;
    private int $currentPage = 1;
    private int $totalItems = 0;


    /**
     * QueryBuilder constructor.
     * @param $db Database connection
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function paginate()
    {
        $this->totalItems = $this->count();

        // if GET is empty, return
        if (empty($_GET)) return $this;

        $orderByColumn = $_GET['sort'] ?? 'created_at';
        $orderByDirection = $_GET['order'] ?? 'ASC';
        $limit = $_GET['limit'] ?? $this->limit;
        $page = $_GET['page'] ?? $this->currentPage;
        $this->currentPage = max(1, (int)$page);
        $this->limit($limit);
        $this->offset(($this->currentPage - 1) * $this->limit);
        $this->orderBy($orderByColumn, $orderByDirection);

        return $this;
    }

    /**
     * Set table for the SQL operation
     * @param string $table name
     * @return $this
     */
    public function setTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get table name
     * @return mixed Table name
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set an alias for a column in the query result
     * @param string $column Column name to set the alias for
     * @param string $alias Alias name
     * @return $this
     */
    public function setAlias(string $column, string $alias): static
    {
        $this->selectColumns[] = "$column AS $alias";
        return $this;
    }

    /**
     * Select columns
     * @param string $columns Columns to select
     * @return $this
     */
    public function select(string $columns = '*')
    {
        if (is_array($columns)) {
            $this->selectColumns = $columns;
        } else {
            $this->selectColumns = explode(', ', $columns);
        }
        return $this;
    }


    /**
     * Add a join clause
     * @param $table Table name
     * @param $condition Join condition
     * @param string $type Join type
     * @return $this
     */
    public function join($table, $condition, $type = 'INNER')
    {
        $this->joins[] = "$type JOIN $table ON $condition";
        return $this;
    }

    /**
     * Add a left join clause
     * @param $table Table name
     * @param $condition Join condition
     * @return $this
     */
    public function leftJoin($table, $condition): static
    {
        $this->joins[] = "LEFT JOIN $table ON $condition";
        return $this;
    }

    /**
     * Add a right join clause
     * @param $table Table name
     * @param $condition Join condition
     * @return $this
     */
    public function rightJoin($table, $condition)
    {
        $this->joins[] = "RIGHT JOIN $table ON $condition";
        return $this;
    }

    /**
     * Add a where clause
     * @param string $column name
     * @param string $value value
     * @param string $operator Comparison operator
     * @param string $logicalOperator Logical operator
     * @return $this
     */
    public function where(string $column, string $value, string $operator = '=', string $logicalOperator = 'AND'): static
    {
        $this->whereClauses[] = [
            'clause' => "$column $operator ?",
            'param' => $value,
            'logicalOperator' => $logicalOperator
        ];
        // Aggiungi il parametro all'array dei parametri
        $this->parameters[] = $value;

        return $this;
    }

    /**
     * Add a where clause with IN operator
     * @param $string $column Column name
     * @param array $values Column values
     * @param string $logicalOperator Logical operator
     * @return $this
     */
    public function whereIn($column, array $values, $logicalOperator = 'AND')
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->whereClauses[] = [
            'clause' => "$column IN ($placeholders)",
            'params' => $values,
            'logicalOperator' => $logicalOperator
        ];

        foreach ($values as $value) {
            $this->parameters[] = $value;
        }
        return $this;
    }

    /**
     * Start a group of where clauses with AND operator
     * @param $column Column name
     * @param array $values Column values
     * @param string $logicalOperator Logical operator
     * @return $this
     */
    public function beginWhereGroup($logicalOperator = 'AND')
    {
        $this->whereClauses[] = [
            'clause' => '(',
            'logicalOperator' => $logicalOperator
        ];
        return $this;
    }

    /**
     * End a group of where clauses
     * @return $this
     */
    public function endWhereGroup()
    {
        $this->whereClauses[] = [
            'clause' => ')',
            'logicalOperator' => null
        ];
        return $this;
    }

    /**
     * Add a group by clause
     * @param $column Column name
     * @return $this
     */
    public function groupBy($column)
    {
        $this->groupBy[] = $column;
        return $this;
    }

    /**
     * Add a having clause
     * @param $column Column name
     * @param $value Column value
     * @param string $operator Comparison operator
     * @return $this
     */
    public function having($column, $value, $operator = '=')
    {
        $this->havingClauses[] = "$column $operator ?";
        $this->parameters[] = $value;
        return $this;
    }

    /**
     * Build parts of SQL query
     * @return string
     */
    protected function buildGroupBy()
    {
        if (empty($this->groupBy)) {
            return '';
        }
        return ' GROUP BY ' . implode(', ', $this->groupBy);
    }

    /**
     * Build parts of SQL query
     * @return string
     */
    protected function buildHaving()
    {
        if (empty($this->havingClauses)) {
            return '';
        }
        return ' HAVING ' . implode(' AND ', $this->havingClauses);
    }

    /**
     * Add an order by clause
     * @param $column Column name
     * @param string $direction Order direction
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orders[] = "$column $direction";
        return $this;
    }

    /**
     * Build parts of SQL query
     * @return string
     */
    protected function buildSelect()
    {
        return 'SELECT ' . implode(', ', $this->selectColumns);
    }

    /**
     * Build parts of SQL query
     * @return string
     */
    protected function buildJoins()
    {
        return implode(' ', $this->joins);
    }

    /**
     * Build parts of SQL query
     * @return string
     */
    public function buildWhere()
    {
        $whereString = '';
        if (!empty($this->whereClauses)) {
            $whereString .= ' WHERE ';
            $firstClause = true;
            foreach ($this->whereClauses as $index => $whereClause) {
                if ($whereClause['clause'] !== '(') {
                    if (!$firstClause && $this->whereClauses[$index - 1]['clause'] !== '(') {
                        $whereString .= ' ' . $whereClause['logicalOperator'];
                    }
                } else {
                    $whereString .= ' ' . $whereClause['logicalOperator'];
                }
                $whereString .= ' ' . $whereClause['clause'];
                $firstClause = false;
            }
        }
        return $whereString;
    }

    /**
     * Execute the query
     * @return \PDOStatement
     */
    public function executeQuery()
    {
        $sql = $this->toSql();
        $this->logQuery($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->getParameters());
        $this->reset();
        return $stmt;
    }

    /**
     * Build parts of SQL query
     * @return string
     */
    protected function buildOrderBy(): string
    {
        if (empty($this->orders)) {
            return ' ';
        }
        return ' ORDER BY ' . implode(', ', $this->orders);
    }

    /**
     * Limit the number of results
     * @return QueryBuilder
     */
    public function limit($limit): QueryBuilder
    {
        $this->limit = (int)$limit;
        return $this;
    }

    /**
     * Offset the results
     * @param int $offset
     * @return QueryBuilder
     */
    public function offset($offset): QueryBuilder
    {
        $this->offset = (int)$offset;
        return $this;
    }

    /**
     * Build parts of SQL query
     * @return string
     */
    protected function buildLimit(): string
    {
        $sql = '';
        if (!empty($this->limit)) {
            $sql = ' LIMIT ' . $this->limit;
        }
        if (!empty($this->offset)) {
            $sql .= ' OFFSET ' . $this->offset;
        }
        return $sql;
    }

    /**
     * Insert a row
     * @param array $values to insert
     * @return \PDOStatement
     */
    public function insert(array $values): array|\PDOStatement
    {
        $values = $this->filterValues($values);
        $placeholders = implode(", ", array_fill(0, count($values), "?"));
        $columns = implode(", ", array_keys($values));
        $sql = "INSERT INTO $this->table ($columns) VALUES ($placeholders)";
        $this->parameters = array_values($values);
        return $this->execute($sql);
    }

    /**
     * Update rows
     * @param array $values to update
     * @param array $conditions Conditions
     * @return int
     */
    public function update($values, $conditions = []): int
    {
        $values = $this->filterValues($values);
        $setClauses = [];
        $this->parameters = [];
        foreach ($values as $column => $value) {
            $setClauses[] = "$column = ?";
            $this->parameters[] = $value;
        }
        $setClause = implode(", ", $setClauses);

        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $this->where($column, $value);
            }
        }

        $sql = "UPDATE $this->table SET $setClause" . $this->buildWhere();

        foreach ($this->whereClauses as $whereClause) {
            if (isset($whereClause['param'])) {
                $this->parameters[] = $whereClause['param'];
            }
            if (isset($whereClause['params'])) {
                foreach ($whereClause['params'] as $param) {
                    $this->parameters[] = $param;
                }
            }
        }

        return $this->execute($sql)->rowCount();
    }


    /**
     * Delete rows
     * @return false
     */
    public function delete(): \PDOStatement|bool
    {
        $sql = "DELETE FROM $this->table" . $this->buildWhere();
        return $this->execute($sql);
    }

    /**
     * Begin a transaction
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit a transaction
     * @return bool
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollback(): bool
    {
        return $this->db->rollback();
    }

    /**
     * Log a query
     * @param $sql
     */
    public function logQuery($sql)
    {

    }

    private function filterValues(array $values): array
    {
        // Filter the input values to only include keys that match the table columns
        $filteredValues = array_intersect_key($values, array_flip($this->getColumns()));

        // Flatten any array values into comma-separated strings
        foreach ($filteredValues as $key => $value) {
            if (is_array($value)) {
                $filteredValues[$key] = implode(',', $value);
            }

            // handle DateTime objects
            if ($value instanceof \DateTime) {
                $filteredValues[$key] = $value->format('Y-m-d H:i:s');
            }

        }

        return $filteredValues;
    }

    /**
     * Count the number of rows
     * @return mixed
     */
    public function count(): mixed
    {
        $sql = "SELECT COUNT(*) FROM $this->table" . $this->buildWhere();
        $stmt = $this->execute($sql);
        return $stmt->fetchColumn();
    }

    /**
     * Get the SQL query string
     * @return string
     */
    public function toSql(): string
    {
        $sql = $this->buildSelect();
        $sql .= " FROM $this->table ";
        $sql .= $this->buildJoins();
        $sql .= $this->buildWhere();
        $sql .= $this->buildGroupBy();
        $sql .= $this->buildHaving();
        $sql .= $this->buildOrderBy();
        $sql .= $this->buildLimit();
        return $sql;
    }

    /**
     * Reset the query builder
     * @return $this
     */
    public function reset(): static
    {
        $this->selectColumns = [];
        $this->joins = [];
        $this->whereClauses = [];
        $this->orders = [];
        $this->parameters = [];
        $this->insertValues = [];
        $this->updateValues = [];
        $this->groupBy = [];
        $this->havingClauses = [];
        $this->limit = 10;
        $this->offset = null;
        return $this;
    }

    /**
     * Get the parameters
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Execute the query and return the result
     * @return array
     */
    public function get(): array
    {
        $this->paginate();
        $sql = $this->toSql();
        $stmt = $this->db->prepare($sql);
        $params = $this->getParameters();
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function first(): array
    {
        $this->limit(1);
        $sql = $this->toSql();
        $stmt = $this->db->prepare($sql);
        $params = $this->getParameters();
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Execute arbitrary SQL
     * @return \PDOStatement|false
     */
    public function execute($sql): \PDOStatement|false
    {
        $stmt = $this->db->prepare($sql);

        for ($i = 0; $i < count($this->parameters); $i++) {
            $stmt->bindParam($i + 1, $this->parameters[$i]);
        }

        $stmt->execute();
        $this->parameters = [];
        return $stmt;
    }

    /**
     * Get the columns of a table
     * @return array
     */
    public function getColumns(): array
    {
        $sql = "SHOW COLUMNS FROM $this->table";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $columns = [];
        foreach ($array as $column) {
            $columns[] = $column['Field'];
        }
        return $columns;
    }

    /**
     * Get the last inserted ID
     * @return string
     */
    public function getLastInsertedId(): string
    {
        return $this->db->lastInsertId();
    }


    public function getPagination()
    {
        return [
            'currentPage' => $this->currentPage,
            'itemsPerPage' => $this->limit,
            'totalItems' => $this->totalItems,
            'totalPages' => $this->limit > 0 ? ceil($this->totalItems / $this->limit) : 0,
            'startItem' => ($this->currentPage - 1) * $this->limit + 1,
            'endItem' => min($this->totalItems, $this->currentPage * $this->limit),
            'query' => $_GET,
        ];
    }

    public function rawSQL($sql)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


}
