<?php
class Application_Form_Produitform extends Zend_Form
{
    public function init()
    {
        // On définit la méthode du formulaire en POST

        //PS : je le déclare dans le init du form donc dès lors que j'appelerais cette class, elle sera toujours en POST. 
        // PS : je ne suis pas pbliger de le faire ici,  je peux sinon le faire dans le controller
        $this->setMethod('post');

        /**
         * Champ "nom" en bdd et c'est équivalent au name="nom" dans un <input> classique
         * -----------------
         * - type : input text
         * - name du champ = nom
         * - label affiché : "Nom d’utilisateur"
         * - requis = true (obligatoire)
         * - attribut HTML class="form-control" (Bootstrap)
         */
        $nom = new Zend_Form_Element_Text('nom');
        $nom->setLabel('Nom du produit :')
            ->setRequired(true)
            ->setAttrib('class', 'form-control');

        /**
         * Champ "description" en bdd et c'est équivalent au name="description" dans un <input> classique
         * --------------
         * - type : input text
         * - name = description
         * - label affiché : "description"
         * - requis = true (obligatoire)
         * - style Bootstrap
         */
        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Description :')
            ->setRequired(true)
            ->setAttrib('placeholder', 'Entrez la description du produit')
            ->setAttrib('class', 'form-control')
            ->setAttrib('rows', 5); // définit la hauteur du textarea

        /**
         * Champ "prix" en bdd et c'est équivalent au name="^rix" dans un <input> classique
         * ----------------
         * - type : input text
         * - name = prix
         * - label affiché : "prix"
         * - addValidator('Float') → vérifie que la valeur saisie est bien un nombre (float).
         * - style Bootstrap
         */
        $prix = new Zend_Form_Element_Text('prix');
        $prix->setLabel('Prix : ')
            ->setRequired(true)
            ->addValidator('Float')
            ->setAttrib('class', 'form-control');

        /**
         * Champ "stock" en bdd et c'est équivalent au name="stock" dans un <input> classique
         * ----------------
         * - type : input text
         * - name = stock
         * - label affiché : "stock"
         *          * - addValidator('Float') → vérifie que la valeur saisie est bien un entier.
         * - style Bootstrap
         */
        $stock = new Zend_Form_Element_Text('stock');
        $stock->setLabel('Stock :')
            ->setRequired(true)
            ->addValidator('Int')
            ->setAttrib('class', 'form-control');

        /**
         * Bouton Submit
         * --------------
         * - type : input submit
         * - nom = register
         * - label affiché : "Enregistrer un produit"
         * - style Bootstrap (btn + btn-primary + mt-3)
         */
        $submit = new Zend_Form_Element_Submit('register');
        $submit->setLabel('Enregistrer un produit')
            ->setAttrib('class', 'btn btn-primary mt-3');


        /**
         * Ajout des éléments au formulaire
         * --------------------------------
         * Ici on ajoute tous les champs définis
         */
        $this->addElements(array($nom, $description, $prix, $stock, $submit));

        /**
         * Définition des "Decorators"
         * ----------------------------
         * Les décorateurs définissent comment chaque élément est rendu en HTML.
         * Ici :
         *  - ViewHelper : affiche l’input lui-même
         *  - Errors : affiche les erreurs de validation
         *  - Label : affiche le label avec la classe CSS "form-label"
         *  - HtmlTag : englobe chaque champ dans <div class="mb-3"> (Bootstrap spacing)
         *
         * Résultat : chaque champ est correctement stylisé avec Bootstrap.
         */
        foreach ($this->getElements() as $element) {
            $element->setDecorators(array(
                'ViewHelper',
                'Errors',
                array('Label', array('class' => 'form-label text-primary')),
                array('HtmlTag', array('tag' => 'div', 'class' => 'mb-3'))
            ));
        }

        // Ajouter le submit **après** et définir son décorateur
        $this->addElement($submit);
        $submit->setDecorators(['ViewHelper']);
    }
}
