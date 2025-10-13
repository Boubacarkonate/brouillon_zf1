<?php
// application/controllers/WidgetController.php
class WidgetController extends Zend_Controller_Action
{
    public function prioriteAction()
    {
        $model = new Application_Model_IconeWidgetPriorite();
        $widgets = $model->getIconesPriorite();
        // var_dump($widgets);
        $this->view->widgets = $model->getIconesPriorite();
    }
}