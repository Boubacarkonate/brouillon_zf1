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

        // Récupérer les messages Flash dans les autres action lors de la création, modification ou suppression
        //PS : ne pas oublier de l'ajouter à la vue. Et comme il y en a plusieurs, je dois parcourir les messages avec une boucle pour afficher le bon message
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
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

    public function ajoutAction()
    {
        // Créer une instance du formulaire
        $produitFormulaire = new Application_Form_Produitform();

        // Récupérer l'objet request
        $request = $this->getRequest();

        // Vérifier si le formulaire a été soumis
        if ($request->isPost()) {
            // Récupérer les données POST
            $formData = $request->getPost();

            // Valider le formulaire
            if ($produitFormulaire->isValid($formData)) {
                // Récupérer les valeurs validées
                $valeurs = $produitFormulaire->getValues();

                // Exemple : enregistrer le produit en base
                $produitModel = new Application_Model_Produit();
                $produitModel->insertProduit($valeurs);

                // Message de succès
                // $nouveauProduit = $produitModel->insertProduit($valeurs);
                // $this->view->message = "Produit ajouté avec succès ! ID=$nouveauProduit";
                //// OU  /////
                // Ajouter un message de succès
                $this->_helper->flashMessenger->addMessage("Produit ajouté avec succès !");

                // Redirection vers la liste des produits
                return $this->_helper->redirector('listeproduit', 'produit');

                // Réinitialiser le formulaire
                $produitFormulaire->reset();
            } else {
                // Remplir le formulaire avec les données saisies pour corriger les erreurs
                $produitFormulaire->populate($formData);
            }
        }

        // Envoyer le formulaire à la vue
        $this->view->formulaire = $produitFormulaire;
    }

    public function modificationAction()
    {
        $id = $this->_getParam('id');
        $produitModel = new Application_Model_Produit();
        $request = $this->getRequest();

        if ($request->isPost()) {
            // Récupérer les données envoyées via le formulaire
            $data = [
                'nom'        => $this->_getParam('nom'),
                'description' => $this->_getParam('description'),
                'prix'       => $this->_getParam('prix'),
                'stock'      => $this->_getParam('stock')
            ];

            $produit_updated = $produitModel->updateProduit($id, $data);
            // Ajouter un message de succès
            $this->_helper->flashMessenger->addMessage("Produit modifié avec succès ! $produit_updated");

            // Redirection vers la liste des produits
            return $this->_helper->redirector('listeproduit', 'produit');
        }
        // Si ce n’est pas un POST, récupérer les infos du produit pour pré-remplir le formulaire
        $this->view->produit = $produitModel->getOneProduit($id);
    }

    public function suppressionAction()
    {
        $id = $this->_getParam('id');
        $produitModel = new Application_Model_Produit();
        $produit_deleted = $produitModel->deleteProduit($id);
        // Ajouter un message de succès
        $this->_helper->flashMessenger->addMessage("Produit supprimé avec succès ! $produit_deleted");

        // Redirection vers la liste des produits
        return $this->_helper->redirector('listeproduit', 'produit');
    }
}
