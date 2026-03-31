<?php
namespace HackatInnov;

class Membre {
    private $nom;
    private $prenom;
    private $mel;
    private $telephone;

    public function __construct($nom, $prenom, $mel, $tel) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->mel = $mel;
        $this->telephone = $tel;
    }

    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getMel() { return $this->mel; }
    public function getTelephone() { return $this->telephone; }

    public function toJson() {
        return json_encode([
            "nom" => $this->nom,
            "prenom" => $this->prenom,
            "mel" => $this->mel,
            "telephone" => $this->telephone
        ]);
    }
}
