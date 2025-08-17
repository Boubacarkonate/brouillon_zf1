<?php
// On crée une classe de formulaire qui hérite de Zend_Form
class Application_Form_Exo2 extends Zend_Form
{
    // La méthode init() est appelée automatiquement à la création du formulaire
    public function init()
    {
        // Définir la méthode HTTP utilisée par le formulaire (ici POST)
        $this->setMethod('post');

        // -------- Champ email --------
        $this->addElement('text', 'mail', [
            'label'      => 'Entrer votre email',
            'required'   => true,
            'filters'    => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
                ['EmailAddress', true] // vérifie que c'est un email valide
            ]
        ]);

        // -------- Bouton Submit --------
        $this->addElement('submit', 'envoyer', [
            // Ignore = ce bouton ne fait pas partie des données validées
            'ignore' => true,

            // Texte affiché sur le bouton
            'label'  => 'Envoyer'
        ]);
    }
}
