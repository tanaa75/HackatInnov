<?php
namespace HackatInnov;

class Initiation extends Evenement {
    private $nbPlaces;
    private $lesMateriels; // dictionnaire (string, int)
    private $lesMembresParticipants; // collection de Membre

    public function __construct($libelle, $dateHeure, $duree, $salle, $lAnimateur, $leTypePublic, $nb) {
        parent::__construct($libelle, $dateHeure, $duree, $salle, $lAnimateur, $leTypePublic);
        $this->nbPlaces = $nb;
        $this->lesMateriels = array();
        $this->lesMembresParticipants = array();
    }

    public function setNbPlaces($nb) {
        if ($nb >= 0) {
            $this->nbPlaces = $nb;
        }
    }

    public function getNbPlaces() { return $this->nbPlaces; }
    public function getLesMateriels() { return $this->lesMateriels; }
    public function getLesMembresParticipants() { return $this->lesMembresParticipants; }

    public function ajouterMateriel($unLibelleMateriel, $uneQuantite) {
        if ($unLibelleMateriel != null && $uneQuantite > 0) {
            if (!array_key_exists($unLibelleMateriel, $this->lesMateriels)) {
                $this->lesMateriels[$unLibelleMateriel] = $uneQuantite;
            }
        }
    }

    public function ajouterParticipant($unMembre) {
        if (count($this->lesMembresParticipants) < $this->nbPlaces) {
            array_push($this->lesMembresParticipants, $unMembre);
            return true;
        }
        return false;
    }

    private function lesMaterielsToJson() {
        $chaineJson = "\"materiels\" : [  \n";
        $debutChaine = true;
        foreach ($this->lesMateriels as $lib => $qte) {
            if (!$debutChaine) {
                $chaineJson .= ",\n";
            } else {
                $debutChaine = false;
            }
            $chaineJson .= "{ \n \"libelle\" : \"".$lib . "\",\n \"quantite\" : \"" . $qte . "\" }";
        }
        $chaineJson .= "]  \n";
        return $chaineJson;
    }

    private function lesParticipantsToJson() {
        $tabInscriptions = array();
        foreach ($this->lesMembresParticipants as $membre) {
            // RGPD : On n'exporte que le nom et prénom pour l'app publique
            $tabInscriptions[] = [
                "nom" => $membre->getNom(),
                "prenom" => $membre->getPrenom()
            ];
        }
        return json_encode($tabInscriptions);
    }

    public function toJson() {
        $res = parent::toJson();
        $res .= ",\n \"nbPlaces\" : " . $this->nbPlaces;
        $res .= ",\n " . $this->lesMaterielsToJson();
        if (!empty($this->lesMembresParticipants)) {
            $res .= ",\n \"participants\" : " . $this->lesParticipantsToJson();
        }
        return $res . "\n }";
    }
}
