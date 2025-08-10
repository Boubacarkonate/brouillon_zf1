<?php
// Contrôleur "PostController" qui hérite de Zend_Controller_Action
class PostController extends Zend_Controller_Action
{
    // Action "listAction" => gère l'affichage de la liste des posts
    public function listAction()
    {
        // 🔹 Récupère le paramètre "page" depuis l'URL.
        // Exemple : /post/list/page/2  => $page = 2
        // Le deuxième argument (1) est la valeur par défaut si "page" n'est pas fourni.
        $page = $this->_getParam('page', 1);

        // 🔹 Exemple de données statiques
        // En pratique, ces données viendraient d'un modèle (ex: Application_Model_Post)
        $posts = [
            ['title' => 'Premier post', 'content' => 'Contenu du premier post...'],
            ['title' => 'Deuxième post', 'content' => 'Contenu du deuxième post...'],
            ['title' => 'Troisième post', 'content' => 'Contenu du troisième post...']
        ];

        // 🔹 Envoi des données à la vue :
        // $this->view agit comme un "pont" vers le fichier list.phtml
        $this->view->page = $page;   // La variable $page sera disponible dans la vue
        $this->view->posts = $posts; // Le tableau $posts sera disponible dans la vue
    }
}
