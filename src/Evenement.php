<?php
namespace HackatInnov;

abstract class Evenement {
    protected $libelle;
    protected $dateHeure;
    protected $duree;
    protected $salle;
    protected $lAnimateur; // type: Membre
    protected $typePublic;

    public function __construct($libelle, $dateHeure, $duree, $salle, $lAnimateur, $leTypePublic) {
        $this->libelle = $libelle;
        $this->dateHeure = $dateHeure;
        $this->duree = $duree;
        $this->salle = $salle;
        $this->lAnimateur = $lAnimateur;
        $this->typePublic = $leTypePublic;
    }

    public function getLibelle() { return $this->libelle; }
    public function getDateHeure() { return $this->dateHeure; }

    protected function toJson() {
        return " { \"libelle\" : \"" . $this->libelle . "\", \n" .
               "\"dateHeure\" : \"" . $this->dateHeure . "\", \n" .
               "\"duree\" : \"" . $this->duree . "\", \n" .
               "\"salle\" :  \"" . $this->salle . "\", \n" .
               "\"typePublic\" : \"" . $this->typePublic . "\", \n" .
               "\"animateur\" : " . $this->lAnimateur->toJson();
    }
}
