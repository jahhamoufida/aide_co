<?php
class Evenement {
    private int $id_evenement;
    private string $nom_evenement;
    private string $date_evenement;
    private int $nombre_places;
    private int $nombre_inscrits;
    private ?string $image;
    private ?int $id_categorie; // chemin de l'image (nullable)

    public function __construct($nom, $date, $places, $inscrits = 0, $image = null) {
        $this->nom_evenement = $nom;
        $this->date_evenement = $date;
        $this->nombre_places = $places;
        $this->nombre_inscrits = $inscrits;
        $this->image = $image;
        

    }

    public function getIdEvenement() { return $this->id_evenement; }
    public function getNomEvenement() { return $this->nom_evenement; }
    public function getDateEvenement() { return $this->date_evenement; }
    public function getNombrePlaces() { return $this->nombre_places; }
    public function getNombreInscrits() { return $this->nombre_inscrits; }
    public function getImage() { return $this->image; }

    public function setNomEvenement($n) { $this->nom_evenement = $n; }
    public function setDateEvenement($d) { $this->date_evenement = $d; }
    public function setNombrePlaces($p) { $this->nombre_places = $p; }
    public function setNombreInscrits($i) { $this->nombre_inscrits = $i; }
    public function setImage($img) { $this->image = $img; }
    public function getIdCategorie() { 
    return $this->id_categorie; 
}

public function setIdCategorie($id) { 
    $this->id_categorie = $id; 
}

}
?>
