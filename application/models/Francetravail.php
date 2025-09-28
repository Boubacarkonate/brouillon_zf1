<?php

class Application_Model_Francetravail
{


    protected $clientId     = 'PAR_testapi_19b477525c48be9f209ef6ecf3d32c5dd263b49155428a75a3fac7c3d1cf0622';
    protected $clientSecret = '995a6c8f95c51ce4910d62046da086933f5d38bc2c7b2193f35ca6be3c819598';
    protected $tokenUrl     = 'https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=/partenaire';
    protected $apiBaseUrl   = 'https://api.francetravail.io/partenaire/offresdemploi/v2';
    protected $scope        = 'api_offresdemploiv2 o2dsoffre';
    protected $apiBaseUrlCompetence   = 'https://api.francetravail.io/partenaire/rome-competences/v1/competences';
    protected $scopeCompetence        = 'api_rome-competencesv1 nomenclatureRome';
    protected $accessToken;
    protected $apiBaseUrlFicheMetier   = 'https://api.francetravail.io/partenaire/rome-fiches-metiers';
    protected $scopeFicheMetier        = 'api_rome-fiches-metiersv1 nomenclatureRome';
    protected $accessTokenFicheMetier;
    protected $tokenExpiresAt;
    protected $tokenCompetenceExpiresAt;
    protected $tokenFicheMetierExpiresAt;
    protected $accessTokenCompetence;

    // === Instarlink (Match via Soft Skills) ===
    // protected $instarlinkApiId     = 'TON_API_ID';
    // protected $instarlinkApiKey    = 'TON_API_KEY';
    // protected $instarlinkBaseUrl   = 'https://dev.instarlink.com/api/v1/professions/job_skills';





