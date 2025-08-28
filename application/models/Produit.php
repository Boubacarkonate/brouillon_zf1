<?php
class Application_Model_Produit extends Zend_Db_Table_Abstract
{
    protected $_name = 'produits';

    public function getListeProduits()
    {

        $select = $this->select()
            ->from($this);
        $resultat = $this->fetchAll($select)->toArray();

        return $resultat;
    }

    public function getOneProduit($id)
    {
        $db = $this->getAdapter();
        $select = $db->select()
            ->from($this->_name)
            ->where('id = ?', $id);
        $resultat = $db->fetchRow($select);

        return $resultat;
    }
}
