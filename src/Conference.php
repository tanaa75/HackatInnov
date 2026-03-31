<?php
namespace HackatInnov;

class Conference extends Evenement {
    private $theme;

    public function __construct($libelle, $dateHeure, $duree, $salle, $lAnimateur, $leTypePublic, $theme) {
        parent::__construct($libelle, $dateHeure, $duree, $salle, $lAnimateur, $leTypePublic);
        $this->theme = $theme;
    }

    public function toJson() {
        return parent::toJson() . ",\n \"theme\" : \"" . $this->theme . "\"\n }\n";
    }
}
