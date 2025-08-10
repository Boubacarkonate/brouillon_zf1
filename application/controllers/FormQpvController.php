<?php
class FormQpvController extends Zend_Controller_Action
{
    public function checkAction()
    {
        $form = new Application_Form_Qpvformulaire();
        $this->view->form = $form;
        $this->view->result = null;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $values = $form->getValues();

                $model = new Application_Model_Qpv();

                $result = $model->checkAdresseMixte(
                    $values['adresse'],
                    $values['code_postal'],
                    $values['nom_commune']
                );

                $this->view->result = $result;
            }
        }
    }
}
