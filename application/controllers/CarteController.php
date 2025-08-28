<?php

class CarteController extends Zend_Controller_Action
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        // Liste des adresses
        $addresses = [
            ['name' => 'Utilisateur 1', 'address' => '10 Downing Street, London, UK'],
            ['name' => 'Utilisateur 2', 'address' => '1600 Pennsylvania Avenue NW, Washington, DC, USA'],
            ['name' => 'Utilisateur 3', 'address' => 'Eiffel Tower, Paris, France']
        ];

        $locations = [];
        foreach ($addresses as $user) {
            $coords = $this->geocodeAddressOSM($user['address']);
            if ($coords) {
                $locations[] = [
                    'name' => $user['name'],
                    'lat'  => $coords['lat'],
                    'lng'  => $coords['lng']
                ];
            }
        }

        $this->view->locations = $locations;
    }

    private function geocodeAddressOSM($address)
    {
        $address = urlencode($address);
        $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";

        $opts = [
            "http" => [
                "header" => "User-Agent: MyZF1App/1.0\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $resp_json = file_get_contents($url, false, $context);
        $resp = json_decode($resp_json, true);

        if (!empty($resp)) {
            return [
                'lat' => $resp[0]['lat'],
                'lng' => $resp[0]['lon']
            ];
        }

        return false;
    }
}
