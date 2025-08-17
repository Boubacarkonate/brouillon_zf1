<?php
class Application_Model_User extends Zend_Db_Table_Abstract
{
    protected $_name = 'users';  // Nom de la table
    protected  $_primary = 'id';  // Clé primaire


    public function getUsers()
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name, ['nom', 'email'])
            ->order('nom ASC');

        $resultat = $db->fetchAll($sql);

        return $resultat;
    }

    public function getoneuser($id)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name, '*')
            ->where('id = ?', $id);

        $resultat = $db->fetchRow($sql);  // fetchRow = un seul enregistrement
        return $resultat;
    }
}
