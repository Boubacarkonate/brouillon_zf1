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

    public function entrepriserecrutementAction()
    {
        $params = [
            'citycode' => $this->_getParam('citycode'),
            'rome'     => $this->_getParam('rome'),
            'distance' => $this->_getParam('distance', 10)
        ];

        $annuaire = new Application_Model_Annuaire();
        $model    = new Application_Model_Francetravail();

        try {
            error_log("[BonneBoiteController] Recherche Bonne Boite avec params : " . json_encode($params));

            // Appel La Bonne Boîte
            $resultats   = $model->getLaBonneBoite($params);
            $entreprises = $resultats['companies'] ?? $resultats['results'] ?? $resultats ?? [];

            // Enrichissement avec Annuaire Entreprises
            $details = $annuaire->getInfosEntreprise('');    //test


            // Passage à la vue
            $this->view->resultats = $entreprises;
            $this->view->citycode  = $params['citycode'];
            $this->view->rome      = $params['rome'];
            $this->view->distance  = $params['distance'];
            $this->view->message   = "Résultats Bonne Boite récupérés avec succès.";

            error_log("[BonneBoiteController] Nombre de résultats : " . count($entreprises));
        } catch (Exception $e) {
            $this->view->resultats = [];
            $this->view->citycode  = $params['citycode'];
            $this->view->rome      = $params['rome'];
            $this->view->distance  = $params['distance'];
            $this->view->message   = "Erreur lors de l’appel à Bonne Boite : " . $e->getMessage();

            error_log("[BonneBoiteController] Erreur Bonne Boite : " . $e->getMessage());
        }
    }

    public function servicesAction()
    {
        // Params
        $page     = (int)$this->_getParam('page', 0);
        $perPage  = (int)$this->_getParam('perPage', 20);
        $lat      = $this->_getParam('lat', null);
        $lon      = $this->_getParam('lon', null);
        $radius   = (int)$this->_getParam('radius', 10); // km
        $theme    = $this->_getParam('theme', null); // ex. 'mobilite', 'logement', 'handicap', 'numerique'
        $query    = $this->_getParam('q', null);

        $model = new Application_Model_DataInclusion();

        try {
            $searchParams = [
                'page' => $page,
                'perPage' => $perPage,
                'lat' => $lat,
                'lon' => $lon,
                'radius' => $radius,
                'theme' => $theme,
                'q' => $query,
            ];

            $result = $model->searchServices($searchParams);
            // $result expected: ['items' => [...], 'total' => int]
            $this->view->services = $result['items'] ?? [];
            $this->view->total = $result['total'] ?? count($this->view->services);
        } catch (Exception $e) {
            $this->view->services = [];
            $this->view->total = 0;
            $this->view->error = "Erreur API Data Inclusion : " . $e->getMessage();
            error_log("[Services] Erreur recherche services : " . $e->getMessage());
        }

        // pass filters back
        $this->view->page = $page;
        $this->view->perPage = $perPage;
        $this->view->lat = $lat;
        $this->view->lon = $lon;
        $this->view->radius = $radius;
        $this->view->theme = $theme;
        $this->view->q = $query;
    }

    /**
     * AJAX: retourne le détail d'un service (JSON)
     * URL: /francetravailnew/service-detail?id=...
     */
    public function serviceDetailAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $id = $this->_getParam('id');
        if (empty($id)) {
            return $this->_helper->json(['success' => false, 'message' => 'id manquant']);
        }

        $model = new Application_Model_DataInclusion();
        try {
            $detail = $model->getServiceDetail($id);
            return $this->_helper->json(['success' => true, 'data' => $detail]);
        } catch (Exception $e) {
            error_log("[Services] Erreur detail service $id : " . $e->getMessage());
            return $this->_helper->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
