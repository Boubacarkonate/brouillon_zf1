<?php
class Application_Model_Francetravail
{
    protected $clientId     = 'PAR_testapi_19b477525c48be9f209ef6ecf3d32c5dd263b49155428a75a3fac7c3d1cf0622';
    protected $clientSecret = '995a6c8f95c51ce4910d62046da086933f5d38bc2c7b2193f35ca6be3c819598';

    protected $tokenUrl   = 'https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=/partenaire';
    protected $apiBaseUrl = 'https://api.francetravail.io/partenaire/offresdemploi/v2';

    protected $scope       = 'api_offresdemploiv2 o2dsoffre';
    protected $accessToken;

    /**
     * Récupère le token OAuth2
     */
    public function getAccessToken()
    {
        $client = new Zend_Http_Client($this->tokenUrl);
        $client->setMethod(Zend_Http_Client::POST);
        $client->setHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);
        $client->setParameterPost([
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->scope
        ]);

        $client->setConfig([
            'timeout'  => 30,
            'adapter'  => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => [
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2
            ]
        ]);

        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new Exception("Erreur Token : " . $response->getBody());
        }

        $data = json_decode($response->getBody(), true);
        $this->accessToken = $data['access_token'] ?? null;

        if (!$this->accessToken) {
            throw new Exception("Impossible de récupérer le token : " . $response->getBody());
        }

        return $this->accessToken;
    }

    /**
     * Recherche des offres d’emploi avec filtres
     */
    public function searchOffres(array $params = [])
    {
        if (!$this->accessToken) {
            $this->getAccessToken();
        }

        // Construire les paramètres GET selon la doc
        $apiParams = [];

        $allowedParams = [
            'motsCles',
            'departement',
            'commune',
            'distance',
            'codeROME',
            'appellation',
            'codeNAF',
            'accesTravailleurHandicape',
            'range',
            'origineOffre'
        ];

        foreach ($allowedParams as $key) {
            if (isset($params[$key]) && $params[$key] !== '') {
                $apiParams[$key] = $params[$key];
            }
        }

        $client = new Zend_Http_Client($this->apiBaseUrl . '/offres/search');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept'        => 'application/json'
        ]);
        $client->setParameterGet($apiParams);

        $client->setConfig([
            'timeout'  => 30,
            'adapter'  => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => [
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2
            ]
        ]);

        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new Exception("Erreur API : " . $response->getBody());
        }

        return json_decode($response->getBody(), true);
    }
}
