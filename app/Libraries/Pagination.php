<?php

namespace App\Libraries;

class Pagination
{
    protected $currentPage;
    protected $itemsPerPage;
    protected $totalItems;
    protected $totalPages;
    protected $path;
    protected $query;



    public function __construct($args = [])
    {
        // set default values
        $args = array_merge([
            'currentPage' => (int)($_GET['page'] ?? 1),
            'limit' => (int)($_GET['limit'] ?? 10),
            'totalItems' => 0,
            'path' => (string)($_SERVER['REQUEST_URI'] ?? '/'),
            'query' => $_GET ?? [],
        ], $args);

        $this->currentPage = max(1, (int)$args['currentPage']);
        $this->itemsPerPage = max(1, (int)$args['limit']);
        $this->totalItems = max(0, (int)$args['totalItems']);
        $this->totalPages = ceil($this->totalItems / $this->itemsPerPage);
        $this->path = $args['path'];
        $this->query = $args['query'];
    }

    public function getPreviousPage()
    {
        return $this->currentPage - 1;
    }

    public function getNextPage()
    {
        return $this->currentPage + 1;
    }

    public function getPreviousPageUrl()
    {
        return $this->path . '?' . http_build_query(array_merge($this->query, ['page' => $this->getPreviousPage()]));
    }

    public function getNextPageUrl()
    {
        return $this->path . '?' . http_build_query(array_merge($this->query, ['page' => $this->getNextPage()]));
    }

    public function getPages()
    {
        $pages = [];

        for ($i = 1; $i <= $this->totalPages; $i++) {
            $pages[] = [
                'number' => $i,
                'url' => $this->path . '?' . http_build_query(array_merge($this->query, ['page' => $i])),
                'isCurrent' => $i === $this->currentPage
            ];
        }

        return $pages;
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    public function getTotalItems()
    {
        return $this->totalItems;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getPagination(){
        return [
            'currentPage' => $this->currentPage,
            'itemsPerPage' => $this->itemsPerPage,
            'totalItems' => $this->totalItems,
            'totalPages' => ceil($this->totalItems / $this->itemsPerPage),
            'startItem' => ($this->currentPage - 1) * $this->itemsPerPage + 1,
            'endItem' => min($this->totalItems, $this->currentPage * $this->itemsPerPage),
            'query' => $this->query,

        ];
    }

    public function getOrderBy()
    {
        return $this->query['order_by'] ?? 'ID_utente';
    }

    public function getDirection()
    {
        return $this->query['direction'] ?? 'ASC';
    }

    public function getLimit()
    {
        return $this->itemsPerPage;
    }

    public function setTotalItems(mixed $totalUsers)
    {
        $this->totalItems = $totalUsers;
    }

    public function getOffset()
    {
        return ($this->currentPage > 1) ? ($this->currentPage - 1) * $this->itemsPerPage : 0;
    }




}