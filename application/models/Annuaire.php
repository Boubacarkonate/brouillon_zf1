<?php

class Application_Model_Annuaire
{
    protected $apiKey = "eyJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjoiNjhkOTY0MGU1Y2FhZmZhZDljZWFlODE0IiwidGltZSI6MTc1OTA3ODg5My42NjAzOTIzfQ.Bs9lTDWtASHrJ8S_AmiQeVQMopBTWfCECw59CN4ftD2JBL68emP08_UhQDGLUVOWuUn2xohmo7JBDYZ22gxC9Q";

    public function getInfosEntreprise($siret)
    {
        $url = "https://recherche-entreprises.api.gouv.fr/search?q={$siret}&api_key={$this->apiKey}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $result['results'][0] ?? null;
    }
}
