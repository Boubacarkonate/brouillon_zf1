<?php
class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->view->message = "Hello ZF1-Future on PHP 8.2!";
    }
}
