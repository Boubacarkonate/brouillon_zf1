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
    public function getTypeServices()
    {
        $resp = $this->callApi('/api/v1/doc/types-services');


        return $resp;
    }


    public function searchServices(array $params = [])
    {
        // Pagination par défaut
        $params['size'] = $params['perPage'] ?? 20;
        $params['page']    = $params['page'] ?? 1;


        unset($params['perPage']);

        // Limiter aux champs utiles pour ne pas exploser la mémoire
        $params['fields'] = 'id,nom,adresse,thematiques,type,source,lat,lon';

        return $this->callApi('/api/v1/search/services', $params);
    }

    public function getSources()
    {
        return $this->callApi('/api/v1/sources');
    }

    public function getServices($params)
    {
        $params['code_postal'] = $params['code_postal'] ?? 75020;
        return $this->callApi('/api/v1/services', $params);
    }


    /**  */
    public function getRefThematique()
    {
        $data = $this->callApi('/api/v1/doc/thematiques');

        $themes = [];

        if (is_array($data)) {
            foreach ($data as $item) {
                $themes[$item['value']] = $item['label'];
            }
        }

        ksort($themes); // tri alphabétique 
        return $themes;
    }
}
