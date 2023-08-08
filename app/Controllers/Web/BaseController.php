<?php

namespace App\Controllers\Web;

use App\Libraries\Auth;
use App\Libraries\Database;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;
use App\Libraries\QueryBuilder;
use App\Libraries\TwigConfigurator;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class BaseController
{
    protected Environment $view;
    protected array $args;
    protected array $filters;
    protected Auth $auth;
    protected Database $db;
    protected string $tableName;

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

    }

    public function index()
    {
        $primaryKey = 'id_' . strtolower(Helper::getTablePrimaryKeyName($this->tableName));
        $qb = new QueryBuilder($this->db);
        $qb = $qb->setTable($this->tableName);
        $qb = $qb->select('*');
        $qb = $qb->setAlias($primaryKey, 'id');
        $rows = $qb->get();
        $pagination = $qb->getPagination();
        $columns = $qb->getColumns();
        echo $this->view->render('list.html.twig', compact('columns', 'rows', 'pagination'));
        exit();
    }

    /**
     * @throws SyntaxError
     * @throws \ReflectionException
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function create(): void
    {
        $entity = 'App\Models\\' . $this->tableName;
        $entity = new $entity();
        $formComponent = new DynamicFormComponent($entity);

        $formData = [];

        $formData['action'] = $this->url( '/store');
        $formData['csrf_token'] = Helper::generateToken($this->tableName);
        $formData['button_label'] = 'Crea';

        $formHtml = $formComponent->renderForm($formData);

        // Puoi personalizzare la vista utilizzata per il form di creazione
        echo $this->view->render('form.html.twig', compact('formHtml'));
    }

    public function store()
    {
        $post = $_POST;
        // Verifica il token CSRF
        if (!Helper::validateToken($this->tableName, $post['csrf_token'])) {
            Helper::addError('Token CSRF non valido.');
            Helper::redirect($this->url('/create'));
            exit();
        }

        unset($post['csrf_token']);

        $post = Helper::sanificaInput($post);
        $entity = 'App\Models\\' . $this->tableName;
        $entity = new $entity();
        $newId = $entity->store($post);

        if ($newId !== false) {
            Helper::addSuccess('Nuovo record creato con successo.');
        } else {
            Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
        }

        Helper::redirect($this->url('/'));
    }


    public function edit($id)
    {
        $entityName = 'App\Models\\' . $this->tableName;
        $entity = new $entityName();
        $entity = $entity::findById($id);
        $primaryKey = 'id_' . strtolower(Helper::getTablePrimaryKeyName($this->tableName));
        if (!$entity) {
            Helper::addError('Record non trovato.');
            Helper::redirect($this->url('/'));
            exit();
        }
        $formComponent = new DynamicFormComponent($entity);

        $formData = [];
        $formData['action'] = $this->url('/update');
        $formData['csrf_token'] = Helper::generateToken($this->tableName);
        $formData[$primaryKey] = $id;
        $formData['button_label'] = 'Edit';

        $formHtml = $formComponent->renderForm($formData);

        echo $this->view->render('form.html.twig', compact('formHtml'));
    }

    public function update()
    {
        $entityName = 'App\Models\\' . $this->tableName;
        $entity = new $entityName();
        $post = $_POST;

        // Verifica il token CSRF
        if (!Helper::validateToken($this->tableName, $post['csrf_token'])) {
            Helper::addError('Token CSRF non valido.');
            Helper::redirect('/eccezioni-sale');
            exit();
        }

        unset($post['csrf_token']);

        $post = Helper::sanificaInput($post);

        $newId = $entity::update($post);

        if ($newId !== false) {
            Helper::addSuccess('Record aggiornato con successo.');
        } else {
            Helper::addError('Errore durante la creazione o l\'aggiornamento del record.');
        }

        Helper::redirect($this->url('/'));
        exit();
    }

    public function delete($id) {
        $entity = 'App\Models\\' . $this->tableName;
        $entity = new $entity();
        $entity->delete($id);
        Helper::addSuccess('Record eliminato con successo!');
        Helper::redirect($this->url('/'));
        exit();
    }

    public function bulkDelete() {
        $primaryKey = 'id_' . strtolower(Helper::getTablePrimaryKeyName($this->tableName));
        $qb = new QueryBuilder($this->db);
        $qb = $qb->setTable($this->tableName);
        $ids = $_POST['ids'];
        // Turn into array if not already
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
            $ids = array_filter($ids);
            $ids = array_map('intval', $ids);
        }
        $qb = $qb->whereIn($primaryKey, $ids);
        $qb = $qb->delete();
        $qb->execute();
        Helper::addSuccess('Record eliminati con successo!');
        Helper::redirect($this->url('/'));
        exit();
    }

    /**
     * Genera l'URL completo per una determinata rotta.
     *
     * @param string $route Nome della rotta (es. 'utenti.index')
     * @param array $params Parametri aggiuntivi per la rotta (opzionale)
     * @return string URL completo per la rotta specificata
     */
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
        $shortClassName = str_replace('Controller', '', $shortClassName);
        return $shortClassName;
    }

}