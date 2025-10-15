<?php
class Application_Model_Offrefrancetravail extends Zend_Db_Table_Abstract
{
    protected $_name = 'francetravail';


    public function getAllOffresByProjet($projet)
    {
        $db = $this->getAdapter();


        $select = $db->select()
            ->from($this->_name)
            ->where('projet = ?', $projet);


        $result = $db->fetchAll($select);

        return $result;
    }

    public function getAllOffresByProjetByModule($projet, $module)
    {
        $db = $this->getAdapter();


        $select = $db->select()
            ->from($this->_name)
            ->where('module = ?', $module)
            ->where('projet = ?', $projet);


        $result = $db->fetchAll($select);

        return $result;
    }

    public function totalModulebyProjet($projet, $module)
    {

        $db = $this->getAdapter();


        $select = $db->select()
            ->from($this->_name, new Zend_Db_Expr('COUNT(id) AS total'))
            ->where('module = ?', $module)
            ->where('projet = ?', $projet);


        $result = $db->fetchOne($select);


        return $result;
    }

    // Récupérer les items sans projet
    public function getAllOffresSansProjet($module)
    {
        $db = $this->getAdapter();

        $select = $db->select()
            ->from($this->_name)
            ->where('module = ?', $module)
            ->where('(projet IS NULL OR projet = "" OR projet = " ")');

        return $db->fetchAll($select);
    }

    // Totaux pour items sans projet
    public function totalModuleSansProjet($module)
    {
        $db = $this->getAdapter();

        $select = $db->select()
            ->from($this->_name, new Zend_Db_Expr('COUNT(id) AS total'))
            ->where('module = ?', $module)
            ->where('(projet IS NULL OR projet = "" OR projet = " ")');

        return $db->fetchOne($select);
    }



    public function deleteItem($id)
    {
        $db = $this->getAdapter();
        return $db->delete($this->_name, ['id = ?' => $id]);
    }
}
