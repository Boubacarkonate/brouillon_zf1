<?php

class Application_Model_Datainclusion
{
    protected $apiBase = 'https://api-staging.data.inclusion.gouv.fr';
    protected $apiKey = 'eyJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjoiNjhkOTY0MGU1Y2FhZmZhZDljZWFlODE0IiwidGltZSI6MTc1OTA3ODg5My42NjAzOTIzfQ.Bs9lTDWtASHrJ8S_AmiQeVQMopBTWfCECw59CN4ftD2JBL68emP08_UhQDGLUVOWuUn2xohmo7JBDYZ22gxC9Q';



    protected function callApi($endpoint, $params = [])
    {
        $client = new Zend_Http_Client($this->apiBase . $endpoint);
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet($params);
        $client->setHeaders([
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ]);
        $client->setConfig(['timeout' => 30]);

        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new Exception('Erreur API Data Inclusion: ' . $response->getBody());
        }

        // Décoder JSON
        return json_decode($response->getBody(), true);
    }

    /** Recherche de services filtrés */
    public function searchServices(array $params = [])
    {
        // Pagination par défaut
        $params['perPage'] = $params['perPage'] ?? 20;
        $params['page']    = $params['page'] ?? 1;

        // Limiter aux champs utiles pour ne pas exploser la mémoire
        $params['fields'] = 'id,nom,adresse,thematiques,type,source,lat,lon';

        return $this->callApi('/api/v1/search/services', $params);
    }

    /** Récupère toutes les sources */
    public function getSources()
    {
        return $this->callApi('/api/v1/sources');
    }

    /** Détail d’un service par id */
    public function getService($id)
    {
        return $this->callApi('/api/v1/services/' . urlencode($id));
    }
}
