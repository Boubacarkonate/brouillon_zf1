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

    public function insertProduit($data)
    {
        // $data est un tableau associatif avec les colonnes de la table
        // Exemple : ['nom' => 'Produit1', 'description' => 'Blablabla...', 'prix' => 10.5, 'stock' => 5]

        // La méthode insert() retourne l'ID du nouvel enregistrement

        $resultat = $this->insert($data);
        return $resultat;
    }

    public function updateProduit($id, $data)
    {
        // Création de la condition sécurisée : 'id = ?' et la valeur $id
        $condition = $this->getAdapter()->quoteInto('id = ?', $id);

        // Mise à jour dans la table
        return $this->update($data, $condition);
    }


    public function deleteProduit($id)
    {
        // Ajoute les quotes et sécurise l'ID
        $id_quoted = $this->getAdapter()->quote($id);

        // Supprime le produit correspondant à l'ID
        $resultat = $this->delete("id = $id_quoted");
        return $resultat;

        /*
        OU
            // Crée la condition SQL sécurisée
    $where = $this->getAdapter()->quoteInto('id = ?', $id);

    // Supprime uniquement la ligne correspondant à l'ID
    $resultat = $this->delete($where);
    return $resultat;
        */
    }
}
