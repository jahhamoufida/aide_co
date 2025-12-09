<?php
require_once __DIR__ . '/../auth/config.php';
require_once __DIR__ . '/../model/Categorie.php';

class CategorieC {

    // Lister toutes les catégories
   // Lister toutes les catégories
public function listCategories() {
    $db = config::getConnexion();

    $sql = "SELECT * FROM categorie ORDER BY date_creation DESC";

    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}




    // Récupérer une catégorie par ID
    public function getCategorie($id) {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM categorie WHERE id_categorie = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Alias pour compatibilité
    public function getCategorieById($id) {
        return $this->getCategorie($id);
    }

    // Ajouter une nouvelle catégorie
    public function addCategorie(Categorie $c) {
        $db = config::getConnexion();
        $sql = "INSERT INTO categorie (nom_categorie, description_categorie, date_creation, etat, nb_evenements)
                VALUES (:nom, :desc, :date, :etat, :nb)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nom' => $c->getNomCategorie(),
            ':desc' => $c->getDescriptionCategorie(),
            ':date' => $c->getDateCreation(),
            ':etat' => $c->getEtat(),
            ':nb' => $c->getNbEvenements()
        ]);
    }

    // Mettre à jour une catégorie existante
    public function updateCategorie($id, Categorie $c) {
        $db = config::getConnexion();
        $sql = "UPDATE categorie SET 
                nom_categorie = :nom,
                description_categorie = :desc,
                date_creation = :date,
                etat = :etat,
                nb_evenements = :nb
                WHERE id_categorie = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':nom' => $c->getNomCategorie(),
            ':desc' => $c->getDescriptionCategorie(),
            ':date' => $c->getDateCreation(),
            ':etat' => $c->getEtat(),
            ':nb' => $c->getNbEvenements()
        ]);
    }

    // Supprimer une catégorie
    public function deleteCategorie($id) {
        $db = config::getConnexion();
        $stmt = $db->prepare("DELETE FROM categorie WHERE id_categorie = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    // Nouvelle fonction de recherche par mot-clé
public function searchCategories($keyword) {
    $db = config::getConnexion();
    $sql = "SELECT c.*, COUNT(e.id_evenement) AS nb_evenements
            FROM categorie c
            LEFT JOIN evenement e ON c.id_categorie = e.id_categorie
            WHERE c.nom_categorie LIKE :keyword
            GROUP BY c.id_categorie
            ORDER BY c.date_creation DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute(['keyword' => "%$keyword%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    // Optionnel : filtrer par nom ou état
    public function filterCategories($name = null, $etat = null) {
        $db = config::getConnexion();
        $clauses = [];
        $params = [];

        if (!empty($name)) {
            $clauses[] = "nom_categorie LIKE :name";
            $params[':name'] = "%$name%";
        }
        if (!empty($etat)) {
            $clauses[] = "etat = :etat";
            $params[':etat'] = $etat;
        }

        $sql = "SELECT * FROM categorie";
        if (count($clauses) > 0) {
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }
        $sql .= " ORDER BY date_creation DESC";

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
