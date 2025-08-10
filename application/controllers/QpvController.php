<?php
class QpvController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $qpv = new Application_Model_Qpv();

        // Exemple WSA
        $resultatWSA = $qpv->checkAdresseWSA(
            '10',
            'rue Charles Baudelaire',
            '93130',
            'Noisy-le-Sec',
            ['QP', 'QP_2015']
        );

        // Exemple MIXTE
        $resultatMixte = $qpv->checkAdresseMixte(
            '10 rue Charles Baudelaire',
            '93130',
            'Noisy-le-Sec',
            ['QP', 'QP_2015']
        );

        $this->view->resultatWSA   = $resultatWSA;
        $this->view->resultatMixte = $resultatMixte;
    }
}
