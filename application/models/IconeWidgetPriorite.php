<?php
// application/models/IconeWidgetPriorite.php
class Application_Model_IconeWidgetPriorite
{
    protected $_db;

    public function __construct()
    {
        $this->_db = Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * Récupère les icônes du widget Priorité
     */
    public function getIconesPriorite()
    {
        // Tentative depuis aux_uxcouleursicones
        $select = $this->_db->select()
            ->from('param_priorite', ['vr', 'code', 'libelle', 'couleur', 'iconify_code '])
            // ->where('code LIKE ?', 'PRIORITE_%')
            ->order('code ASC');

        $result = $this->_db->fetchAll($select);

        // Si vide → fallback vers aux_domaine
        // if (empty($result)) {
        //     $select = $this->_db->select()
        //         ->from('aux_domaine', ['code', 'libelle', 'icone' => 'valeur'])
        //         ->where('code LIKE ?', 'PRIORITE_%')
        //         ->order('code ASC');
        //     $result = $this->_db->fetchAll($select);
        // }

        return $result;
    }

    /**
     * Exemple de méthode d'update (pour CRUD futur)
     */
    public function updateIcone($code, $newIcon)
    {
        return $this->_db->update(
            'param_priorite',
            ['iconify_code' => $newIcon],
            ['code = ?' => $code]
        );
    }
}
