<?php
class Exo1Controller extends Zend_Controller_Action
{
    public function prenomAction()
    {
        // Récupérer le paramètre "prenom" dans l'URL
        $prenom = $this->_getParam('arg1', 'inconnu');
        // fonctionne en clé =valeur donc arg1 est la clé et ce qui viendra après dans l'url /... sera la valeur afficher dans le navigateur

        // Passer à la vue
        $this->view->prenom = $prenom;
    }
}
