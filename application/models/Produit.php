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

    public function calculeMoyenne()
    {
        $db = $this->getAdapter();
        $sql = "select AVG(prix) from produits";
        $resultat = $db->fetchOne($sql);  //fetchOne($sql) = une seule valeur (équivalent à fetchColumn()
        return $resultat;
    }

    //     public function calculeMoyenne()
    // {
    //     $db = $this->getAdapter();

    //     // Récupère tous les prix dans un tableau
    //     $prix = $db->fetchCol("SELECT prix FROM produits");

    //     // Compte le nombre de prix
    //     $nbreProduit = (int) $db->fetchOne("SELECT COUNT(prix) FROM produits");

    //     if ($nbreProduit === 0) {
    //         return null; // ou gérer division par zéro autrement
    //     }

    //     // Calcule la moyenne
    //     $total = array_sum($prix) / $nbreProduit;

    //     return $total;
    // }

    public function prixMax()
    {
        $db = $this->getAdapter();
        $sql = "select MAX(prix) from produits";
        $resultat = $db->fetchOne($sql);
        return  $resultat;
    }

    public function prixMin()
    {
        $db = $this->getAdapter();
        $sql = "select MIN(prix) from produits";
        $resultat = $db->fetchOne($sql);
        return  $resultat;
    }

    public function totalStockByProduit()
    {
        $db = $this->getAdapter();
        $sql = "select nom, stock from produits group by nom";
        $resultat = $db->fetchAll($sql);
        return  $resultat;
    }

    public function classementProduitsChers($limit = null)
    {
        $db = $this->getAdapter();
        $nbre = $limit;
        $nbre ? $sql = "select nom, prix from produits order by prix DESC limit $nbre" : $sql = "select nom, prix from produits order by prix DESC";
        // $sql = "select nom, prix from produits order by prix DESC limit $nbre";
        $resultat = $db->fetchAll($sql);
        return  $resultat;
    }

    public function motRecherche($lettre)
    {
        $db = $this->getAdapter();
        $lettrequoted = $db->quote('%' . $lettre . '%');
        $sql = "select * from produits where nom like $lettrequoted ";
        $resultat = $db->fetchAll($sql);
        return  $resultat;
    }

    public function filtreEntreDeuxValeurs($valeur1, $valeur2)
    {
        $db = $this->getAdapter();
        $val1securited = $valeur1;
        $val2securited = $valeur2;
        $sql = "select * from produits where prix between $val1securited and $val2securited";
        $resultat = $db->fetchAll($sql);
        return  $resultat;
    }

    public function moins50Euros()
    {
        $db = $this->getAdapter();

        $sql = 'select * from produits where prix < 50 ';
        $resultat = $db->fetchAll($sql);
        return  $resultat;
    }

    public function plus100Euros()
    {
        $db = $this->getAdapter();

        $sql = 'select * from produits where prix > 100 ';
        $resultat = $db->fetchAll($sql);
        return  $resultat;
    }
    public function decroissant()
    {
        $db = $this->getAdapter();

        $sql = 'select * from produits order by prix DESC';
        $resultat = $db->fetchAll($sql);
        return  $resultat;
    }
    public function croissant()
    {
        $db = $this->getAdapter();

        $sql = 'select * from produits order by prix ASC ';
        $resultat = $db->fetchAll($sql);
        return  $resultat;
    }
}
