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
            $resultats = $model->getLaBonneBoite($params);
            $items     = $resultats['items'] ?? [];

            // Enrichissement de toutes les entreprises avec l'adresse du siège
            foreach ($items as $entreprise) {
                if (!empty($entreprise['siret'])) {
                    $infos = $annuaire->getInfosEntreprise($entreprise['siret']);
                    if (!empty($infos['siege']['geo_adresse'])) {
                        $items['adresse_complete'] = $infos['siege']['geo_adresse'];
                    }
                }
            }

            // Passage à la vue
            // $this->view->adresse = $lacalisation;
            $this->view->items     = $items;
            $this->view->resultats = $resultats;
            $this->view->citycode  = $params['citycode'];
            $this->view->rome      = $params['rome'];
            $this->view->distance  = $params['distance'];
            $this->view->message   = "Résultats Bonne Boite récupérés avec succès.";
        } catch (Exception $e) {
            $this->view->items     = [];
            $this->view->resultats = [];
            $this->view->citycode  = $params['citycode'];
            $this->view->rome      = $params['rome'];
            $this->view->distance  = $params['distance'];
            $this->view->message   = "Erreur lors de l’appel à Bonne Boite : " . $e->getMessage();
        }
    }





    /**
     * Liste / recherche des services
     */
    /** Liste et recherche de services */
    public function servicesAction()
    {
        $codeCommune = $this->_getParam('code_commune', '75120');
        $theme       = $this->_getParam('theme', null);
        $type        = $this->_getParam('type', null);
        $source      = $this->_getParam('source', null);
        $q           = $this->_getParam('q', null);
        $page        = max(1, (int)$this->_getParam('page', 1));
        $perPage     = 1000;
        $themeValue = $this->_getParam('themeValue');
        $typeValue = $this->_getParam('typeValue');

        $model = new Application_Model_Datainclusion();


        // recuperation de la liste des thématiques
        // $a = $model->getRefThematique();
        // var_dump($a);
        // exit;

        $themesRecuperer   = $model->getRefThematique();

        $typeRecuperer = $model->getTypeServices();
        $allTypes = [];
        foreach ($typeRecuperer as $key => $value) {
            $allTypes[] = $value['label'];
        }


        $sourcesData = $model->getSources();
        $sourcesRecuperer = [];
        foreach ($sourcesData as $s) {
            // clé = slug (utilisée dans les requêtes / filtres)
            // valeur = nom (affichée dans le select)
            $sourcesRecuperer[$s['slug']] = $s['nom'];
        }

        // var_dump($sourceRecuperer);
        // exit;



        // $b = [];
        // foreach ($a as $label) {
        //     $b[] = $label['label'];
        // }


        $typeService = $model->getTypeServices();
        // var_dump($typeService);
        // exit;

        try {
            // --- Recherche sans filtre pour récupérer toutes les thématiques/types/structures ---
            $allServicesResp = $model->searchServices(['page' => 1, 'perPage' => $perPage]);
            $allServices = $allServicesResp['items'] ?? [];

            $themes     = [];
            $types      = [];
            $sources    = [];
            $structures = [];

            foreach ($allServices as $item) {
                $s = $item['service'] ?? $item;

                // Thématiques
                foreach ($s['thematiques'] ?? [] as $t) {
                    $themes[$t] = $t;
                }

                // Types
                if (!empty($s['type'])) {
                    $types[$s['type']] = $s['type'];
                }

                // Sources
                if (!empty($s['source'])) {
                    $sources[$s['source']] = $s['source'];
                }

                // Structures
                if (!empty($s['structure'])) {
                    $struct = $s['structure'];
                    $id = $struct['id'] ?? uniqid('struct_');
                    $structures[$id] = [
                        'id'      => $id,
                        'label'   => $struct['nom'] ?? $id,
                        'address' => $struct['adresse'] ?? '',
                        'lat'     => $struct['latitude'] ?? null,
                        'lon'     => $struct['longitude'] ?? null,
                    ];
                }
            }

            ksort($themes);
            ksort($types);
            ksort($sources);

            // --- Recherche filtrée pour la vue ---
            $params = [
                'page'    => $page,
                'perPage' => 20,
            ];

            if ($codeCommune) $params['code_commune'] = $codeCommune;
            if ($theme)       $params['thematiques']  = $theme;
            if ($type)        $params['types']        = $type;
            if ($source)      $params['sources']      = $source;
            if ($q)           $params['q']            = $q;

            $resp = $model->searchServices($params);

            $services = [];
            $filteredStructures = [];

            foreach ($resp['items'] ?? [] as $item) {
                $s = $item['service'] ?? $item;

                $services[] = [
                    'id'          => $s['id'] ?? null,
                    'label'       => $s['nom'] ?? null,
                    'description' => $s['description'] ?? null,
                    'codePostal'  => $s['code_postal'] ?? null,
                    'telephone'   => $s['telephone'] ?? null,
                    'courriel'    => $s['courriel'] ?? null,
                    'theme'       => $s['thematiques'] ?? [],
                    'type'        => $s['type'] ?? null,
                    'adresse'     => $s['adresse'] ?? null,
                    'source'      => $s['source'] ?? null,
                    'lat'         => $s['latitude'] ?? null,
                    'lon'         => $s['longitude'] ?? null,
                    'commune'     => $s['commune'] ?? null,
                    'structure'   => $s['structure'] ?? null,
                    'distance'    => $s['distance'] ?? null,
                    'modes_accueil'    => $s['modes_accueil'] ?? null,
                ];

                // var_dump($services);
                // exit;

                // Structures liées aux services filtrés
                if (!empty($s['structure'])) {
                    $struct = $s['structure'];
                    $id = $struct['id'] ?? uniqid('struct_');
                    $filteredStructures[$id] = [
                        'id'          => $id,
                        'label'       => $struct['nom'] ?? $id,
                        'adresse'     => $s['adresse'] ?? null,
                        'commune'     => $s['commune'] ?? null,
                        'codePostal'  => $s['code_postal'] ?? null,
                        'telephone'   => $s['telephone'] ?? null,
                        'courriel'    => $s['courriel'] ?? null,
                        'description' => $s['description'] ?? null,
                    ];
                }
            }

            // --- Passage à la vue ---
            $this->view->thematique = $themesRecuperer;
            $this->view->typeValue = $typeValue;
            $this->view->allTypes = $allTypes;
            $this->view->sourcesList = $sourcesRecuperer;

            $this->view->codeCommune = $codeCommune;
            $this->view->services    = $services;
            $this->view->structures  = $filteredStructures;
            $this->view->total       = $resp['total'] ?? count($services);
            $this->view->page        = $page;
            $this->view->perPage     = $perPage;
            $this->view->theme       = $theme;
            $this->view->type        = $type;
            $this->view->source      = $source;
            $this->view->q           = $q;
            $this->view->themes      = $themes;
            $this->view->types       = $types;
            $this->view->sources     = $sources;
        } catch (Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }


    public function marchetravaillocalAction()
    {



        // ============================
        // 1. Projets fictifs
        // ============================
        $projet = [
            "Projet 1" => ["coderome" => 'M1855', "departement" => 75],
            "Projet 2" => ["coderome" => 'A1203', "departement" => 92],
            "Projet 3" => ["coderome" => 'M1830', "departement" => 31],
            "Projet 4" => ["coderome" => 'N1101', "departement" => 64],
            "Projet 5" => ["coderome" => 'C1503', "departement" => 13]
        ];

        $periode = [
            "ANNEE",
            "TRIMESTRE"
        ];


        $territoireCode = $this->_getParam('territoireCode', 'Inscrire une code de territoire');
        $codeRome = $this->_getParam('codeRome', 'Inscrire un code rome');
        $periodeRecherchee = $this->_getParam('periodeRecherchee', 'TRIMESTRE');
        if (!in_array($periodeRecherchee, $periode)) {
            $periodeRecherchee = 'TRIMESTRE';
        }
        $model = new Application_Model_Francetravail();



        try {
            // --- Dynamique de l'emploi ---
            $paramsDynamique = [
                "codeTypeTerritoire" => "DEP",
                "codeTerritoire"     => $territoireCode,
                "codeTypeActivite"   => "MOYENNE",   // global sur le territoire
                "codeActivite"       => "MOYENNE",   // idem
                "codeTypePeriode"    => $periodeRecherchee, //trimestre obligatoire
                "dernierePeriode"    => true
            ];

            $dyn = $model->getDynamiqueEmploi($paramsDynamique);

            // var_dump($dyn);
            // exit;
            $dynamique = null;

            if (!empty($dyn['listeValeursParPeriode'])) {
                $v = $dyn['listeValeursParPeriode'][0];

                // Sécurisation pour éviter les undefined / null
                $valeur = $v['valeurPrincipaleNom']
                    ?? $v['valeurPrincipaleNombre']
                    ?? $v['valeurPrincipaleMontant']
                    ?? $v['valeurPrincipaleRang']
                    ?? $v['valeurPrincipaleTaux']
                    ?? $v['valeurSecondairePourcentage']
                    ?? null;
                // var_dump($valeur);

                $dynamique = [
                    'valeur'        => $valeur,
                    'periodeLib'    => $v['libPeriode'] ?? '-',
                    'datMaj'        => $v['datMaj'] ?? null,
                    'territoire'    => $v['libTerritoire'] ?? '-',
                    'departement' => $v['codeTerritoire'],
                    'activite'      => $v['libActivite'] ?? 'Marché global',
                    'valeur' => $v['valeurPrincipaleNom'],
                    'interpretation' => $this->interpretDynamique($valeur)
                ];
            }

            $this->view->dynamique = $dynamique;


            /////////////////////////////////////////////////////////////////////////////////////

            // --- Demandeurs d'emploi DE_5 nouveaux inscrits sur les 12 derniers mois ---

            /////////////////////////////////////////////////////////////

            $paramsDemandeurs12DerniersMois = [
                "codeTypeTerritoire"   => "DEP",
                "codeTerritoire"       => $territoireCode,
                "codeTypeActivite"     => "ROME",
                "codeActivite"         => $codeRome,
                "codeTypePeriode"      => $periodeRecherchee,
                "codeTypeNomenclature" => "CATCAND",
                "dernierePeriode"      => true
            ];

            $demandeurs12DerniersMois = $model->getDemandeurs12DerniersMois($paramsDemandeurs12DerniersMois);
            // var_dump($demandeurs12DerniersMois);
            // exit;


            $demandeurDerniersMois = null;
            foreach ($demandeurs12DerniersMois['listeValeursParPeriode'] as $v) {
                if ($v['codeNomenclature'] === 'CUMUL 12 MOIS') {


                    $demandeurDerniersMois = [
                        'periodeLib'       => $v['libPeriode'],
                        'datMaj'           => $v['datMaj'],
                        'territoire'       => $v['libTerritoire'],
                        'departement'      => $v['codeTerritoire'],
                        'activite'         => $v['libActivite'],
                        'categorie'        => $v['libNomenclature'],
                        'nombre'           => $v['valeurPrincipaleNombre'] ?? null,
                        'pourcentage'      => $v['valeurSecondairePourcentage'] ?? null,
                        // 'caracteristiques' => $v['listeValeurParCaract'] ?? [],
                    ];
                }
            }
            // var_dump($demandeur12DerniersMois);
            // exit;
            $this->view->demandeur12DerniersMois = $demandeurDerniersMois;



            /////////////////////////////////////////////////////////////////////////////////////

            // --- Demandeurs d'emploi DE_1 inscrists en fin de trimestre ---

            /////////////////////////////////////////////////////////////////////////////////////
            $paramsDemandeurs = [
                "codeTypeTerritoire"   => "DEP",
                "codeTerritoire"       => $territoireCode,
                "codeTypeActivite"     => "ROME",
                "codeActivite"         => $codeRome,
                "codeTypePeriode"      => $periodeRecherchee,
                "codeTypeNomenclature" => "CATCAND",
                "dernierePeriode"      => true
            ];

            $dem = $model->getDemandeurs($paramsDemandeurs);
            // var_dump($dem);
            // exit;

            $demandeursData = [];
            $dernier = null;
            foreach ($dem['listeValeursParPeriode'] as $v) {
                if ($v['codeNomenclature'] === 'A') {
                    // On ne garde que la période la plus récente

                    // Filtre GENRE
                    $genres = [];
                    if (!empty($v['listeValeurParCaract'])) {
                        foreach ($v['listeValeurParCaract'] as $caract) {
                            if ($caract['codeTypeCaract'] === 'GENRE') {
                                $genres[] = $caract;
                            }
                        }
                    }

                    $dernier = [
                        'periodeLib'       => $v['libPeriode'],
                        'datMaj'           => $v['datMaj'],
                        'territoire'       => $v['libTerritoire'],
                        'departement'      => $v['codeTerritoire'],
                        'activite'         => $v['libActivite'],
                        'categorie'        => $v['libNomenclature'],
                        'nombre'           => $v['valeurPrincipaleNombre'] ?? null,
                        'pourcentage'      => $v['valeurSecondairePourcentage'] ?? null,
                        'caracteristiquesGenre' => $genres,
                        'caracteristiques' => $v['listeValeurParCaract'] ?? [],
                    ];
                }
            }
            // var_dump($dernier);
            // exit;
            $this->view->dernierDemandeurs = $dernier;



            // var_dump($demandeursData[0]);
            // exit;



            // --- Embauches ---
            $paramsEmbauches = [
                "codeTypeTerritoire"   => "DEP",
                "codeTerritoire"       => $territoireCode,
                "codeTypeActivite"     => "ROME",
                "codeActivite"         => $codeRome,
                "codeTypePeriode"      => $periodeRecherchee,
                "codeTypeNomenclature" => "CATCANDxDUREEEMP",
                "dernierePeriode"      => true
            ];

            $emb = $model->getEmbauches($paramsEmbauches);


            $embauchesData = null;
            foreach ($emb['listeValeursParPeriode'] as $v) {
                if ($v['codeNomenclature'] === "ABCDEFG-TOUTE") {

                    $genres = [];
                    $contrat = [];
                    if (!empty($v['listeValeurParCaract'])) {
                        foreach ($v['listeValeurParCaract'] as $caract) {
                            if ($caract['codeTypeCaract'] === 'GENRE') {
                                $genres[] = $caract;
                            } elseif ($caract['codeTypeCaract'] === 'TYPECTR') {
                                $contrat[] = $caract;
                            }
                        }
                    }


                    $embauchesData = [
                        'periodeLib'    => $v['libPeriode'],
                        'datMaj'        => $v['datMaj'],
                        'territoire'    => $v['libTerritoire'],
                        'departement'    => $v['codeTerritoire'],
                        'activite'      => $v['libActivite'],
                        'categorie'     => $v['libNomenclature'],
                        'nombre'        => $v['valeurPrincipaleNombre'] ?? null,
                        'pourcentage'   => $v['valeurSecondairePourcentage'] ?? null,
                        'caracteristiques' => $v['listeValeurParCaract'] ?? [],
                        'genre'             => $genres,
                        'typeContrat'    => $contrat
                    ];
                }
            }
            // var_dump($embauchesData);
            // exit;
            $this->view->embauches = $embauchesData;

            // --- Offres d'emploi ---
            $paramsOffres = [
                "codeTypeTerritoire"   => "DEP",
                "codeTerritoire"       => $territoireCode,
                "codeTypeActivite"     => "ROME",
                "codeActivite"         => $codeRome,
                "codeTypePeriode"      => $periodeRecherchee,
                "codeTypeNomenclature" => "ORIGINEOFF",
                "dernierePeriode"      => true
            ];

            $offres = $model->getStatsOffreEmploi($paramsOffres);

            $offreData = null;
            foreach ($offres['listeValeursParPeriode'] as $v) {
                if ($v['codeNomenclature'] === 'TOFF-CUMUL12MOIS') {

                    $offreData = [
                        'periodeLib'    => $v['libPeriode'],
                        'datMaj'        => $v['datMaj'],
                        'territoire'    => $v['libTerritoire'],
                        'departement'    => $v['codeTerritoire'],
                        'activite'      => $v['libActivite'],
                        'codeTypePeriode' => $v['codeTypePeriode'],
                        'categorie'     => $v['libNomenclature'],
                        'nombre'        => $v['valeurPrincipaleNombre'] ?? null,
                        // 'caracteristiques' => $v['listeValeurParCaract'] ?? [],
                    ];
                }
            };
            // var_dump($offreData);
            // exit;

            $this->view->statsOffres = $offreData;


            // var_dump($offres['listeValeursParPeriode'][3]);
            // exit;


            // --- Tension recrutement ---
            $paramsTension = [
                "codeTypeTerritoire"   => "DEP",
                "codeTerritoire"       => $territoireCode,
                "codeTypeActivite"     => "ROME",
                "codeActivite"         => $codeRome,
                "codeTypePeriode"      => "ANNEE",             // toujours annuel
                "codeTypeNomenclature" => "TYPE_TENSION",      // toujours ce libellé
                "dernierePeriode"      => true,
                "sansCaracteristiques" => true        // pas de caractéristiques
            ];

            try {
                $ten = $model->getTensionRecrutement($paramsTension);

                // --- Initialisation des indicateurs ---
                $tensionPrincipale       = null;  // Indicateur de tension global
                $intensiteEmbauche       = null;  // Intensité d’embauche
                $manqueMainOeuvre        = null;  // Manque de main-d’œuvre
                $attractiviteSalariale   = null;  // Attractivité salariale
                $conditionsTravail       = null;  // Conditions de travail
                $durabiliteEmploi        = null;  // Durabilité de l’emploi
                $inadEquationGeo         = null;  // Inadéquation géographique

                foreach ($ten['listeValeursParPeriode'] as $v) {
                    $valeur = $v['valeurPrincipaleDecimale'] ?? null;

                    switch ($v['codeNomenclature']) {
                        case 'PERSPECTIVE':
                            $tensionPrincipale = [
                                'valeur'         => $valeur,
                                'periodeType'    => $v['codeTypePeriode'],
                                'periodeCode'    => $v['codePeriode'],
                                'periodeLib'     => $v['libPeriode'],
                                'datMaj'         => $v['datMaj'],
                                'libActivite'    => $v['libActivite'],
                                'interpretation' => $this->interpretIndicateur($v['codeNomenclature'], $valeur),
                            ];
                            break;

                        case 'INT_EMB':
                            $intensiteEmbauche = [
                                'valeur'         => $valeur,
                                'periodeLib'     => $v['libPeriode'],
                                'libActivite'    => $v['libActivite'],
                                'interpretation' => $this->interpretIndicateur($v['codeNomenclature'], $valeur),
                            ];
                            break;

                        case 'MAIN_OEUVRE':
                            $manqueMainOeuvre = [
                                'valeur'         => $valeur,
                                'periodeLib'     => $v['libPeriode'],
                                'libActivite'    => $v['libActivite'],
                                'interpretation' => $this->interpretIndicateur($v['codeNomenclature'], $valeur),
                            ];
                            break;

                        case 'ATTR_SALARIALE':
                            $attractiviteSalariale = [
                                'valeur'         => $valeur,
                                'periodeLib'     => $v['libPeriode'],
                                'libActivite'    => $v['libActivite'],
                                'interpretation' => $this->interpretIndicateur($v['codeNomenclature'], $valeur),
                            ];
                            break;

                        case 'COND_TRAVAIL':
                            $conditionsTravail = [
                                'valeur'         => $valeur,
                                'periodeLib'     => $v['libPeriode'],
                                'libActivite'    => $v['libActivite'],
                                'interpretation' => $this->interpretIndicateur($v['codeNomenclature'], $valeur),
                            ];
                            break;

                        case 'DUR_EMPL':
                            $durabiliteEmploi = [
                                'valeur'         => $valeur,
                                'periodeLib'     => $v['libPeriode'],
                                'libActivite'    => $v['libActivite'],
                                'interpretation' => $this->interpretIndicateur($v['codeNomenclature'], $valeur),
                            ];
                            break;

                        case 'MISMATCH_GEO':
                            $inadEquationGeo = [
                                'valeur'         => $valeur,
                                'periodeLib'     => $v['libPeriode'],
                                'libActivite'    => $v['libActivite'],
                                'interpretation' => $this->interpretIndicateur($v['codeNomenclature'], $valeur),
                            ];
                            break;
                    }
                }

                // var_dump($tensionPrincipale);
                // var_dump($intensiteEmbauche);
                // var_dump($manqueMainOeuvre);
                // var_dump($attractiviteSalariale);
                // var_dump($conditionsTravail);
                // var_dump($durabiliteEmploi);
                // var_dump($inadEquationGeo);
                // exit;

                // Passe les valeurs à la vue
                $this->view->tensionPrincipale      = $tensionPrincipale;
                $this->view->intensiteEmbauche      = $intensiteEmbauche;
                $this->view->manqueMainOeuvre       = $manqueMainOeuvre;
                $this->view->attractiviteSalariale  = $attractiviteSalariale;
                $this->view->conditionsTravail      = $conditionsTravail;
                $this->view->durabiliteEmploi       = $durabiliteEmploi;
                $this->view->inadEquationGeo        = $inadEquationGeo;
            } catch (Exception $e) {
                error_log("[FranceTravail] Tension non disponible : " . $e->getMessage());
                $this->view->tensionPrincipale = null;
                $this->view->tensionError      = "Données non disponibles pour ce territoire / métier";
            }

            // salaire median
            $salaire = $model->getStatsSalaire('DEP', $territoireCode);
            $codeRomeCible = trim($codeRome); // code ROME demandé
            $salaireStruct = [];

            try {
                if (!empty($salaire['valeursParPeriode'])) {
                    foreach ($salaire['valeursParPeriode'] as $v) {
                        // On filtre par code ROME
                        if (trim($v['codeActivite']) !== $codeRomeCible) continue;

                        $activite  = $v['libActivite'] ?? '';
                        $periode   = $v['libPeriode'] ?? '';
                        $datMaj    = $v['datMaj'] ?? '';

                        // Transformation des salaires en clé => valeur
                        $salaires = [];
                        if (!empty($v['salaireValeurMontant'])) {
                            foreach ($v['salaireValeurMontant'] as $s) {
                                $salaires[$s['codeNomenclature']] = $s['valeurPrincipaleMontant'];
                            }
                        }

                        $salaireStruct[] = [
                            'activite'   => $activite,
                            'periode'    => $periode,
                            'datMaj'     => $datMaj,
                            'salaire1'   => $salaires['SAL1'] ?? null, // débutant
                            'salaire2'   => $salaires['SAL2'] ?? null, // moyen
                            'salaire3'   => $salaires['SAL3'] ?? null, // expérimenté
                        ];
                    }
                }

                // echo "<pre>";
                // print_r($salaire);
                // echo "</pre>";
                // exit;

                if (empty($salaireStruct)) {
                    // Aucun résultat pour ce code ROME
                    $this->view->salaireParRome = null;
                    $this->view->salaireError = "Aucune donnée trouvée pour le code ROME {$codeRomeCible}";
                } else {
                    $this->view->salaireParRome = $salaireStruct;
                }
            } catch (Exception $e) {
                $this->view->salaireParRome = null;
                if (strpos($e->getMessage(), '404') !== false) {
                    $this->view->salaireError = "Aucune donnée trouvée pour le code ROME {$codeRomeCible}";
                } else {
                    $this->view->salaireError = "Erreur API : " . $e->getMessage();
                }
            }






            $this->view->codeRome = $codeRome;
            $this->view->territoireCode = $territoireCode;
            $this->view->projet = $projet;
            $this->view->periode = $periode;
        } catch (Exception $e) {
            error_log("[FranceTravail] Erreur API : " . $e->getMessage());
            $this->view->error = $e->getMessage();
        }
    }

    // --- Interprétation dynamique emploi ---
    protected function interpreterDynamique($data)
    {
        if (empty($data['listeValeursParPeriode'][0])) {
            return null;
        }

        $valeurs = $data['listeValeursParPeriode'][0];
        // var_dump($valeurs);
        // exit;
        $valeur = (int)($valeurs['valeurPrincipaleNom'] ?? 0);

        switch ($valeur) {
            case 3:
                $interpretation = "Très dynamique : forte croissance attendue sur ce territoire.";
                $badge = "success";
                break;
            case 2:
                $interpretation = "Dynamique moyenne : emploi stable, croissance modérée.";
                $badge = "warning";
                break;
            case 1:
                $interpretation = "Faible dynamique : opportunités limitées, vigilance sur la viabilité.";
                $badge = "danger";
                break;
            default:
                $interpretation = "Indicateur non défini.";
                $badge = "secondary";
        }

        return [
            "territoire"     => $valeurs['libTerritoire'] ?? '-',
            "departement"     => $valeurs['codeTerritoire'] ?? '-',
            "periode"        => $valeurs['libPeriode'] ?? '-',
            "activite"       => $valeurs['libActivite'] ?? '-',
            "valeur"         => $valeur,
            "interpretation" => $interpretation,
            "badge"          => $badge,
            "datMaj"         => $valeurs['datMaj'] ?? null
        ];
    }

    private function interpretDynamique($valeur)
    {
        if ($valeur === null) return "Non disponible";

        if ($valeur < 0) {
            return "Recul de l'emploi (marché en baisse)";
        } elseif ($valeur >= 0 && $valeur <= 2) {
            return "Stabilité du marché";
        } else {
            return "Marché en croissance (dynamique positive)";
        }
    }


    // protected function interpretTension($valeur) {
    //     if ($valeur === null) return "Donnée non disponible";

    //     if ($valeur >= 1) {
    //         return "Tension forte";
    //     } elseif ($valeur > 0) {
    //         return "Tension modérée";
    //     } elseif ($valeur == 0) {
    //         return "Tension neutre";
    //     } elseif ($valeur > -1) {
    //         return "Tension faible";
    //     } else {
    //         return "Tension très faible";
    //     }   
    //      }

    protected function interpretIndicateur(string $codeNomenclature, ?float $valeur): string
    {
        if ($valeur === null) {
            return "Donnée non disponible";
        }

        switch ($codeNomenclature) {

            // ======================
            // Indicateur de tension global
            // ======================
            case 'PERSPECTIVE':
                if ($valeur >= 1) return "Tension forte";
                if ($valeur > 0) return "Tension modérée";
                if ($valeur == 0) return "Tension neutre";
                if ($valeur > -1) return "Tension faible";
                return "Tension très faible";

                // ======================
                // Indicateurs prioritaires
                // ======================
            case 'INT_EMB':
                if ($valeur >= 1) return "Embauche rapide";
                if ($valeur > 0) return "Embauche modérée";
                return "Embauche faible";

            case 'MAIN_OEUVRE':
                if ($valeur >= 1) return "Forte pénurie";
                if ($valeur > 0) return "Pénurie modérée";
                return "Pas de pénurie";

                // ======================
                // Indicateurs secondaires
                // ======================
            case 'ATTR_SALARIALE':
                if ($valeur >= 0.5) return "Salarialement attractif";
                if ($valeur > 0) return "Légèrement attractif";
                return "Peu attractif";

            case 'COND_TRAVAIL':
                if ($valeur >= 0.5) return "Bonnes conditions";
                if ($valeur > 0) return "Conditions correctes";
                return "Conditions difficiles";

            case 'DUR_EMPL':
                if ($valeur >= 0.5) return "Emplois stables";
                if ($valeur > 0) return "Emplois moyennement stables";
                return "Emplois précaires";

            case 'MISMATCH_GEO':
                if ($valeur >= 0.5) return "Fort décalage géographique";
                if ($valeur > 0) return "Décalage modéré";
                return "Décalage faible";

            default:
                return "Valeur : " . $valeur;
        }
    }
}
