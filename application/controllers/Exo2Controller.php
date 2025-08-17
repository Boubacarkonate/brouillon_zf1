<?php
class Exo2Controller extends Zend_Controller_Action
{
    public function indexAction()
    {
        $form = new Application_Form_Exo2();

        // Vérifier si on a soumis un formulaire en POST
        if ($this->getRequest()->isPost()) {

            // Récupérer les données envoyées
            $data = $this->getRequest()->getPost();

            // Validation des données
            if ($form->isValid($data)) {
                // ✅ Données valides
                $email = $form->getValue('mail');
                $this->view->message = "Merci, votre email <b>$email</b> a bien été enregistré.";
            } else {
                // ❌ Données invalides → affichage des erreurs
                $this->view->message = "Le formulaire contient des erreurs.";
            }
        }

        // Envoyer le formulaire à la vue
        $this->view->formulaire = $form;
    }
}
