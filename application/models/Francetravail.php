<?php
class Application_Model_Francetravail
{


    // protected $tokenUrl   = 'https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=/partenaire';
    // protected $apiBaseUrl = 'https://api.francetravail.io/partenaire/offresdemploi/v2';




    /**
     * Récupère le token OAuth2
     */
    const TOKEN_URL = "https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=/partenaire";



    public function __construct($clientId, $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        // Fichier pour stocker temporairement le token
    }

    public function getToken()
    {



        // Sinon, demander un nouveau token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::TOKEN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            "grant_type" => "client_credentials",
            "client_id" => $this->clientId,
            "client_secret" => $this->clientSecret,
            "scope" => "api_offresdemploiv2 o2dsoffre"
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Impossible d'obtenir un token France Travail : $response");
        }

        $data = json_decode($response, true);

        // Calculer l’expiration
        $data['expires_at'] = time() + $data['expires_in'] - 60; // marge de 1 min

        // Sauvegarder

        return $data['access_token'];
    }
}
