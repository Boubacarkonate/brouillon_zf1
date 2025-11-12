<?php

class UxalertesController extends Zend_Controller_Action
{
    public function indexAction()
    {
        // Simulation de types d'alertes
        $alertes_types = [
            'expirations' => 'Alertes expirations',
            'taches'      => 'Tâches en cours',
            'autres'      => 'Autres alertes',
        ];

        $param_type = $this->getRequest()->getParam('type', 'autres');
        if (!in_array($param_type, array_keys($alertes_types))) {
            throw new Exception("Cette page n'existe pas ou vous n'y avez pas accès", 404);
        }

        switch ($param_type) {
            case 'autres':
                $model_briques = new Application_Model_Briquesfake();
                $briques = $model_briques->fetchAllActives();

                $param_requete = $this->getRequest()->getParam('requete');
                $find = array_filter($briques, fn($b) => $b['requete'] == $param_requete);
                $this->view->brique = current($find) ?: current($briques) ?: null;
                $this->view->briques = $briques;
                break;

            default:
                $this->view->message = "Type d'alerte non géré dans ce test.";
        }

        $this->view->type_alerte = $param_type;
    }

    private function getInfosBrique($nom)
    {
        $model_briques = new Application_Model_Briquesfake();
        $briques = $model_briques->fetchAllActives();

        $i = array_search($nom, array_column($briques, 'requete'));
        if ($i === false) {
            throw new Exception("Cette page n'existe pas ou vous n'y avez pas accès", 404);
        }

        $nomReq = $briques[$i]['requete'];
        $typeReq = $briques[$i]['type'];

        return [$nomReq, $typeReq];
    }

    public function ouvrirreqAction()
    {
        // Désactive le rendu de vue et le layout
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $this->getResponse()->setHeader('Content-Type', 'application/json');

        try {
            $params = $this->getRequest()->getParams();

            $retour = [
                'nom' => $params["nom"] ?? '',
                'nbligpage' => 25,
                'selection' => '[]',
                'conditions' => 'false'
            ];

            if (!empty($params["nom"])) {
                $model_briques = new Application_Model_Briquesfake();
                $briques = $model_briques->fetchAllActives();

                $found = array_filter($briques, fn($b) => $b['requete'] === $params['nom']);
                $brique = current($found);

                if ($brique) {
                    if ($brique['type'] === 'requete') {
                        $retour['colonnes'] = ['ID', 'Nom', 'Valeur'];
                        $retour['infos'] = "Initialisation de la brique {$brique['requete']}";
                    }
                } else {
                    throw new Exception("Brique non trouvée", 404);
                }
            }

            echo json_encode($retour);
        } catch (Exception $e) {
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
        }

        return true;
    }

    public function executereqdatatableAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $this->getResponse()->setHeader('Content-Type', 'application/json');

        try {
            $params = $this->getRequest()->getParams();

            if (empty($params['requete'])) {
                throw new Exception("Paramètre 'requete' manquant", 400);
            }

            $model_briques = new Application_Model_Briquesfake();
            $briques = $model_briques->fetchAllActives();

            $found = array_filter($briques, fn($b) => $b['requete'] === $params['requete']);
            $brique = current($found);

            if (!$brique) {
                throw new Exception("Brique inconnue", 404);
            }

            $params['data'] = [
                ['ID' => 1, 'Nom' => 'Exemple', 'Valeur' => 'Test'],
                ['ID' => 2, 'Nom' => 'Autre', 'Valeur' => 'Démo']
            ];

            echo json_encode($params);
        } catch (Exception $e) {
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
        }

        return true;
    }
}
