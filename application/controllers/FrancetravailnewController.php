<?php
class FrancetravailnewController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $api = new Application_Model_Francetravail();

        try {
            $profil = [
                'departement'         => '75',
                'distance'        => 20,
                'motsCles'        => 'développeur web',
                // 'codeROME'        => 'M1805',
                'origineOffre'    => 2,
                'range'           => '0-9'
            ];
            //             $profil = [
            //     'departement'                     => '75',        // Code INSEE de la commune
            //     'distance'                     => 20,            // Rayon en km autour de la commune
            //     'motsCles'                     => 'développeur web',
            //     // 'codeROME'                     => 'M1805',       // Métier ROME
            //     // 'appellation'                  => '38444',       // Code appellation ROME
            //     // 'codeNAF'                      => '62.01Z',      // Code NAF (activité)
            //     // 'accesTravailleurHandicape'    => false,         // filtrer les offres accessibles aux travailleurs handicapés
            //     'origineOffre'                 => 2,             // 0=France Travail, 1=Partenaire, 2=les deux
            //     'range'                        => '0-49',        // pagination
            //     //'dateDebut'                    => date('Y-m-d'), // optionnel : date de début publication
            // ];

            $result = $api->searchOffres($profil);

            $offres = [];
            if (!empty($result['resultats'])) {
                foreach ($result['resultats'] as $offre) {
                    $offres[] = [
                        'titre'       => $offre['intitule'] ?? 'N/A',
                        'entreprise'  => $offre['entreprise']['nom'] ?? 'N/A',
                        'ville'       => $offre['lieuTravail']['libelle'] ?? 'N/A',
                        'contrat'     => $offre['typeContrat'] ?? 'N/A',
                        'date'        => $offre['dateCreation'] ?? 'N/A',
                        'lien'        => $offre['origineOffre']['urlOrigine'] ?? 'N/A',
                    ];
                }
            }

            $this->view->offres = $offres;
        } catch (Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }
}
