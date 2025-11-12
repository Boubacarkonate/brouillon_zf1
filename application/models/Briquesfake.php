<?php

class Application_Model_Briquesfake
{
    public function fetchAllActives()
    {
        // Données "en dur" simulant les briques configurées
        return [
            [
                'tuile'       => 'BRQ_TEST1',
                'nom_affiche' => 'Alerte - Utilisateurs inactifs',
                'requete'     => 'req_inactifs',
                'type'        => 'requete',
                'ordre'       => 1,
            ],
            [
                'tuile'       => 'BRQ_TEST2',
                'nom_affiche' => 'Alerte - Erreurs système',
                'requete'     => 'req_erreurs',
                'type'        => 'vue',
                'ordre'       => 2,
            ],
        ];
    }
}
