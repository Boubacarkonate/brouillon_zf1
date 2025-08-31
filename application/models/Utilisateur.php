<?php
class Application_Model_Utilisateur extends Zend_Db_Table_Abstract
{
    protected $_name = 'utilisateur';

    //     public function getUsers()
    // {
    //     return $this->fetchAll()->toArray();
    // }
    public function getUsers()
    {
        $db = $this->getAdapter();  //je retoune un tableau
        $sql = $db->select()
            ->from($this->_name);

        $resultat = $db->fetchAll($sql);

        return $resultat;
    }

    public function getOneUser($id)  //je retoune un objet
    {
        $select = $this->select()
            ->from($this->_name)
            ->where('id = ?', $id);

        $resultat = $this->fetchRow($select);
        return $resultat;
    }

    /**
     * Ajouter un utilisateur
     */
    public function addUser($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->insert($data);
    }


    public function updateUser() {}

    public function deleteUser() {}

    public function authentification($email, $password)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name, ['id', 'email', 'password'])
            ->where('email = ?', $email)
            ->limit(1);

        $resultat = $db->fetchRow($sql);

        // Vérifier le mot de passe hashé en BDD
        // if ($resultat && password_verify($password, $resultat['password'])) {
        //     unset($resultat['password']); // ne pas exposer le hash
        //     return $resultat;             // retourne un tableau associatif
        // }

        if ($resultat) {
            unset($resultat['password']); // on ne retourne pas le mot de passe
            return $resultat;             // retourne un tableau associatif
        }

        return false;
    }
}
