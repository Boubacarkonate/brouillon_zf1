<?php
class Application_Form_Qpvformulaire extends Zend_Form
{
    public function init()
    {
        // Champ adresse (texte)
        $this->addElement('text', 'adresse', [
            'label'      => 'Adresse :',
            'required'   => true,
            'filters'    => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        // Champ code postal
        $this->addElement('text', 'code_postal', [
            'label'      => 'Code postal :',
            'required'   => true,
            'filters'    => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
                ['Digits'],
                ['StringLength', false, [5, 5]], // 5 chiffres pour le code postal FR
            ],
        ]);

        // Champ commune
        $this->addElement('text', 'nom_commune', [
            'label'      => 'Commune :',
            'required'   => true,
            'filters'    => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        // Bouton envoyer
        $this->addElement('submit', 'envoyer', [
            'label' => 'Vérifier',
        ]);
    }
}
