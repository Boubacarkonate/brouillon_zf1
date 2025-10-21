<?php
class SupportController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->view->message = "hello support";
    }
}
