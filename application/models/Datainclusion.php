<?php

class Application_Model_Datainclusion
{
    protected $apiBase = 'https://api.data-inclusion.example/v1'; // adapte l'URL
    protected $apiKey = 'eyJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjoiNjhkOTY0MGU1Y2FhZmZhZDljZWFlODE0IiwidGltZSI6MTc1OTA3ODg5My42NjAzOTIzfQ.Bs9lTDWtASHrJ8S_AmiQeVQMopBTWfCECw59CN4ftD2JBL68emP08_UhQDGLUVOWuUn2xohmo7JBDYZ22gxC9Q'; // si nécessaire

    /**
     * Recherche de services.
     * Retourne ['items' => [...], 'total' => int]
     */
    public function searchServices(array $params = [])
    {
        // Prépare les params
        $query = [];
        if (!empty($params['lat']) && !empty($params['lon']) && !empty($params['radius'])) {
            $query['lat'] = $params['lat'];
            $query['lon'] = $params['lon'];
            $query['radius'] = $params['radius']; // km
        }
        if (!empty($params['theme'])) $query['theme'] = $params['theme'];
        if (!empty($params['q'])) $query['q'] = $params['q'];
        $page = (int)($params['page'] ?? 0);
        $perPage = (int)($params['perPage'] ?? 20);
        $query['page'] = $page;
        $query['per_page'] = $perPage;

        // Appel API (GET avec clé dans header ou query selon API)
        $client = new Zend_Http_Client($this->apiBase . '/services/search');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet($query);
        $client->setHeaders([
            'Accept' => 'application/json',
            'X-API-KEY' => $this->apiKey
        ]);
        $client->setConfig(['timeout' => 30, 'adapter' => 'Zend_Http_Client_Adapter_Curl']);

        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new Exception('Erreur API Data Inclusion: ' . $response->getBody());
        }

        $data = json_decode($response->getBody(), true);
        // Adapter selon le format réel ; on suppose ['items'=>..., 'total'=>...]
        $items = $data['items'] ?? $data['results'] ?? [];
        $total = $data['total'] ?? count($items);

        // Normalize minimal fields we need (id,label,lat,lon,address,theme,description,contact)
        $normalized = [];
        foreach ($items as $it) {
            $normalized[] = [
                'id' => $it['id'] ?? ($it['uid'] ?? null),
                'label' => $it['label'] ?? $it['name'] ?? null,
                'description' => $it['description'] ?? null,
                'theme' => $it['theme'] ?? null,
                'address' => $it['address'] ?? ($it['adresse'] ?? null),
                'lat' => $it['lat'] ?? ($it['location']['lat'] ?? null),
                'lon' => $it['lon'] ?? ($it['location']['lon'] ?? null),
                'contact' => $it['contact'] ?? null,
                'raw' => $it
            ];
        }

        return ['items' => $normalized, 'total' => $total];
    }

    /**
     * Détail d'un service
     */
    public function getServiceDetail(string $id)
    {
        $client = new Zend_Http_Client($this->apiBase . '/services/' . urlencode($id));
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders([
            'Accept' => 'application/json',
            'X-API-KEY' => $this->apiKey
        ]);
        $client->setConfig(['timeout' => 30, 'adapter' => 'Zend_Http_Client_Adapter_Curl']);

        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new Exception('Erreur API Data Inclusion: ' . $response->getBody());
        }
        $data = json_decode($response->getBody(), true);
        return $data;
    }
}
