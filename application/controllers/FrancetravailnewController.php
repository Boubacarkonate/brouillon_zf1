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
    public function marchetravaillocalAction()
    {

        // // Token widget
        // $this->view->francetravailToken = $token;

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
                "codeTypePeriode"    => $periodeRecherchee,
                "dernierePeriode"    => true
            ];

            $dyn = $model->getDynamiqueEmploi($paramsDynamique);

            $dynamique = null;

            if (!empty($dyn['listeValeursParPeriode'])) {
                $v = $dyn['listeValeursParPeriode'][0];

                // Sécurisation pour éviter les undefined / null
                $valeur = $v['valeurPrincipaleTaux']
                    ?? $v['valeurPrincipaleNombre']
                    ?? $v['valeurPrincipaleMontant']
                    ?? null;

                $dynamique = [
                    'valeur'        => $valeur,
                    'periodeLib'    => $v['libPeriode'] ?? '-',
                    'datMaj'        => $v['datMaj'] ?? null,
                    'territoire'    => $v['libTerritoire'] ?? '-',
                    'activite'      => $v['libActivite'] ?? 'Marché global',
                    'interpretation' => $this->interpretDynamique($valeur)
                ];
            }

            $this->view->dynamique = $dynamique;



            // --- Demandeurs d'emploi ---
            $paramsDemandeurs = [
                "codeTypeTerritoire"   => "DEP",
                "codeTerritoire"       => $territoireCode,
                "codeTypeActivite"     => "ROME",
                "codeActivite"         => $codeRome,
                "codeTypePeriode"      => $periodeRecherchee,
                "codeTypeNomenclature" => "CATCAND",
                "dernierePeriode"      => false
            ];

            $dem = $model->getDemandeurs($paramsDemandeurs);

            $demandeursData = [];
            foreach ($dem['listeValeursParPeriode'] as $v) {
                $demandeursData[] = [
                    'periodeLib'    => $v['libPeriode'],
                    'datMaj'        => $v['datMaj'],
                    'territoire'    => $v['libTerritoire'],
                    'activite'      => $v['libActivite'],
                    'categorie'     => $v['libNomenclature'],
                    'nombre'        => $v['valeurPrincipaleNombre'] ?? null,
                    'pourcentage'   => $v['valeurSecondairePourcentage'] ?? null,
                    'caracteristiques' => $v['listeValeurParCaract'] ?? []
                ];
            }

            $this->view->demandeurs = $demandeursData;
            $this->view->dernierDemandeurs = !empty($demandeursData) ? end($demandeursData) : null;


            // --- Embauches ---
            $paramsEmbauches = [
                "codeTypeTerritoire"   => "DEP",
                "codeTerritoire"       => $territoireCode,
                "codeTypeActivite"     => "ROME",
                "codeActivite"         => $codeRome,
                "codeTypePeriode"      => $periodeRecherchee,
                "codeTypeNomenclature" => "CATCANDxDUREEEMP",
                "dernierePeriode"      => false
            ];

            $emb = $model->getEmbauches($paramsEmbauches);

            $embauchesData = [];
            foreach ($emb['listeValeursParPeriode'] as $v) {
                $embauchesData[] = [
                    'periodeLib'    => $v['libPeriode'],
                    'datMaj'        => $v['datMaj'],
                    'territoire'    => $v['libTerritoire'],
                    'activite'      => $v['libActivite'],
                    'categorie'     => $v['libNomenclature'],
                    'nombre'        => $v['valeurPrincipaleNombre'] ?? null,
                    'pourcentage'   => $v['valeurSecondairePourcentage'] ?? null,
                    'caracteristiques' => $v['listeValeurParCaract'] ?? []
                ];
            }

            $this->view->embauches = $embauchesData;
            $this->view->dernieresEmbauches = !empty($embauchesData) ? end($embauchesData) : null;
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

                // --- Filtrer l’indicateur principal ---
                $tensionPrincipale = null;

                foreach ($ten['listeValeursParPeriode'] as $v) {
                    if ($v['codeNomenclature'] === 'PERSPECTIVE') {
                        $valeur = $v['valeurPrincipaleDecimale'];

                        $tensionPrincipale = [
                            'valeur'        => $valeur,                    // valeur réelle
                            'periodeType'   => $v['codeTypePeriode'],      // ex. 'ANNEE'
                            'periodeCode'   => $v['codePeriode'],          // ex. '2024'
                            'periodeLib'    => $v['libPeriode'],           // ex. 'Année 2024'
                            'datMaj'        => $v['datMaj'],
                            'libActivite'   => $v['libActivite'],
                            'interpretation' => $this->interpretTension($valeur), // texte compréhensible
                        ];
                        break;
                    }
                }

                $this->view->tension = $tensionPrincipale;
                $this->view->tension = $tensionPrincipale;
            } catch (Exception $e) {
                error_log("[FranceTravail] Tension non disponible : " . $e->getMessage());
                $this->view->tension = null;
                $this->view->tensionError = "Données non disponibles pour ce territoire / métier";
            }



            // $data = $model->getReferentielDesIndicateurs('/v1/referentiel/indicateurs');
            $data = $model->getReferentielDesIndicateurs('/v1/referentiel/familles-indicateurs');

            $codeFamille = "DEMANDEURS"; // exemple
            $codeIndicateur = "DE_1";    // indicateur précis

            $data = $model->getReferentielDesIndicateurs("/v1/referentiel/familles-indicateurs");

            // echo "<pre>";
            // var_dump($data);
            // echo "</pre>";
            // exit;



            $this->view->codeRome = $codeRome;
            $this->view->territoireCode = $territoireCode;
            $this->view->projet = $projet;
            $this->view->periode = $periode;
        } catch (Exception $e) {
            error_log("[FranceTravail] Erreur API : " . $e->getMessage());
            $this->view->error = $e->getMessage();
        }

        // try {
        //     // --- Récupération des libellés dynamiques ---
        //     $libelleTerritoire = $model->getLibelleTerritoire($territoireCode);
        //     $libelleRome = $model->getLibelleRome($codeRome);



        //     $this->view->libelleTerritoire = $libelleTerritoire;
        //     $this->view->libelleRome = $libelleRome;

        //     // --- Ratio DE / Offres ---
        //     $ratioDEOffres = $model->calculateRatioDEOffres($this->view->demandeurs, $this->view->statsOffreEmploi);
        //     $this->view->ratioDEOffres = $ratioDEOffres;

        //     // var_dump($ratioDEOffres);
        //     // exit;
        //     // --- Top métiers pour ce territoire ---
        //     $topMetiers = $model->getTopMetiers($territoireCode, 5);
        //     $this->view->topMetiers = $topMetiers;
        // } catch (Exception $e) {
        //     error_log("[FranceTravail] Erreur complémentaire : " . $e->getMessage());
        //     $this->view->complementaryError = $e->getMessage();
        // }
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


    protected function interpretTension($valeur)
    {
        if ($valeur === null) return "Donnée non disponible";

        if ($valeur >= 1) {
            return "Tension forte";
        } elseif ($valeur > 0) {
            return "Tension modérée";
        } elseif ($valeur == 0) {
            return "Tension neutre";
        } elseif ($valeur > -1) {
            return "Tension faible";
        } else {
            return "Tension très faible";
        }
    }
}
