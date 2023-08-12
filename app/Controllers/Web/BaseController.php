<?php

namespace App\Controllers\Web;

use App\Attributes\PrimaryKey;
use App\Libraries\Auth;
use App\Libraries\Database;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;
use App\Libraries\TwigConfigurator;

use App\Models\BaseModel;
use Twig\Environment;

abstract class BaseController
{
    protected Environment $view;

    protected array $filters;
    protected Auth $auth;
    protected Database $db;
    protected BaseModel $entity;
    protected string $primaryKey;
    private string $tableName;

    public function __construct()
    {
        $this->view = TwigConfigurator::configure();
        $this->auth = new Auth();
        $this->entity = $this->getEntity();
        $this->db = $this->entity->db;
        $this->tableName = $this->entity->getShortClassName();
        $this->primaryKey = $this->entity->getEntityProperty(PrimaryKey::class);

    }

    public function index(): void
    {
        $rows = $this->entity->getAll();
        $columns = array_keys($this->entity->getVisibleProperties());
        $pagination = $this->entity->qb->getPagination();
        echo $this->view->render('list.html.twig', compact('columns', 'rows', 'pagination'));
        exit();
    }

    public function create(): void
    {
        $formComponent = new DynamicFormComponent($this->entity);
        $formData = [];
        $formData['action'] = $this->url('/store');
        $formData['csrf_token'] = Helper::generateToken($this->tableName);
        $formData['button_label'] = 'Crea';

        $formHtml = $formComponent->renderForm($formData);

        // Puoi personalizzare la vista utilizzata per il form di creazione
        echo $this->view->render('form.html.twig', compact('formHtml'));
    }

    /**
     * @throws \Exception
     */
    public function store()
    {
        $post = $_POST;
        $this->verifyToken($post['csrf_token']);
        unset($post['csrf_token']);

        $post = Helper::sanificaInput($post);

        // set the properties of the entity
        $this->entity->setProperties($post);
        try {
            $this->entity->store();
            Helper::addSuccess('Record aggiornato con successo!');
            Helper::redirect($this->url('/'));
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function update()
    {
        $this->verifyToken($_POST['csrf_token']);
        unset($_POST['csrf_token']);
        $post = Helper::sanificaInput($_POST);
        $this->entity->setProperties($post);

        try {
            $this->entity->store();
            Helper::addSuccess('Record aggiornato con successo!');
            Helper::redirect($this->url('/'));
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->entity = $this->entity::get($id);

        $formComponent = new DynamicFormComponent($this->entity);
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
        try {
            $this->entity->delete($id);
            Helper::addSuccess('Record eliminato con successo!');
            Helper::redirect($this->url('/'));
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    private function getEntity()
    {
        $reflection = (new \ReflectionClass(static::class))->getShortName();
        // Remove the 'Controller' suffix, because the entity name is the same as the controller name
        $tableName = str_replace('Controller', '', $reflection);
        $entity = 'App\Models\\' . $tableName;
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

        try {
            $this->entity->bulkDelete($ids);
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
}