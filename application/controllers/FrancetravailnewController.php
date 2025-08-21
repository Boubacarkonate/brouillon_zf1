<?php
class FrancetravailnewController extends Zend_Controller_Action
{
    // $clientId = "PAR_testapi_19b477525c48be9f209ef6ecf3d32c5dd263b49155428a75a3fac7c3d1cf0622";
    // $clientSecret = "995a6c8f95c51ce4910d62046da086933f5d38bc2c7b2193f35ca6be3c819598";
    //         $url = "https://api.francetravail.io/partenaire/offresdemploi/v2/referentiel/communes";

    /**
     * Page qui affiche le widget + le sélecteur
     */
    public function indexAction()
    {
        $clientId = "PAR_testapi_19b477525c48be9f209ef6ecf3d32c5dd263b49155428a75a3fac7c3d1cf0622";
        $clientSecret = "995a6c8f95c51ce4910d62046da086933f5d38bc2c7b2193f35ca6be3c819598";

        $ft = new Application_Model_Francetravail($clientId, $clientSecret);
        $token = $ft->getToken();

        // On passe juste le token à la vue
        $this->view->francetravailToken = $token;
    }

    /**
     * Action qui sert de proxy pour récupérer les communes
     */
    public function communesAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        $clientId = "PAR_testapi_19b477525c48be9f209ef6ecf3d32c5dd263b49155428a75a3fac7c3d1cf0622";
        $clientSecret = "995a6c8f95c51ce4910d62046da086933f5d38bc2c7b2193f35ca6be3c819598";

        $ft = new Application_Model_Francetravail($clientId, $clientSecret);
        $token = $ft->getToken();

        $url = "https://api.francetravail.io/partenaire/offresdemploi/v2/referentiel/communes";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setHttpResponseCode($httpCode)
            ->setBody($response);
    }
}
