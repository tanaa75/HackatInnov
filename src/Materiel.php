<?php
namespace HackatInnov;

class Materiel {
    private $libelle;
    public function __construct($libM){
        $this->libelle = $libM;
    }
    public function getLibelle() { return $this->libelle; }
}
