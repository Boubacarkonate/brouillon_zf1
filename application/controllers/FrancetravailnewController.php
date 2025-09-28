<?php

class FranceTravailnewController extends Zend_Controller_Action
{
    public function init()
    {
        // parent::init();

        // error_log("[FranceTravailController] Init du contrôleur");
    }


    public function indexAction()
    {
        // ============================
        // 1. Projets fictifs
        // ============================
        $projet = [
            "Projet 1" => ["metier" => 'développeur', "codeInsee" => 92050],
            "Projet 2" => ["metier" => 'architecte', "codeInsee" => 79185],
            "Projet 3" => ["metier" => 'infirmier', "codeInsee" => 31003],
            "Projet 4" => ["metier" => 'chef de produit', "codeInsee" => 64005],
            "Projet 5" => ["metier" => 'ingénieur agronome', "codeInsee" => 80008]
        ];

        // ============================
        // 2. Paramètres de filtre / pagination
        // ============================
        $page = (int) $this->_getParam('page', 0);
        $perPage = 50;

        $params = [
            'motsCles' => $this->_getParam('motsCles', '') ?: null,
            'commune'  => $this->_getParam('commune', '') ?: null,
            'distance' => $this->_getParam('distance', 0) > 0 ? $this->_getParam('distance', 0) : null,
            'page'        => $page,
            'perPage'     => $perPage,
            'agregation' => []
        ];

        // Filtres dynamiques
        $dynamicFilters = ['typeContrat', 'natureContrat', 'niveauFormations', 'secteursActivites', 'experience'];
        foreach ($dynamicFilters as $filter) {
            $val = $this->_getParam($filter, null);
            if ($val !== null && $val !== '') {
                $params[$filter] = $val;
            }
        }

        // ============================
        // 3. Récupération des offres
        // ============================
        $model = new Application_Model_Francetravail();
        try {
            $result = $model->searchOffres($params);
            $offres = $result['resultats'] ?? [];
            $totalOffres = count($offres); // total réel si l'API le fournit


            // $hasNextPage = count($offres) === $perPage;    // Indicateur pour savoir s’il y a une page suivante

            var_dump($totalOffres);

            // Référentiels
            $typesContrats      = $model->getReferentiel('typesContrats');
            $naturesContrats    = $model->getReferentiel('naturesContrats');
            $niveauxFormation   = $model->getReferentiel('niveauxFormations');
            $secteursActivites  = $model->getReferentiel('secteursActivites');

            // Filtres dynamiques
            $allowedFilters = ['typeContrat', 'natureContrat', 'niveauFormations', 'secteursActivites', 'experience'];
            $filtresDynamiques = [];

            if (!empty($result['filtresPossibles'])) {
                foreach ($result['filtresPossibles'] as $filtre) {
                    $nom = $filtre['filtre'] ?? null;
                    if ($nom && in_array($nom, $allowedFilters)) {
                        $codes = array_map(fn($v) => $v['valeurPossible'], $filtre['agregation'] ?? []);
                        switch ($nom) {
                            case 'typeContrat':
                                $filtresDynamiques[$nom] = array_intersect_key($typesContrats, array_flip($codes));
                                break;
                            case 'natureContrat':
                                $filtresDynamiques[$nom] = array_intersect_key($naturesContrats, array_flip($codes));
                                break;
                            case 'niveauFormations':
                                $filtresDynamiques[$nom] = array_intersect_key($niveauxFormation, array_flip($codes));
                                break;
                            case 'secteursActivites':
                                $filtresDynamiques[$nom] = array_intersect_key($secteursActivites, array_flip($codes));
                                break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->view->error = $e->getMessage();

            $filtresDynamiques = [];

            // $hasNextPage = false;  //pagination sans nuéro de page
        }

        // ============================
        // 4. Envoi des données à la vue
        // ============================
        $this->view->projet = $projet;
        $this->view->offres = $offres;
        $this->view->page     = $page;
        $this->view->perPage  = $perPage;
        $this->view->filtresDynamiques = $filtresDynamiques;
        $this->view->params = $_GET; // <-- Ajouté pour éviter l'erreur array_merge
        $this->view->totalOffres = $totalOffres; // <-- ici le total

        // $this->view->hasNextPage = $hasNextPage;  //pagination sans nuéro de page
    }


    public function enregistreroffreAction()
    {
        // ============================
        // 1. Désactiver le layout et la vue
        // ============================
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // ============================
        // 2. Récupération des données POST
        // ============================
        $data = $this->getRequest()->getPost();

        // Vérification des champs obligatoires
        if (empty($data['id']) || empty($data['intitule']) || empty($data['url'])) {
            return $this->_helper->json([
                'success' => false,
                'message' => 'Données manquantes'
            ]);
        }

        // ============================
        // 3. Insertion en base
        // ============================
        try {
            $tableFrancetravail = new Zend_Db_Table('francetravail');

            $insertData = [
                'identifiant_offre' => $data['id'],
                'poste'             => $data['intitule'],
                'url'               => $data['url'],
                'date'              => new Zend_Db_Expr('NOW()')
            ];

            $tableFrancetravail->insert($insertData);

            // ============================
            // 4. Réponse JSON succès
            // ============================
            return $this->_helper->json([
                'success' => true,
                'message' => 'Offre enregistrée avec succès'
            ]);
        } catch (Exception $e) {
            // ============================
            // 4. Réponse JSON erreur
            // ============================
            return $this->_helper->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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

    /**
     * Formulaire simple pour tester un code ROME
     * URL : /francetravail/testsoftskills
     */
    public function testsoftskillsAction()
    {
        $this->_helper->layout->disableLayout();

        $codeRome = $this->_getParam('code');
        $this->view->codeRome = $codeRome;

        if ($codeRome) {
            try {
                $model = new Application_Model_Francetravail();
                $softSkills = $model->fetchSoftSkillsByRome($codeRome);
                var_dump($softSkills);
                $this->view->softSkills = $softSkills;
            } catch (Exception $e) {
                $this->view->error = $e->getMessage();
            }
        }
    }

    public function widgetAction()
    {
        $model = new Application_Model_Francetravail();
        $token = $model->getTokenWidget();
        $this->view->francetravailToken = $token;
    }
}
