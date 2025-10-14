<?php

class Application_Model_Francetravail
{


    protected $clientId     = 'PAR_testapi_19b477525c48be9f209ef6ecf3d32c5dd263b49155428a75a3fac7c3d1cf0622';
    protected $clientSecret = '995a6c8f95c51ce4910d62046da086933f5d38bc2c7b2193f35ca6be3c819598';
    protected $tokenUrl     = 'https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=/partenaire';

    protected $tokens = [];
    protected $tokenExpirations = [];

    protected $apiEndpoints = [
        'offres'        => 'https://api.francetravail.io/partenaire/offresdemploi/v2',
        'competence'    => 'https://api.francetravail.io/partenaire/rome-competences/v1/competences',
        'ficheMetier'   => 'https://api.francetravail.io/partenaire/rome-fiches-metiers/v1/fiches-rome',
        'metierRome'    => 'https://api.francetravail.io/partenaire/rome-metiers/v1/metiers',
        'matchSkills'   => 'https://api.francetravail.io/partenaire/matchviasoftskills/v1/professions/job_skills',
        'bonneBoite'    => 'https://api.francetravail.io/partenaire/labonneboite/v2',
        'marcheTravail' => 'https://api.francetravail.io/partenaire/stats-offres-demandes-emploi/v1/indicateur'
    ];

    protected $scopes = [
        'offres'        => 'api_offresdemploiv2 o2dsoffre',
        'competence'    => 'api_rome-competencesv1 nomenclatureRome',
        'ficheMetier'   => 'api_rome-fiches-metiersv1 nomenclatureRome',
        'metierRome'    => 'api_rome-metiersv1 nomenclatureRome',
        'matchSkills'   => 'api_matchviasoftskillsv1',
        'bonneBoite'    => 'api_labonneboitev2 search office',
        'marcheTravail' => 'offresetdemandesemploi api_stats-offres-demandes-emploiv1'
    ];



    /* ============================================================
       UTILITAIRES COMMUNS
       ============================================================ */

    private function getAccessToken(string $scopeKey)
    {
        if (isset($this->tokens[$scopeKey]) && time() < $this->tokenExpirations[$scopeKey]) {
            error_log("[FranceTravail][$scopeKey] Utilisation du token existant");
            return $this->tokens[$scopeKey];
        }

        error_log("[FranceTravail][$scopeKey] Récupération d’un nouveau token...");

        $client = new Zend_Http_Client($this->tokenUrl);
        $client->setMethod(Zend_Http_Client::POST);
        $client->setHeaders(['Content-Type' => 'application/x-www-form-urlencoded']);
        $client->setParameterPost([
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->scopes[$scopeKey] ?? ''
        ]);
        $client->setConfig([
            'timeout' => 30,
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => [
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2
            ]
        ]);

        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new Exception("[FranceTravail][$scopeKey] Erreur Token : " . $response->getBody());
        }

        $data = json_decode($response->getBody(), true);
        if (empty($data['access_token'])) {
            throw new Exception("[FranceTravail][$scopeKey] Token manquant : " . $response->getBody());
        }

        $this->tokens[$scopeKey] = $data['access_token'];
        $this->tokenExpirations[$scopeKey] = time() + (int)$data['expires_in'] - 30;

