<?php
class Application_Model_Offrefrancetravail extends Zend_Db_Table_Abstract
{
    protected $_name = 'offrefrancetravail';

    public function getAll()  //recup des offres
    {
        return $this->fetchAll()->toArray();
    }
    public function getAllMetiers()
    {
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from('offrefrancetravail')
            ->where('module LIKE ?', 'fiche');

        // 3. Exécution
        $result = $db->fetchAll($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }
    public function getAllServices()
    {
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from('offrefrancetravail')
            ->where('module LIKE ?', 'services');

        // 3. Exécution
        $result = $db->fetchAll($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }

    public function getAllEntreprises()
    {
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from('offrefrancetravail')
            ->where('module LIKE ?', 'entreprisereccrutement');

        // 3. Exécution
        $result = $db->fetchAll($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }

    public function totalOffres()
    {
        // 1. Récupération de l’adaptateur DB
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from('offrefrancetravail', new Zend_Db_Expr('COUNT(id) AS total'))
            ->where('module LIKE ?', 'offres');

        // 3. Exécution
        $result = $db->fetchOne($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }

    public function totalServices()
    {
        // 1. Récupération de l’adaptateur DB
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from('offrefrancetravail', new Zend_Db_Expr('COUNT(id) AS total'))
            ->where('module LIKE ?', 'services');

        // 3. Exécution
        $result = $db->fetchOne($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }

    public function totalMetiers()
    {
        // 1. Récupération de l’adaptateur DB
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from('offrefrancetravail', new Zend_Db_Expr('COUNT(id) AS total'))
            ->where('module LIKE ?', 'fiche');

        // 3. Exécution
        $result = $db->fetchOne($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }
}