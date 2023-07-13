<?php

namespace App\Libraries;

class Table {
    private $headers = [];
    private $rows = [];
    private $sortFunctions = [];
    private $filters = [];

    public function __construct(array $headers) {
        $this->headers = $headers;
    }

    public function addRow(array $row) {
        $this->rows[] = $row;
    }

    public function setSortFunctions(array $sortFunctions) {
        $this->sortFunctions = $sortFunctions;
    }

    public function applySort($sortKey) {
        $sortFunction = $this->sortFunctions[$sortKey] ?? null;

        if (!is_null($sortFunction) && is_callable($sortFunction)) {
            usort($this->rows, $sortFunction);
        }
    }

    public function setFilters(array $filters) {
        $this->filters = $filters;
    }

    public function applyFilter($filterKey, $filterValue) {
        $filterFunction = $this->filters[$filterKey] ?? null;

        if (!is_null($filterFunction) && is_callable($filterFunction)) {
            $this->rows = array_filter($this->rows, function($row) use ($filterFunction, $filterValue) {
                return $filterFunction($row, $filterValue);
            });
        }
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getRows(): array {
        return $this->rows;
    }
}