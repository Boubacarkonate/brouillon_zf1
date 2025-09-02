<?php

class FranceTravailnewController extends Zend_Controller_Action
{
    public function init()
    {
        parent::init();

        error_log("[FranceTravailController] Init du contrôleur");
    }

    public function indexAction()
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
    }

    public function competenceAction()
    {
        $codeRome = $this->_getParam('codeRome', null);
        $libelle  = $this->_getParam('libelle', null);

        $params = [];
        if (!empty($codeRome)) {
            $params['codeRome'] = $codeRome;
        }
        if (!empty($libelle)) {
            $params['libelle'] = $libelle;
        }

        // Si aucun paramètre, on met un code ROME par défaut (exemple : M1805 = Études et développement informatique)
        if (empty($params)) {
            $params['codeRome'] = 'M1805';
        }

        $francetravail = new Application_Model_Francetravail();
        $listeCompetences = $francetravail->searchCompetence($params);

        $this->view->competences = $listeCompetences;
    }

    public function fichemetierAction()
    {
        $params = [
            'champs' => 'code,groupescompetencesmobilisees(competences(libelle,code),enjeu(libelle,code)),groupessavoirs(savoirs(libelle,code),categoriesavoirs(libelle,code)),metier(libelle,code)'
        ];

        $francetravail = new Application_Model_Francetravail();

        try {
            $ficheMetier = $francetravail->ficheMetier($params);
            // echo '<pre>';
            // print_r($ficheMetier);
            // echo '</pre>';
            // exit;

            $this->view->ficheMetier = $ficheMetier;
            $this->view->error = null;
        } catch (Exception $e) {
            $this->view->ficheMetier = [];
            $this->view->error = $e->getMessage();
        }
    }

    // public function ficheAction()
    // {
    //     $model = new Application_Model_Francetravail();

    //     $page = (int) $this->_getParam('page', 0);
    //     $perPage = 10;
    //     $motsCles = $this->_getParam('motsCles', '');
    //     $departement = $this->_getParam('departement', '');

    //     error_log("[FranceTravailController] Paramètres reçus : page=$page, motsCles=$motsCles, departement=$departement");

    //     $params = [
    //         'motsCles'    => $motsCles,
    //         'departement' => $departement,
    //         'page'        => $page,
    //         'perPage'     => $perPage
    //     ];

    //     try {
    //         $result = $model->searchOffres($params);
    //         error_log("[FranceTravailController] Nombre d'offres reçues : " . print_r($result['resultats']));
    //     } catch (Exception $e) {
    //         $this->view->error = $e->getMessage();
    //         error_log("[FranceTravailController] Erreur lors de la récupération des resultats : " . $e->getMessage());
    //         $result = ['resultats' => []];
    //     }

    //     $this->view->offres   = $result['resultats'] ?? [];
    //     $this->view->page     = $page;
    //     $this->view->perPage  = $perPage;
    //     $this->view->params   = $params;

    //     error_log("[FranceTravailController] Fin indexAction, affichage des offres");


    //     // Indiquer explicitement d’utiliser fiche/test.phtml
    //     $this->renderScript('francetravailnew/fiche/test.phtml');
    // }
}
