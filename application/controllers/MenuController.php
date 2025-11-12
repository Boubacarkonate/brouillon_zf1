<?php

class MenuController extends Zend_Controller_Action
{
    public function init()
    {
        // Fake role pour test
        $userRole = 'admin'; // change à 'guest' pour simuler un utilisateur normal
        $menuItems = [];
        // Menu de base
        $menuItems[] =
            ['label' => 'Accueil', 'url' => '/zf1/public/index/index'];

        // Menu réservé à l’admin
        if ($userRole === 'admin') {
            $menuItems[] = ['label' => 'Module1', 'url' => '/zf1/public/menu/module1'];
            $menuItems[] = ['label' => 'Module2', 'url' => '/zf1/public/menu/module2'];
        }

        // Menu visible par tous
        $menuItems[] = ['label' => 'Test', 'url' => '/zf1/public/menu/test'];
        if ($userRole === 'admin') {
            $menuItems[] = ['label' => 'ModuleAdmin', 'url' => '/zf1/public/menu/moduleAdmin'];
        }
        $menuItems[] = ['label' => 'Test2', 'url' => '/zf1/public/menu/test2'];


        // On passe le menu filtré à la vue
        $this->view->menuItems = $menuItems;
    }

    public function indexAction()
    {
        // Ici, on peut ajouter d'autres variables spécifiques à index si nécessaire
    }

    public function module1Action()
    {
        $this->view->message = 'Je suis le premier module';
    }

    public function module2Action()
    {
        $this->view->message = 'Je suis le deuxième module';
    }
}
