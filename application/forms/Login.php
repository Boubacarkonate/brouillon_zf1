<?php
class Application_Form_login extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Entrer votre email :')
            ->setRequired(true)
            ->addValidator('EmailAddress')
            ->setAttrib('class', 'form-control');

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Entrer votre mot de passe :')
            ->setRequired(true)
            ->setAttrib('class', 'form-control');

        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Se connecter')
            ->setAttrib('class', 'btn btn-primary mt-3');

        $this->addElements([$email, $password, $submit]);

        // Décorateurs Bootstrap
        foreach ($this->getElements() as $element) {
            $element->setDecorators([
                'ViewHelper',
                'Errors',
                ['Label', ['class' => 'form-label']],
                ['HtmlTag', ['tag' => 'div', 'class' => 'mb-3']]
            ]);
        }

        // Décorateur spécifique pour le bouton submit
        $submit->setDecorators(['ViewHelper']);
    }
}
