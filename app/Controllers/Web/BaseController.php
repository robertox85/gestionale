<?php

namespace App\Controllers\Web;

use App\Libraries\Auth;
use App\Libraries\Database;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;
use App\Libraries\QueryBuilder;
use App\Libraries\TwigConfigurator;

use Twig\Environment;

abstract class BaseController
{
    protected Environment $view;
    protected array $args;
    protected array $filters;
    protected Auth $auth;
    protected Database $db;
    protected string $tableName;
    protected string $primaryKey;

    public function __construct()
    {
        $this->view = TwigConfigurator::configure();
        $this->args = [
            'limit' => $_GET['limit'] ?? 10,
            'currentPage' => $_GET['page'] ?? 1,
            'order_by' => $_GET['sort'] ?? 'id',
            'order' => $_GET['direction'] ?? 'asc',
            'search' => $_GET['s'] ?? '',
        ];
        $this->auth = new Auth();
        $this->db = Database::getInstance();
        $this->tableName = $this->getShortClassName();
        $this->primaryKey = $this->getPrimaryKey();

    }
    public function index()
    {
        $qb = new QueryBuilder($this->db);
        $qb = $qb->setTable($this->tableName);
        $qb = $qb->select('*');
        $qb = $qb->setAlias($this->primaryKey, 'id');
        $rows = $qb->get();
        $pagination = $qb->getPagination();
        $columns = $qb->getColumns();
        echo $this->view->render('list.html.twig', compact('columns', 'rows', 'pagination'));
        exit();
    }
    public function create(): void
    {
        $entity = $this->getEntity();
        $formComponent = new DynamicFormComponent($entity);
        $formData = [];
        $formData['action'] = $this->url('/store');
        $formData['csrf_token'] = Helper::generateToken($this->tableName);
        $formData['button_label'] = 'Crea';

        $formHtml = $formComponent->renderForm($formData);

        // Puoi personalizzare la vista utilizzata per il form di creazione
        echo $this->view->render('form.html.twig', compact('formHtml'));
    }
    public function store()
    {
        $post = $_POST;
        $this->verifyToken($post['csrf_token']);
        unset($post['csrf_token']);

        $post = Helper::sanificaInput($post);
        $entity = 'App\Models\\' . $this->tableName;
        $entity = new $entity();

        // set the properties of the entity
        $entity->setProperties($post);
        try {
            $entity->store();
            Helper::addSuccess('Record aggiornato con successo!');
            Helper::redirect($this->url('/'));
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
    public function update()
    {
        $entity = $this->getEntity();
        $this->verifyToken($_POST['csrf_token']);
        unset($_POST['csrf_token']);
        $post = Helper::sanificaInput($_POST);
        $entity->setProperties($post);

        try {
            $entity->store();
            Helper::addSuccess('Record aggiornato con successo!');
            Helper::redirect($this->url('/'));
        }
        catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
    public function edit($id)
    {
        $entity = $this->getEntity();
        $entity = $entity::get($id);

        if (!$entity) {
            Helper::addError('Record non trovato.');
            Helper::redirect($this->url('/'));
            exit();
        }
        $formComponent = new DynamicFormComponent($entity);
        $args = [];
        $args['action'] = $this->url('/update');

        $args['csrf_token'] = Helper::generateToken($this->tableName);
        $args['primary_key'] = [
            'name' => $this->primaryKey,
            'value' => $id
        ];
        $args['button_label'] = 'Edit';

        $formHtml = $formComponent->renderForm($args);

        echo $this->view->render('form.html.twig', compact('formHtml'));
    }
    public function delete($id)
    {
        $entity = $this->getEntity();
        try {
            $entity->delete($id);
            Helper::addSuccess('Record eliminato con successo!');
            Helper::redirect($this->url('/'));
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
    private function getEntity()
    {
        $entity = 'App\Models\\' . $this->tableName;
        return new $entity();
    }
    public function bulkDelete()
    {
        $ids = $_POST['ids'];
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
            $ids = array_filter($ids);
            $ids = array_map('intval', $ids);
        }
        $entity = $this->getEntity();
        try {
            $entity->bulkDelete($ids);
            Helper::addSuccess('Record eliminati con successo!');
            Helper::redirect($this->url('/'));
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
    private function verifyToken($token)
    {
        if (!Helper::validateToken($this->tableName, $token)) {
            Helper::addError('Token CSRF non valido.');
            Helper::redirect($this->url('/create'));
            exit();
        }
        return true;
    }
    function url(string $route, array $params = []): string
    {
        $fullUrl = $_SERVER['REQUEST_URI'];
        $fullUrl = ltrim($fullUrl, '/');
        $fullUrl = rtrim($fullUrl, '/');
        $fullUrl = preg_split('/\//', $fullUrl);
        $fullUrl = $fullUrl[0];
        $fullUrl = rtrim($fullUrl, '/');
        $baseUrl = $_ENV['BASE_URL'] ?? '';
        $routeParts = explode('.', $route);
        $controllerName = ltrim($routeParts[0], '/');
        $actionName = $routeParts[1] ?? 'index';

        $url = $baseUrl . '/' . $fullUrl . '/' . $controllerName;

        if ($actionName !== 'index') {
            $url .= '/' . $actionName;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
    private function getShortClassName()
    {
        $shortClassName = (new \ReflectionClass(static::class))->getShortName();
        return str_replace('Controller', '', $shortClassName);
    }

    private function getPrimaryKey()
    {
        $entity = $this->getEntity();
        return $entity->getPrimaryKey();
    }
}