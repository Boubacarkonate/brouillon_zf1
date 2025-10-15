<?php

class FranceTravailnewController extends Zend_Controller_Action
{
    public function init() {}




    public function metiercompetenceAction()
    {


        // Paramètres de l'API France Travail
        $paramsFicheMetier = [
            'champs' => 'code,groupescompetencesmobilisees(competences(libelle,code),enjeu(libelle,code)),groupessavoirs(savoirs(libelle,code),categoriesavoirs(libelle,code)),metier(libelle,code)'
        ];

        $paramsDomaineCompetence = [
            'champs' => 'code,libelle,enjeux(objectifs(libelle,macrocompetences(libelle,transferable,@macrosavoiretreprofessionnel(qualiteprofessionnelle),souscategorie,code,riasecmineur,codearborescence,transitionecologique,transitionnumerique,codeogr,maturite,riasecmajeur),code,codearborescence),libelle,code,codearborescence)'
        ];

        $paramsDescriptionMetier = [
            'emploireglemente,formacodes(libelle,code),libelle,domaineprofessionnel(libelle,granddomaine(libelle,code),code),obsolete,code,definition,secteursactiviteslies(secteuractivite(libelle,code,secteuractivite(libelle,code)),principal),divisionsnaf(libelle,code),riasecmajeur,transitionecologiquedetaillee,themes(libelle,code),transitionecologique,datefin,competencesmobiliseesprincipales(libelle,@macrosavoiretreprofessionnel(riasecmajeur,riasecmineur),@competencedetaillee(riasecmajeur,riasecmineur),code,@macrosavoirfaire(riasecmajeur,riasecmineur),codeogr),emploicadre,riasecmineur,transitionnumerique,contextestravail(libelle,code,categorie),codeisco,centresinterets(libelle,code),competencesmobilisees(libelle,@macrosavoiretreprofessionnel(riasecmajeur,riasecmineur),@competencedetaillee(riasecmajeur,riasecmineur),code,@macrosavoirfaire(riasecmajeur,riasecmineur),codeogr),transitiondemographique,secteursactivites(libelle,code,secteuractivite(libelle,code)),appellations(emploireglemente,transitionecologiquedetaillee,libelle,code,emploicadre,transitionecologique,transitionnumerique,transitiondemographique,classification,libellecourt,competencescles(frequence,competence(libelle,codeogr,code))),competencesmobiliseesemergentes(libelle,@macrosavoiretreprofessionnel(riasecmajeur,riasecmineur),@competencedetaillee(riasecmajeur,riasecmineur),code,@macrosavoirfaire(riasecmajeur,riasecmineur),codeogr),centresinteretslies(centreinteret(libelle,code),principal),accesemploi'
        ];

        // Projets fictifs
        $projet = [
            "Projet 1" => ["coderome" => 'M1805'],
            "Projet 2" => ["coderome" => 'A1203'],
            "Projet 3" => ["coderome" => 'M1830'],
            "Projet 4" => ["coderome" => 'N1101'],
            "Projet 5" => ["coderome" => 'C1503']
        ];

        $codeRome = $this->_getParam('codeRome', $projet['Projet 1']['coderome']);

        $francetravail = new Application_Model_Francetravail();

        try {

            //pour le partial
            $dashboardData = $this->getDashboardData();
            $this->view->dashboardData = $dashboardData;

            $missionsMetier = $francetravail->getMetierByCodeRome($paramsDescriptionMetier, $codeRome);

            $ficheMetier       = $francetravail->ficheMetier($codeRome, $paramsFicheMetier);
            $domaineCompetence = $francetravail->domaineCompetence($paramsDomaineCompetence);

            $this->view->domaineCompetence = $domaineCompetence;
            $this->view->ficheMetier       = $ficheMetier;
            $this->view->codeRome          = $codeRome;
            $this->view->missionsMetier = $missionsMetier;
            $this->view->projet = $projet;
            $this->view->error             = null;
        } catch (Exception $e) {
            $this->view->domaineCompetence = [];
            $this->view->ficheMetier       = [];
            $this->view->codeRome          = $codeRome;
            $this->view->error             = $e->getMessage();
        }
    }


