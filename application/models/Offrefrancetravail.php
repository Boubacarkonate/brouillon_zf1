<?php
class Application_Model_Offrefrancetravail extends Zend_Db_Table_Abstract
{
    protected $_name = 'offrefrancetravail';

    public function getAll()
    {
        return $this->fetchAll()->toArray();
    }
}
