<?php
class Application_Model_Qpv
{
    protected $_apiUrl = 'https://wsa.sig.ville.gouv.fr/service/georeferenceur.json';
    protected $_username = 'gouv.data@rezosocial.com';
    protected $_password = 'Rezosocial_Zfqpv2025';

    /**
     * Format WSA : numéro de voie et nom de voie séparés
     */
    public function checkAdresseWSA($num_voie, $nom_voie, $code_postal, $nom_commune, $types_quartier = ['QP'])
    {
        $auth = base64_encode($this->_username . ':' . $this->_password);

        $client = new Zend_Http_Client($this->_apiUrl);
        $client->setHeaders('Authorization', 'Basic ' . $auth);

        $params = [
            'type_adresse'          => 'WSA',
            'adresse[num_voie]'     => $num_voie,
            'adresse[nom_voie]'     => $nom_voie,
            'adresse[code_postal]'  => $code_postal,
            'adresse[nom_commune]'  => $nom_commune,
        ];

        foreach ($types_quartier as $type) {
            $params['type_quartier[]'] = $type;
        }

        $client->setParameterGet($params);

        return $this->_sendRequest($client);
    }

    /**
     * Format MIXTE : numéro + voie dans le même champ
     */
    public function checkAdresseMixte($voie_complete, $code_postal, $nom_commune, $types_quartier = ['QP'])
    {
        $auth = base64_encode($this->_username . ':' . $this->_password);

        $client = new Zend_Http_Client($this->_apiUrl);
        $client->setHeaders('Authorization', 'Basic ' . $auth);

        $params = [
            'type_adresse'          => 'MIXTE',
            'adresse[nom_voie]'     => $voie_complete,
            'adresse[code_postal]'  => $code_postal,
            'adresse[nom_commune]'  => $nom_commune,
        ];

        foreach ($types_quartier as $type) {
            $params['type_quartier[]'] = $type;
        }

        $client->setParameterGet($params);

        return $this->_sendRequest($client);
    }

    /**
     * Envoie la requête et gère la réponse JSON
     */
    protected function _sendRequest(Zend_Http_Client $client)
    {
        try {
            $response = $client->request('GET');
            if ($response->isSuccessful()) {
                return json_decode($response->getBody(), true);
            }
            error_log('Erreur API QPV : HTTP ' . $response->getStatus());
        } catch (Exception $e) {
            error_log('Erreur API : ' . $e->getMessage());
        }
        return null;
    }
}
