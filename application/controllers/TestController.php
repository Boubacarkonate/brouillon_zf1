<?php
class TestController extends Zend_Controller_Action
{
    public function init() {}

    public function ficheAction()
    {
        $model = new Application_Model_Francetravail();

        $page = (int) $this->_getParam('page', 0);
        $perPage = 10;
        $motsCles = $this->_getParam('motsCles', '');
        $departement = $this->_getParam('departement', '');

        error_log("[FranceTravailController] Paramètres reçus : page=$page, motsCles=$motsCles, departement=$departement");

        $params = [
            'motsCles'    => $motsCles,
            'departement' => $departement,
            'page'        => $page,
            'perPage'     => $perPage
        ];

        try {
            $result = $model->searchOffres($params);
            error_log("[FranceTravailController] Nombre d'offres reçues : " . print_r($result['resultats']));
        } catch (Exception $e) {
            $this->view->error = $e->getMessage();
            error_log("[FranceTravailController] Erreur lors de la récupération des resultats : " . $e->getMessage());
            $result = ['resultats' => []];
        }

        $this->view->offres   = $result['resultats'] ?? [];
        $this->view->page     = $page;
        $this->view->perPage  = $perPage;
        $this->view->params   = $params;

        error_log("[FranceTravailController] Fin indexAction, affichage des offres");


        // Indiquer explicitement d’utiliser fiche/test.phtml
        $this->renderScript('francetravailnew/fiche/test.phtml');
    }
}
