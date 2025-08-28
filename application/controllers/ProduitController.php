<?php
class ProduitController extends Zend_Controller_Action
{
    public function init() {}

    // Page d’accueil
    public function indexAction()
    {
        $this->view->accueil = "page d'accueil";
    }

    // Liste des produits
    public function listeproduitAction()
    {
        $produitModel = new Application_Model_Produit();
        $listeProduits = $produitModel->getListeProduits();
        $this->view->liste = $listeProduits;
    }

    // Afficher un produit par ID
    public function unproduitAction()
    {
        // On récupère le paramètre "id" dans l’URL : http://localhost/zf1/public/produit/unproduit/id/6 par exemple
        $id = $this->_getParam('id', 'aucun produit');
        $produitModel = new Application_Model_Produit();
        $unproduit = $produitModel->getOneProduit($id);
        $this->view->produit = $unproduit;
    }

    public function ajoutAction() {}

    public function modificationAction()
    {
        $id = $this->_getParam('id');
        $this->view->message = "Modification du produit : " . $id;
    }

    public function suppressionAction()
    {
        $id = $this->_getParam('id');
        $this->view->message = "Suppression du produit : " . $id;
    }
}
