<?php
class ExocrudController extends Zend_Controller_Action
{
    public function init() {}

    public function indexAction() {}
    public function listuserAction()
    {
        $model = new Application_Model_User();
        $listUser = $model->getUsers();

        $this->view->list = $listUser;
    }
    public function userAction()
    {
        $model = new Application_Model_User();

        // Récupérer le paramètre "numero" passé dans l'URL
        $id = (int) $this->_getParam('numero', 0); // 0 par défaut si rien n’est passé

        if ($id > 0) {
            $user = $model->getOneUser($id);
            $this->view->user = $user;
        } else {
            $this->view->user = null; // ou gérer une erreur / redirection
        }
    }

    public function updateuserAction() {}
    public function deleteuserAction() {}
}
