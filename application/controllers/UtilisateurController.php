<?php
class UtilisateurController extends Zend_Controller_Action
{
    public function init() {}

    public function loginAction()
    {
        // Création du formulaire
        $form = new Application_Form_Login();
        $this->view->formulaire = $form; // envoyer le formulaire à la vue

        $request = $this->getRequest();

        if ($request->isPost()) {
            $formData = $request->getPost();

            if ($form->isValid($formData)) {
                $valuesForm = $form->getValues();

                $utilisateurModel = new Application_Model_Utilisateur();
                $utilisateurLogged = $utilisateurModel->authentification(
                    $valuesForm['email'],
                    $valuesForm['password']
                );

                if ($utilisateurLogged) {
                    // Stocker l'utilisateur dans la session
                    $auth = Zend_Auth::getInstance();
                    $auth->getStorage()->write($utilisateurLogged);

                    // Redirection vers la page protégée
                    return $this->_helper->redirector('listeproduit', 'produit');
                } else {
                    $this->_helper->flashMessenger->addMessage('Identifiants non valides !');
                }
            } else {
                // Remplir le formulaire avec les données saisies
                $form->populate($formData);
            }
        }

        // Messages flash pour la vue
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    public function logoutAction()
    {
        // Récupérer l'instance Zend_Auth
        $auth = Zend_Auth::getInstance();

        // Vider la session de l'utilisateur
        $auth->clearIdentity();

        // Ajouter un message flash optionnel
        $this->_helper->flashMessenger->addMessage('Vous êtes maintenant déconnecté.');

        // Rediriger vers la page de login ou autre
        $this->_helper->redirector('login', 'utilisateur');
    }




    public function listeutilisateursAction()
    {
        $utilisateurModel = new Application_Model_Utilisateur();
        $listeUtilisateur = $utilisateurModel->getUsers();
        $this->view->utilisateurs = $listeUtilisateur;
    }

    public function utilisateurAction()
    {
        $id = $this->_getParam('id');
        $utilisateurModel = new Application_Model_Utilisateur();
        $utilisateur = $utilisateurModel->getOneUser($id);

        $this->view->utilisateur = $utilisateur;
    }
}
