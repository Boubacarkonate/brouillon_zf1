<?php
class Application_Model_Offrefrancetravail extends Zend_Db_Table_Abstract
{
    protected $_name = 'francetravail';

    public function getAllOffres()
    {
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from($this->_name)
            ->where('module LIKE ?', 'offres');

        // 3. Exécution
        $result = $db->fetchAll($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }

    public function getAllMetiers()
    {
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from($this->_name)
            ->where('module LIKE ?', 'fiches');

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
            ->from($this->_name)
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
            ->from($this->_name)
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
            ->from($this->_name, new Zend_Db_Expr('COUNT(id) AS total'))
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
            ->from($this->_name, new Zend_Db_Expr('COUNT(id) AS total'))
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
            ->from($this->_name, new Zend_Db_Expr('COUNT(id) AS total'))
            ->where('module LIKE ?', 'fiches');

        // 3. Exécution
        $result = $db->fetchOne($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }

    public function totalEntreprises()
    {
        // 1. Récupération de l’adaptateur DB
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from($this->_name, new Zend_Db_Expr('COUNT(id) AS total'))
            ->where('module LIKE ?', 'entreprisereccrutement');

        // 3. Exécution
        $result = $db->fetchOne($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }

    public function getAllOffresByProjet($projet)
    {
        $db = $this->getAdapter();

        // Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from($this->_name)
            ->where('projet = ?', $projet); // pas de quote ici

        // Exécution
        $result = $db->fetchAll($select);

        return $result;
    }

    public function getAllOffresByProjetByModule($projet, $module)
    {
        $db = $this->getAdapter();

        // Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from($this->_name)
            ->where('module = ?', $module)
            ->where('projet = ?', $projet);

        // Exécution
        $result = $db->fetchAll($select);

        return $result;
    }

    public function totalModulebyProjet($projet, $module)
    {
        // 1. Récupération de l’adaptateur DB
        $db = $this->getAdapter();

        // 2. Construction de la requête avec Zend_Db_Select
        $select = $db->select()
            ->from($this->_name, new Zend_Db_Expr('COUNT(id) AS total'))
            ->where('module = ?', $module)
            ->where('projet = ?', $projet);

        // 3. Exécution
        $result = $db->fetchOne($select); // fetchOne retourne directement la valeur du COUNT

        // 4. Exemple d’utilisation
        return $result;
    }
}
