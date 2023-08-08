<?php

namespace App\Controllers\Web;

use App\Models\DisponibilitaSale;
use App\Libraries\QueryBuilder;
use App\Libraries\DynamicFormComponent;
use App\Libraries\Helper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DisponibilitaSaleController extends BaseController
{

    public function create(): void
    {
        $entity = new DisponibilitaSale();
        $giorni_disponibili = $this->getGiorniDisponibili();
        $entity->setGiorniDisponibili($giorni_disponibili);

        $formComponent = new DynamicFormComponent($entity);
        $formData = [];
        $formData['action'] = $this->url('store');
        $formData['csrf_token'] = Helper::generateToken('DisponibilitaSale');
        $formData['button_label'] = 'Crea';

        $formHtml = $formComponent->renderForm($formData);

        // Puoi personalizzare la vista utilizzata per il form di creazione
        echo $this->view->render('form.html.twig', compact('formHtml'));
    }

    public function edit($id)
    {
        $disponibilitasale = DisponibilitaSale::findById($id);
        if (!$disponibilitasale) {
            Helper::addError('Record non trovato.');
            Helper::redirect('/disponibilita-sale');
            exit();
        }
        $giorni_disponibili = $this->getGiorniDisponibili();
        $disponibilitasale->setGiorniDisponibili($giorni_disponibili);
        $formComponent = new DynamicFormComponent($disponibilitasale);

        $formData = [];
        $formData['action'] = $this->url('/update');
        $formData['csrf_token'] = Helper::generateToken('DisponibilitaSale');
        $formData['id_disponibilita'] = $id;
        $formData['button_label'] = 'Edit';

        $formHtml = $formComponent->renderForm($formData);

        echo $this->view->render('form.html.twig', compact('formHtml'));
    }


    public function getGiorniDisponibili()
    {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder = $queryBuilder->setTable('GiorniSettimana');
        $queryBuilder = $queryBuilder->select('*');
        $giorni = $queryBuilder->get();
        $giorni_disponibili = [];
        foreach ($giorni as $index => $giorno) {
            $giorni_disponibili[$index] = $giorno['nome_giorno'];
        }
        return $giorni_disponibili;
    }
}
