<?php
class Categorie {
    private int $id_categorie;
    private string $nom_categorie;
    private string $description_categorie;
    private string $date_creation;
    private string $etat; // active / désactivée
    private int $nb_evenements;

    // Constructeur
    public function __construct($nom, $desc, $date, $etat = 'active', $nb_evenements = 0) {
        $this->nom_categorie = $nom;
        $this->description_categorie = $desc;
        $this->date_creation = $date;
        $this->etat = $etat;
        $this->nb_evenements = $nb_evenements;
    }

    // Getters
    public function getIdCategorie() { return $this->id_categorie; }
    public function getNomCategorie() { return $this->nom_categorie; }
    public function getDescriptionCategorie() { return $this->description_categorie; }
    public function getDateCreation() { return $this->date_creation; }
    public function getEtat() { return $this->etat; }
    public function getNbEvenements() { return $this->nb_evenements; }

    // Setters
    public function setNomCategorie($nom) { $this->nom_categorie = $nom; }
    public function setDescriptionCategorie($desc) { $this->description_categorie = $desc; }
    public function setDateCreation($date) { $this->date_creation = $date; }
    public function setEtat($etat) { $this->etat = $etat; }
    public function setNbEvenements($nb) { $this->nb_evenements = $nb; }
}
?>