    public function entrepriserecrutementAction()
    {


        $projet = [
            "Projet 1" => ["codeRome" => 'M1855', "codeInsee" => 92050],
            "Projet 2" => ["codeRome" => 'A1203', "codeInsee" => 79185],
            "Projet 3" => ["codeRome" => 'M1830', "codeInsee" => 31003],
            "Projet 4" => ["codeRome" => 'N1101', "codeInsee" => 64005],
            "Projet 5" => ["codeRome" => 'C1503', "codeInsee" => 80008]
        ];

        $params = [
            // 'citycode'   => $this->_getParam('citycode'),
            // 'rome'       => $this->_getParam('rome'),
            'citycode' => $this->_getParam('citycode', $projet['Projet 1']['codeInsee']),
            'rome'     => $this->_getParam('rome', $projet['Projet 1']['codeRome']),
            'distance'   => $this->_getParam('distance', 10),
            'page'       => $this->_getParam('page', 1),
            'page_size'  => $this->_getParam('page_size', 100)
        ];

        $annuaire = new Application_Model_Annuaire();
        $model    = new Application_Model_Francetravail();

        try {
            error_log("[BonneBoiteController] Recherche Bonne Boite avec params : " . json_encode($params));


            $resultats   = $model->getLaBonneBoite($params);


            $total      = $resultats['hits'] ?? 0;
            $page       = (int) $params['page'];
            $page_size  = (int) $params['page_size'];
            $nbPages    = $page_size > 0 ? ceil($total / $page_size) : 1;

            $entreprises = $resultats['results'] ?? $resultats ?? [];


            $this->view->resultats = $entreprises;
            $this->view->citycode  = $params['citycode'];
            $this->view->rome      = $params['rome'];
            $this->view->distance  = $params['distance'];
            $this->view->projet = $projet;
            $this->view->total     = $total;
            $this->view->page      = $page;
            $this->view->nbPages   = $nbPages;
            $this->view->message   = "Résultats récupérés avec succès : " . $total . " entreprises.";

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

    public function indexAction()
    {
        // ============================
        // 1. Projets fictifs
        // ============================
        $projets = [
            "Projet 1" => ["metier" => 'développeur', "codeInsee" => 92050],
            "Projet 2" => ["metier" => 'architecte', "codeInsee" => 79185],
            "Projet 3" => ["metier" => 'infirmier', "codeInsee" => 31003],
            "Projet 4" => ["metier" => 'chef de produit', "codeInsee" => 64005],
            "Projet 5" => ["metier" => 'ingénieur agronome', "codeInsee" => 80008]
        ];

        // ✅ Projet courant (par défaut : Projet 1)
        $currentProjet = $this->_getParam('projet', 'Projet 1');
        $this->view->currentProjet = $currentProjet;

        // ============================
        // 2. Paramètres de filtre / pagination
        // ============================
        $page = (int) $this->_getParam('page', 0);
        $perPage = 20;

        $params = [
            'motsCles' => $this->_getParam('motsCles', '') ?: null,
            'commune'  => $this->_getParam('commune', '') ?: null,
            'distance' => $this->_getParam('distance', 0) > 0 ? $this->_getParam('distance', 0) : null,
            'page'        => $page,
            'perPage'     => $perPage,
            'agregation' => []
        ];

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
            $totalOffres = count($offres);

            // Référentiels pour filtres dynamiques
            $typesContrats      = $model->getReferentiel('typesContrats');
            $naturesContrats    = $model->getReferentiel('naturesContrats');
            $niveauxFormation   = $model->getReferentiel('niveauxFormations');
            $secteursActivites  = $model->getReferentiel('secteursActivites');

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

            // ============================
            // 4. Récupération du dashboard complet
            // ============================
            $dashboardData = $this->getDashboardData($currentProjet);

            // Injecter toutes les données dans la vue pour le partial
            foreach ($dashboardData as $key => $value) {
                $this->view->$key = $value;
            }

            $this->view->currentProjet = $currentProjet;
        } catch (Exception $e) {
            $this->view->error = $e->getMessage();
            $filtresDynamiques = [];
        }

        // ============================
        // 5. Envoi des données à la vue
        // ============================
        $this->view->projets = $projets;
        $this->view->offres = $offres;
        $this->view->page = $page;
        $this->view->perPage = $perPage;
        $this->view->filtresDynamiques = $filtresDynamiques;
        $this->view->params = $_GET;
        $this->view->totalOffres = $totalOffres;

        // ✅ Le partial peut maintenant récupérer :
        // currentProjet, offresData, metiersData, servicesData, entreprisesData,
        // totalOffreData, totalMetiersData, totalServicesData, totalEntreprisesData
    }



    /** Liste et recherche de services */
    public function serviceaccompagnementAction()
    {


        //    $modelfrancetravalCommune = new Application_Model_Offrefrancetravail();
        // $insee = $modelfrancetravalCommune->getCommune();
        // $codeCommune = $this->_getParam('code_commune', $insee);

        $nomCommune = $this->_getParam('commune', null); // champ que l'utilisateur remplit
        $codeCommune = null;


        $theme       = $this->_getParam('theme', null);
        $type        = $this->_getParam('type', null);
        $source      = $this->_getParam('source', null);
        $q           = $this->_getParam('q', null);
        $page        = max(1, (int)$this->_getParam('page', 1));
        $perPage     = 20;
        $themeValue = $this->_getParam('themeValue');
        $typeValue = $this->_getParam('typeValue');

        $model = new Application_Model_Datainclusion();

        $themesRecuperer   = $model->getRefThematique();

        $typeRecuperer = $model->getTypeServices();
        $allTypes = [];
        foreach ($typeRecuperer as $t) {
            // si ta source fournit un code (recommandé), on l'utilise comme clé
            if (!empty($t['code'])) {
                $allTypes[$t['code']] = $t['label'];   // ex: 'ACCOMP' => 'Accompagnement'
            } else {
                // sinon, on met le label comme clé aussi (pas idéal mais tolérable)
                $allTypes[$t['label']] = $t['label'];
            }
        }

        $sourcesData = $model->getSources();
        $sourcesRecuperer = [];
        foreach ($sourcesData as $s) {
            // clé = slug (utilisée dans les requêtes / filtres)
            // valeur = nom (affichée dans le select)
            $sourcesRecuperer[$s['slug']] = $s['nom'];
        }

        try {
            // --- Recherche sans filtre pour récupérer toutes les thématiques/types/structures ---
            $allServicesResp = $model->searchServices(['page' =>  $page, 'perPage' => $perPage]);
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
                'perPage' => $perPage,
                'exclure_doublons' => true,
                // 'score_qualite_minimum' => 1
            ];

            if ($codeCommune) $params['code_commune'] = $codeCommune;
            if ($theme)       $params['thematiques']  = $theme;
            if ($type)        $params['types']        = $type;
            if ($source)      $params['sources']      = $source;
            if ($q)           $params['q']            = $q;

            $resp = $model->searchServices($params);
            // var_dump($resp);
            // exit;

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
                    'date_maj'    => $s['date_maj'] ?? null,
                    'theme'       => $s['thematiques'] ?? [],
                    'type'        => $s['type'] ?? null,
                    'adresse'     => $s['adresse'] ?? null,
                    'source'      => $s['source'] ?? null,
                    'lien_source'      => $s['lien_source'] ?? null,
                    'lat'         => $s['latitude'] ?? null,
                    'lon'         => $s['longitude'] ?? null,
                    'commune'     => $s['commune'] ?? null,
                    'structure'   => $s['structure'] ?? null,
                    'distance'    => $s['distance'] ?? null,
                    'modes_accueil'    => $s['modes_accueil'] ?? null,
                    'publics'    => $s['publics'] ?? null,
                    'frais'    => $s['frais'] ?? null,
                    'lien_mobilisation'    => $s['lien_mobilisation'] ?? null,
                    'conditions_acces'    => $s['conditions_acces'] ?? null,

                ];

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

            //pour le partial
            // $dashboardData = $this->getDashboardData();
            // $this->view->dashboardData = $dashboardData;

            $this->view->thematique = $themesRecuperer;
            $this->view->typeValue = $typeValue;
            $this->view->allTypes = $allTypes;
            $this->view->sourcesList = $sourcesRecuperer;

            $this->view->communeName = $nomCommune;
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
            $this->view->params = $_GET;
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

        $territoireCode = $this->_getParam('territoireCode', $projet['Projet 1']['departement']);
        $codeRome = $this->_getParam('codeRome', $projet['Projet 1']['coderome']);
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

                $dynamiqueInterpr = $this->interpretDynamique((int)$valeur);

                $dynamique = [
                    'valeur'        => $valeur,
                    'periodeLib'    => $v['libPeriode'] ?? '-',
                    'datMaj'        => $v['datMaj'] ?? null,
                    'territoire'    => $v['libTerritoire'] ?? '-',
                    'departement' => $v['codeTerritoire'],
                    'activite'      => $v['libActivite'] ?? 'Marché global',
                    'valeur' => $v['valeurPrincipaleNom'],
                    'interpretation' => $dynamiqueInterpr['texte'],
                    'badge'         => $dynamiqueInterpr['badge']
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

            $this->view->dernierDemandeurs = $dernier;

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


            $this->view->statsOffres = $offreData;

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
                    $interpret = $this->interpretIndicateur($v['codeNomenclature'], $valeur);

                    switch ($v['codeNomenclature']) {
                        case 'PERSPECTIVE':
                            $tensionPrincipale = $interpret + [
                                'periodeType' => $v['codeTypePeriode'],
                                'periodeCode' => $v['codePeriode'],
                                'periodeLib'  => $v['libPeriode'],
                                'datMaj'      => $v['datMaj'],
                                'libActivite' => $v['libActivite'],
                            ];
                            break;

                        case 'INT_EMB':
                            $intensiteEmbauche = $interpret + [
                                'periodeLib'  => $v['libPeriode'],
                                'libActivite' => $v['libActivite'],
                            ];
                            break;

                        case 'MAIN_OEUVRE':
                            $manqueMainOeuvre = $interpret + [
                                'periodeLib'  => $v['libPeriode'],
                                'libActivite' => $v['libActivite'],
                            ];
                            break;

                        case 'ATTR_SALARIALE':
                            $attractiviteSalariale = $interpret + [
                                'periodeLib'  => $v['libPeriode'],
                                'libActivite' => $v['libActivite'],
                            ];
                            break;

                        case 'COND_TRAVAIL':
                            $conditionsTravail = $interpret + [
                                'periodeLib'  => $v['libPeriode'],
                                'libActivite' => $v['libActivite'],
                            ];
                            break;

                        case 'DUR_EMPL':
                            $durabiliteEmploi = $interpret + [
                                'periodeLib'  => $v['libPeriode'],
                                'libActivite' => $v['libActivite'],
                            ];
                            break;

                        case 'MISMATCH_GEO':
                            $inadEquationGeo = $interpret + [
                                'periodeLib'  => $v['libPeriode'],
                                'libActivite' => $v['libActivite'],
                            ];
                            break;
                    }
                }

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
            //pour le partial
            $dashboardData = $this->getDashboardData();
            $this->view->dashboardData = $dashboardData;

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

    private function interpretDynamique(?int $valeur): array
    {
        if ($valeur === null) {
            return ['texte' => "Non disponible", 'badge' => "secondary"];
        }

        switch ($valeur) {
            case 3:
                return ['texte' => "Très dynamique : forte croissance attendue", 'badge' => "success"];
            case 2:
                return ['texte' => "Dynamique moyenne : emploi stable, croissance modérée", 'badge' => "warning"];
            case 1:
                return ['texte' => "Faible dynamique : opportunités limitées", 'badge' => "danger"];
            default:
                return ['texte' => "Indicateur non défini", 'badge' => "secondary"];
        }
    }




    protected function interpretIndicateur(string $codeNomenclature, ?float $valeur): array
    {
        if ($valeur === null) {
            return ['texte' => "Donnée non disponible", 'badge' => "secondary"];
        }

        switch ($codeNomenclature) {

            // Indicateur de tension global
            case 'PERSPECTIVE':
                if ($valeur >= 1) return ['texte' => "Tension forte", 'badge' => "danger"];
                if ($valeur > 0) return ['texte' => "Tension modérée", 'badge' => "warning"];
                if ($valeur == 0) return ['texte' => "Tension neutre", 'badge' => "info"];
                if ($valeur > -1) return ['texte' => "Tension faible", 'badge' => "success"];
                return ['texte' => "Tension très faible", 'badge' => "secondary"];

                // Indicateurs prioritaires
            case 'INT_EMB':
                if ($valeur >= 1) return ['texte' => "Embauche rapide", 'badge' => "success"];
                if ($valeur > 0) return ['texte' => "Embauche modérée", 'badge' => "warning"];
                return ['texte' => "Embauche faible", 'badge' => "danger"];

            case 'MAIN_OEUVRE':
                if ($valeur >= 1) return ['texte' => "Forte pénurie", 'badge' => "danger"];
                if ($valeur > 0) return ['texte' => "Pénurie modérée", 'badge' => "warning"];
                return ['texte' => "Pas de pénurie", 'badge' => "success"];

                // Indicateurs secondaires
            case 'ATTR_SALARIALE':
                if ($valeur >= 0.5) return ['texte' => "Salarialement attractif", 'badge' => "success"];
                if ($valeur > 0) return ['texte' => "Légèrement attractif", 'badge' => "warning"];
                return ['texte' => "Peu attractif", 'badge' => "danger"];

            case 'COND_TRAVAIL':
                if ($valeur >= 0.5) return ['texte' => "Bonnes conditions", 'badge' => "success"];
                if ($valeur > 0) return ['texte' => "Conditions correctes", 'badge' => "warning"];
                return ['texte' => "Conditions difficiles", 'badge' => "danger"];

            case 'DUR_EMPL':
                if ($valeur >= 0.5) return ['texte' => "Emplois stables", 'badge' => "success"];
                if ($valeur > 0) return ['texte' => "Emplois moyennement stables", 'badge' => "warning"];
                return ['texte' => "Emplois précaires", 'badge' => "danger"];

            case 'MISMATCH_GEO':
                if ($valeur >= 0.5) return ['texte' => "Fort décalage géographique", 'badge' => "danger"];
                if ($valeur > 0) return ['texte' => "Décalage modéré", 'badge' => "warning"];
                return ['texte' => "Décalage faible", 'badge' => "success"];

            default:
                return ['texte' => "Valeur : " . $valeur, 'badge' => "secondary"];
        }
    }

    public function enregistreroffreAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $data = $this->getRequest()->getPost();

        if (empty($data['libelle'])) {
            return $this->_helper->json([
                'success' => false,
                'message' => 'Données manquantes'
            ]);
        }

        try {
            $tableFrancetravail = new Zend_Db_Table('francetravail');

            $insertData = [
                'module' => $data['module'] ?? 'inconnu',
                'identifiant_offre' => $data['identifiant_offre'] ?? null,
                'libelle' => $data['libelle'],
                'url' => $data['url'] ?? null,
                'latitude' => $data['latitude'] ?? $data['lat'] ?? null,
                'longitude' => $data['longitude'] ?? $data['lon'] ?? null,
                'date_enregistrement_bdd' => new Zend_Db_Expr('NOW()'),
                'dateApi' => !empty($data['dateActualisation'])
                    ? date('Y-m-d H:i:s', strtotime($data['dateActualisation']))
                    : (!empty($data['dateCreation'])
                        ? date('Y-m-d H:i:s', strtotime($data['dateCreation']))
                        : null),
                'projet' => $data['projet'] ?? null,
                'identifiant_offre' => $data['identifiant_offre'] ?? null,

            ];

            $tableFrancetravail->insert($insertData);

            return $this->_helper->json(['success' => true]);
        } catch (Exception $e) {
            return $this->_helper->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    // //POUR LES TOTAUX GENERAUX DES PROJETS
    // public function tableaudebordAction()
    // {
    //     // Récupération des données via la méthode privée
    //     $dashboardData = $this->getDashboardData();

    //     // Assignation des variables à la vue
    //     foreach ($dashboardData as $key => $value) {
    //         $this->view->$key = $value;
    //     }
    // }

    // private function getDashboardData()
    // {
    //     $model = new Application_Model_Offrefrancetravail();

    //     return [
    //         'offresData' => $model->getAllOffres(),
    //         'metiersData' => $model->getAllMetiers(),
    //         'servicesData' => $model->getAllServices(),
    //         'entreprisesData' => $model->getAllEntreprises(),
    //         'totalOffreData' => $model->totalOffres(),
    //         'totalServicesData' => $model->totalServices(),
    //         'totalMetiersData' => $model->totalMetiers(),
    //         'totalEntreprisesData' => $model->totalEntreprises(),
    //     ];
    // }

    public function tableaudebordAction()
    {
        // Récupérer le projet sélectionné depuis l'URL (default = Projet 1)
        $projet = $this->_getParam('projet', 'Projet 1');

        // Dashboard data
        $dashboardData = $this->getDashboardData($projet);

        foreach ($dashboardData as $key => $value) {
            $this->view->$key = $value;
        }

        $this->view->currentProjet = $projet; // utile pour afficher le projet courant
    }




    private function getDashboardData($projet)
    {
        $model = new Application_Model_Offrefrancetravail();

        if ($projet === 'sans-projet') {
            return [
                'offresData' => $model->getAllOffresSansProjet('offres'),
                'metiersData' => $model->getAllOffresSansProjet('fiches'),
                'servicesData' => $model->getAllOffresSansProjet('services'),
                'entreprisesData' => $model->getAllOffresSansProjet('entreprisereccrutement'),

                'totalOffreData' => $model->totalModuleSansProjet('offres'),
                'totalServicesData' => $model->totalModuleSansProjet('services'),
                'totalMetiersData' => $model->totalModuleSansProjet('fiches'),
                'totalEntreprisesData' => $model->totalModuleSansProjet('entreprisereccrutement'),
            ];
        }

        // Projet classique
        return [
            'offresData' => $model->getAllOffresByProjetByModule($projet, 'offres'),
            'metiersData' => $model->getAllOffresByProjetByModule($projet, 'fiches'),
            'servicesData' => $model->getAllOffresByProjetByModule($projet, 'services'),
            'entreprisesData' => $model->getAllOffresByProjetByModule($projet, 'entreprisereccrutement'),

            'totalOffreData' => $model->totalModulebyProjet($projet, 'offres'),
            'totalServicesData' => $model->totalModulebyProjet($projet, 'services'),
            'totalMetiersData' => $model->totalModulebyProjet($projet, 'fiches'),
            'totalEntreprisesData' => $model->totalModulebyProjet($projet, 'entreprisereccrutement'),
        ];
    }


    /**
     * Requête AJAX : renvoie les données JSON selon le module (offres, métiers, services…)
     */
    public function getdataAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $type = $this->_getParam('type');
        $projet = $this->_getParam('projet', null);

        $model = new Application_Model_Offrefrancetravail();

        if ($projet === 'sans-projet' || empty($projet)) {
            // Données sans projet
            switch ($type) {
                case 'offres':
                    $data = $model->getAllOffresSansProjet('offres');
                    break;
                case 'metiers':
                    $data = $model->getAllOffresSansProjet('fiches');
                    break;
                case 'services':
                    $data = $model->getAllOffresSansProjet('services');
                    break;
                case 'entreprises':
                    $data = $model->getAllOffresSansProjet('entreprisereccrutement');
                    break;
                default:
                    $data = [];
            }
        } else {
            // Projet classique
            switch ($type) {
                case 'offres':
                    $data = $model->getAllOffresByProjetByModule($projet, 'offres');
                    break;
                case 'metiers':
                    $data = $model->getAllOffresByProjetByModule($projet, 'fiches');
                    break;
                case 'services':
                    $data = $model->getAllOffresByProjetByModule($projet, 'services');
                    break;
                case 'entreprises':
                    $data = $model->getAllOffresByProjetByModule($projet, 'entreprisereccrutement');
                    break;
                default:
                    $data = [];
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }


    /**
     * (Optionnel) Requête AJAX pour supprimer toutes les entrées d’un projet
     */
    public function deleteitemAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $id = (int) $this->_getParam('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            return;
        }

        $model = new Application_Model_Offrefrancetravail();
        $deleted = $model->deleteItem($id);

        echo json_encode([
            'success' => (bool)$deleted,
            'message' => $deleted ? 'Élément supprimé avec succès.' : 'Élément introuvable.'
        ]);
    }

    public function getdashboarddataAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $projet = $this->_getParam('projet', 'Projet 1');

        $data = $this->getDashboardData($projet); // même méthode que dans indexAction

        echo json_encode($data);
    }
}
