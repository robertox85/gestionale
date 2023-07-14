<?php

namespace App\Libraries;

class Table {
    private $headers = [];
    private $rows = [];

    public function __construct(array $headers) {
        $this->headers = $headers;
    }

    public function addRow(array $row) {
        $this->rows[] = $row;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getRows(): array {
        return $this->rows;
    }


}