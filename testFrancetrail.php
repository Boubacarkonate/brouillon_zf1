<?php

// MODEL

/**
 * getAccessToken()
 *→ Fait une requête POST à l’API d’authentification pour obtenir un access_token.
 *→ Stocke le token dans $this->accessToken.

 *searchOffres($params)
 *→ Vérifie si on a déjà un token, sinon en génère un.
 *→ Fait un appel GET sur /offres/search avec les paramètres passés (par ex. motsCles=php).
 *→ Retourne les résultats JSON sous forme de tableau.
 */
class Application_Model_Francetravail
{
    // 🔑 Identifiants fournis par France Travail (à récupérer dans ton espace développeur)
    protected $clientId     = 'PAR_testapi_19b477525c48be9f209ef6ecf3d32c5dd263b49155428a75a3fac7c3d1cf0622';
    protected $clientSecret = '995a6c8f95c51ce4910d62046da086933f5d38bc2c7b2193f35ca6be3c819598';

    // 🔗 URL pour demander un token d’accès
    protected $tokenUrl   = 'https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=/partenaire';

    // 🔗 Base de l’API France Travail (ici, les offres d’emploi v2)
    protected $apiBaseUrl = 'https://api.francetravail.io/partenaire/offresdemploi/v2';

    // 📜 Les scopes autorisés pour accéder à cette API (doivent correspondre à ceux de ton espace dev)
    protected $scope       = 'api_offresdemploiv2 o2dsoffre';

    // 🔐 Token temporaire (généré par l’API OAuth2 et stocké ici pour réutilisation)
    protected $accessToken;

    /**
     * ⚡ Récupère un token OAuth2 (obligatoire avant tout appel d’API)
     */
    public function getAccessToken()
    {
        // On crée un client HTTP pour appeler l’endpoint de génération du token
        $client = new Zend_Http_Client($this->tokenUrl);
        $client->setMethod(Zend_Http_Client::POST);

        // On définit les en-têtes (type de contenu attendu par l’API)
        $client->setHeaders(array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        ));

        // On passe les paramètres nécessaires à OAuth2
        $client->setParameterPost(array(
            'grant_type'    => 'client_credentials', // type d’authentification
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => $this->scope
        ));

        // Config réseau + TLS sécurisé (ici on force TLS 1.2)
        $client->setConfig(array(
            'timeout'  => 30, // délai max en secondes
            'adapter'  => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(
                CURLOPT_SSL_VERIFYPEER => true, // vérifie le certificat SSL
                CURLOPT_SSL_VERIFYHOST => 2,    // vérifie le nom de domaine
                CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2 // force TLS 1.2
            )
        ));

        // ⚡ Envoi de la requête au serveur
        $response = $client->request();

        // Si la réponse n’est pas un succès (HTTP 200), on lève une exception
        if (!$response->isSuccessful()) {
            throw new Exception("Erreur Token : " . $response->getBody());
        }

        // Décodage du JSON en tableau PHP
        $data = json_decode($response->getBody(), true);

        // Récupération du token (ou null si inexistant)
        $this->accessToken = $data['access_token'] ?? null;

        // Si pas de token, on lève une erreur
        if (!$this->accessToken) {
            throw new Exception("Impossible de récupérer le token : " . $response->getBody());
        }

        // Retourne le token utilisable dans les appels API
        return $this->accessToken;
    }

    /**
     * 🔎 Recherche des offres d’emploi
     * @param array $params Liste des critères de recherche (ex: motsCles, range, etc.)
     */
    public function searchOffres(array $params = array())
    {
        // Si on n’a pas encore de token, on va le chercher
        if (!$this->accessToken) {
            $this->getAccessToken();
        }

        // Création d’un client HTTP pointant sur l’endpoint de recherche
        $client = new Zend_Http_Client($this->apiBaseUrl . '/offres/search');
        $client->setMethod(Zend_Http_Client::GET);

        // En-têtes obligatoires (authentification + JSON attendu)
        $client->setHeaders(array(
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept'        => 'application/json'
        ));

        // Ajout des paramètres de recherche passés à la fonction
        $client->setParameterGet($params);

        // Config réseau + TLS sécurisé (comme pour le token)
        $client->setConfig(array(
            'timeout'  => 30,
            'adapter'  => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2
            )
        ));

        // ⚡ Exécution de la requête
        $response = $client->request();

        // Si l’API renvoie une erreur (ex : token invalide, mauvais param…), on arrête
        if (!$response->isSuccessful()) {
            throw new Exception("Erreur API : " . $response->getBody());
        }

        // Retourne la réponse sous forme de tableau associatif
        return json_decode($response->getBody(), true);
    }
}


// CONTROLLER
class FrancetravailnewController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $api = new Application_Model_Francetravail();

        try {
            $offres = $api->searchOffres(array(
                'codeROME'   => 'M1805', // Exemple: métiers de l'informatique
                'departement' => '75',
                'limit'      => 5
            ));

            $this->view->offres = $offres['resultats'];
        } catch (Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }
}


// VUE
if (!empty($this->error)): ?>
    <p style="color:red;">Erreur : <?php echo $this->escape($this->error); ?></p>
<?php else: ?>
    <h2>Offres trouvées :</h2>
    <ul>
        <?php foreach ($this->offres as $offre): ?>
            <li>
                <strong><?php echo $this->escape($offre['intitule']); ?></strong><br>
                <?php echo $this->escape($offre['lieuTravail']['libelle']); ?><br>
                <a href="<?php echo $this->escape($offre['origineOffre']['urlOrigine']); ?>" target="_blank">Voir l'offre</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>