    public function getAccessToken()
    {
        if ($this->accessToken && $this->tokenExpiresAt && time() < $this->tokenExpiresAt) {
            error_log("[FranceTravail] Utilisation du token existant");
            return $this->accessToken;
        }

        error_log("[FranceTravail] Demande d'un nouveau token");

        $client = new Zend_Http_Client($this->tokenUrl);
        $client->setMethod(Zend_Http_Client::POST);
        $client->setHeaders(['Content-Type' => 'application/x-www-form-urlencoded']);
        $client->setParameterPost([
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->scope
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
            error_log("[FranceTravail] Erreur Token : " . $response->getBody());
            throw new Exception("Erreur Token : " . $response->getBody());
        }

        $data = json_decode($response->getBody(), true);
        if (empty($data['access_token'])) {
            error_log("[FranceTravail] Impossible de récupérer le token : " . $response->getBody());
            throw new Exception("Impossible de récupérer le token : " . $response->getBody());
        }

        $this->accessToken = $data['access_token'];

        if (!empty($data['expires_in'])) {
            $this->tokenExpiresAt = time() + (int) $data['expires_in'] - 30;
        }

        error_log("[FranceTravail] Token récupéré avec succès");
        return $this->accessToken;
    }

    /* france travail widget */
    public function getTokenWidget()
    {
        return $this->getAccessToken();
    }

    /* france travail personnalisé */
    public function searchOffres(array $params = [])
    {
        $token = $this->getAccessToken();

        error_log("[FranceTravail] Recherche offres avec params : " . json_encode($params));

        $apiParams = [];
        $allowedParams = [
            'motsCles',
            'departement',
            'distance',
            'codeROME',
            'commune',
            'accesTravailleurHandicape',
            'origineOffre',
            'natureContrat',
            'typeContrat',
            'qualification',
            'experience',
            'dureeHebdo',
            'salaireMin',
            'salaireMax',
            'offresManqueCandidats',
            'offresEures',
            'publieeDepuis',
            'minCreationDate',
            'maxCreationDate',
            'minPublicationDate',
            'maxPublicationDate',
            'minModificationDate',
            'maxModificationDate',
            'agregation',
            'niveauFormations',
            'secteursActivites'
        ];

        // On ajoute automatiquement l'agrégation pour récupérer typeContrat
        if (empty($params['agregation'])) {
            $params['agregation'] = ['typeContrat'];
        }

        foreach ($allowedParams as $key) {
            if (!empty($params[$key])) {
                $apiParams[$key] = $params[$key];
            }
        }

        $perPage = isset($params['perPage']) ? (int)$params['perPage'] : 20;
        $page    = isset($params['page']) ? (int)$params['page'] : 0;
        $start   = $page * $perPage;
        $end     = $start + $perPage - 1;
        $apiParams['range'] = "$start-$end";

        error_log("[FranceTravail] Params API préparés : " . json_encode($apiParams));

        $client = new Zend_Http_Client($this->apiBaseUrl . '/offres/search');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json'
        ]);
        $client->setParameterGet($apiParams);
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
            error_log("[FranceTravail] Erreur API : " . $response->getBody());
            throw new Exception("Erreur API : " . $response->getBody());
        }

        $data = json_decode($response->getBody(), true);
        error_log("[FranceTravail] Réponse API reçue : " . json_encode($data));


        // Récupération automatique des valeurs valides typeContrat
        $typeContratValues = [];
        if (!empty($data['filtresPossibles'])) {
            foreach ($data['filtresPossibles'] as $filtre) {
                if ($filtre['filtre'] === 'typeContrat') {
                    foreach ($filtre['agregation'] as $val) {
                        $typeContratValues[] = $val['valeurPossible'];
                    }
                }
            }
        }
        $data['typeContratValues'] = $typeContratValues;

        return $data;
    }


    public function getReferentiel(string $type): array
    {
        $token = $this->getAccessToken(); // ou récupère ton token depuis le modèle
        $url = "https://api.francetravail.io/partenaire/offresdemploi/v2/referentiel/$type";

        $client = new Zend_Http_Client($url);
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json'
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
            error_log("[FranceTravail] Erreur API référentiel $type : " . $response->getBody());
            return [];
        }

        $data = json_decode($response->getBody(), true);
        // transformer en tableau code => libellé
        $referentiel = [];
        foreach ($data as $item) {
            $referentiel[$item['code']] = $item['libelle'] ?? $item['code'];
        }

        return $referentiel;
    }


    public function getAccessTokenCompetence()
    {
        if ($this->accessTokenCompetence && $this->tokenCompetenceExpiresAt && time() < $this->tokenCompetenceExpiresAt) {
            error_log("[FranceTravail] Utilisation du token existant");
            return $this->accessTokenCompetence;
        }

        error_log("[FranceTravail] Demande d'un nouveau token");

        $client = new Zend_Http_Client($this->tokenUrl);
        $client->setMethod(Zend_Http_Client::POST);
        $client->setHeaders(['Content-Type' => 'application/x-www-form-urlencoded']);
        $client->setParameterPost([
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->scopeCompetence
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
            error_log("[FranceTravail] Erreur Token : " . $response->getBody());
            throw new Exception("Erreur Token : " . $response->getBody());
        }

        $data = json_decode($response->getBody(), true);
        if (empty($data['access_token'])) {
            error_log("[FranceTravail] Impossible de récupérer le token : " . $response->getBody());
            throw new Exception("Impossible de récupérer le token : " . $response->getBody());
        }

        $this->accessTokenCompetence = $data['access_token'];

        if (!empty($data['expires_in'])) {
            $this->tokenCompetenceExpiresAt = time() + (int) $data['expires_in'] - 30;
        }

        error_log("[FranceTravail] Token récupéré avec succès");
        return $this->accessTokenCompetence;
    }

    /* france travail widget */
    public function getTokenWidgetcompetence()
    {
        return $this->getAccessTokenCompetence();
    }

    public function searchCompetence(array $params = [])
    {
        $token = $this->getAccessTokenCompetence();

        error_log("[FranceTravail] Recherche compétences avec params : " . json_encode($params));

        $apiParams = [];
        $allowedParams = ['libelle', 'code'];
        foreach ($allowedParams as $key) {
            if (!empty($params[$key])) {
                $apiParams[$key] = $params[$key];
            }
        }

        $client = new Zend_Http_Client($this->apiBaseUrlCompetence . '/competence');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json'
        ]);
        $client->setParameterGet($apiParams);
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
            error_log("[FranceTravail] Erreur API Compétence : " . $response->getBody());
            throw new Exception("Erreur API Compétence : " . $response->getBody());
        }

        $data = json_decode($response->getBody(), true);
        error_log("[FranceTravail] Réponse API compétences : " . json_encode($data));
        return $data;
    }

    public function tokenFicheRome()
    {
        if ($this->accessTokenFicheMetier && $this->tokenFicheMetierExpiresAt && time() < $this->tokenFicheMetierExpiresAt) {
            error_log("[FranceTravail] Utilisation du token existant");
            return $this->accessTokenFicheMetier;
        }

        error_log("[FranceTravail] Demande d'un nouveau token");

        $client = new Zend_Http_Client($this->tokenUrl);
        $client->setMethod(Zend_Http_Client::POST);
        $client->setHeaders(['Content-Type' => 'application/x-www-form-urlencoded']);
        $client->setParameterPost([
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->scopeFicheMetier
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
            error_log("[FranceTravail] Erreur Token : " . $response->getBody());
            throw new Exception("Erreur Token : " . $response->getBody());
        }

        $data = json_decode($response->getBody(), true);
        if (empty($data['access_token'])) {
            error_log("[FranceTravail] Impossible de récupérer le token : " . $response->getBody());
            throw new Exception("Impossible de récupérer le token : " . $response->getBody());
        }

        $this->accessTokenFicheMetier = $data['access_token'];

        if (!empty($data['expires_in'])) {
            $this->tokenFicheMetierExpiresAt = time() + (int) $data['expires_in'] - 30;
        }

        error_log("[FranceTravail] Token récupéré avec succès");
        return $this->accessTokenFicheMetier;
    }


    public function getAccessTokenFicheRome()
    {
        return $this->tokenFicheRome();
    }
    public function ficheMetier(array $params = [])
    {
        $codeMetier = 'M1607'; // tu pourras le passer en paramètre ensuite si tu veux

        // Récupération du token
        $token = $this->getAccessTokenFicheRome();
        error_log("[FranceTravail] Token pour fiche-metier : " . substr($token, 0, 10) . '...');

        // Appel API fiche-metier
        $client = new Zend_Http_Client($this->apiBaseUrlFicheMetier . '/v1/fiches-rome/fiche-metier/' . $codeMetier);
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json'
        ]);
        $client->setParameterGet($params);
        $client->setConfig([
            'timeout' => 30,
            'adapter' => 'Zend_Http_Client_Adapter_Curl'
        ]);

        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new Exception("Erreur API fiche-metier : " . $response->getBody());
        }

        $data = json_decode($response->getBody(), true);
        error_log("[FranceTravail] Réponse fiche-metier : " . substr($response->getBody(), 0, 200) . '...');
        return $data;
    }

    protected function getAccessTokenSkills()
    {
        $client = new Zend_Http_Client('https://entreprise.francetravail.io/partenaire/oauth2/token');
        $client->setHeaders(['Content-Type' => 'application/x-www-form-urlencoded']);
        $client->setParameterPost([
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'api_matchviasoftskillsv1'
        ]);

        $response = $client->request('POST');
        if ($response->isSuccessful()) {
            $data = json_decode($response->getBody(), true);
            return $data['access_token'] ?? null;
        }

        throw new Exception("Impossible d'obtenir le token OAuth : " . $response->getStatus() . ' ' . $response->getMessage());
    }

    public function fetchSoftSkillsByRome($codeRome)
    {
        $token = $this->getAccessTokenSkills();

        $client = new Zend_Http_Client('https://api.francetravail.io/partenaire/matchviasoftskills/v1/professions/job_skills');
        $client->setHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json'
        ]);
        $client->setParameterGet(['code' => $codeRome]);

        $response = $client->request('POST');
        if (!$response->isSuccessful()) {
            throw new Exception("Erreur API France Travail : " . $response->getStatus() . ' ' . $response->getMessage());
        }

        return json_decode($response->getBody(), true);
    }
}