        return $this->tokens[$scopeKey];
    }

    private function createClient($url, $token, $method = 'GET', $isJson = false)
    {
        $client = new Zend_Http_Client($url);
        $client->setMethod($method);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json'
        ];
        if ($isJson) {
            $headers['Content-Type'] = 'application/json';
        }
        $client->setHeaders($headers);
        $client->setConfig([
            'timeout' => 30,
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => [
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2
            ]
        ]);
        return $client;
    }

    private function request($client)
    {
        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new Exception("[FranceTravail] Erreur API : " . $response->getBody());
        }
        return json_decode($response->getBody(), true);
    }

    /* ============================================================
       API : OFFRES D’EMPLOI
       ============================================================ */

    public function searchOffres(array $params = [])
    {
        $token = $this->getAccessToken('offres');

        if (empty($params['agregation'])) {
            $params['agregation'] = ['typeContrat'];
        }

        // pagination
        $perPage = (int)($params['perPage'] ?? 20);
        $page    = (int)($params['page'] ?? 0);
        $start   = $page * $perPage;
        $params['range'] = "$start-" . ($start + $perPage - 1);

        $url = $this->apiEndpoints['offres'] . '/offres/search';
        $client = $this->createClient($url, $token);
        $client->setParameterGet($params);

        $data = $this->request($client);

        // extraire typeContrat
        $data['typeContratValues'] = [];
        if (!empty($data['filtresPossibles'])) {
            foreach ($data['filtresPossibles'] as $filtre) {
                if ($filtre['filtre'] === 'typeContrat') {
                    foreach ($filtre['agregation'] as $val) {
                        $data['typeContratValues'][] = $val['valeurPossible'];
                    }
                }
            }
        }

        return $data;
    }

    public function getReferentiel(string $type)
    {
        $token = $this->getAccessToken('offres');
        $url = $this->apiEndpoints['offres'] . "/referentiel/$type";
        $client = $this->createClient($url, $token);
        $data = $this->request($client);

        $referentiel = [];
        foreach ($data as $item) {
            $referentiel[$item['code']] = $item['libelle'] ?? $item['code'];
        }
        return $referentiel;
    }

    /* ============================================================
       API : COMPÉTENCES
       ============================================================ */

    public function domaineCompetence(array $params = [])
    {
        $token = $this->getAccessToken('competence');
        $url = $this->apiEndpoints['competence'] . '/domaine-competence';
        $client = $this->createClient($url, $token);
        $client->setParameterGet($params);
        return $this->request($client);
    }

    /* ============================================================
       API : FICHE MÉTIER
       ============================================================ */

    public function ficheMetier(string $codeMetier)
    {
        $token = $this->getAccessToken('ficheMetier');
        $url = $this->apiEndpoints['ficheMetier'] . "/fiche-metier/$codeMetier";
        $client = $this->createClient($url, $token);
        return $this->request($client);
    }

    /* ============================================================
       API : MÉTIER & ROME
       ============================================================ */

    public function getMetierByCodeRome(array $params = [], string $codeRome)
    {
        $token = $this->getAccessToken('metierRome');
        $url = $this->apiEndpoints['metierRome'] . '/metier/' . urlencode($codeRome);
        $client = $this->createClient($url, $token);
        $client->setParameterGet($params);
        return $this->request($client);
    }

    /* ============================================================
       API : LA BONNE BOÎTE
       ============================================================ */

    public function getLaBonneBoite(array $params = [])
    {
        $token = $this->getAccessToken('bonneBoite');
        $url = $this->apiEndpoints['bonneBoite'] . '/recherche';
        $client = $this->createClient($url, $token);
        $client->setParameterGet($params);
        return $this->request($client);
    }

    /* ============================================================
       API : MARCHÉ DU TRAVAIL
       ============================================================ */

    private function callMarcheTravail($endpoint, $body = null, $method = 'POST')
    {
        $token = $this->getAccessToken('marcheTravail');
        $url = $this->apiEndpoints['marcheTravail'] . $endpoint;
        $client = $this->createClient($url, $token, $method, true);
        if ($body) {
            $client->setRawData(json_encode($body), 'application/json');
        }
        return $this->request($client);
    }

    public function getDynamiqueEmploi($params)
    {
        return $this->callMarcheTravail('/stat-dynamique-emploi', $params);
    }

    public function getEmbauches($params)
    {
        return $this->callMarcheTravail('/stat-embauches', $params);
    }

    public function getDemandeurs($params)
    {
        return $this->callMarcheTravail('/stat-demandeurs', $params);
    }

    public function getDemandeurs12DerniersMois($params)
    {
        return $this->callMarcheTravail('/stat-demandeurs-entrant', $params);
    }

    public function getTensionRecrutement($params)
    {
        return $this->callMarcheTravail('/stat-perspective-employeur', $params);
    }

    public function getStatsSalaire($typeTerritoire, $codeTerritoire)
    {
        $token = $this->getAccessToken('marcheTravail');
        $url = "https://api.francetravail.io/partenaire/stats-offres-demandes-emploi/v1/indicateur/salaire-rome-fap/{$typeTerritoire}/{$codeTerritoire}";
        $client = $this->createClient($url, $token);
        return $this->request($client);
    }

    public function getStatsOffreEmploi($params)
    {
        return $this->callMarcheTravail('/stat-offres', $params);
    }
}
