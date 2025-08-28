<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Application_',
            'basePath'  => APPLICATION_PATH,
        ));
        return $autoloader;
    }

    protected function _initLayout()
    {
        Zend_Layout::startMvc(array(
            'layoutPath' => APPLICATION_PATH . '/layouts/scripts/',
            'layout'     => 'layout' // nom du fichier layout.phtml
        ));
    }
}
