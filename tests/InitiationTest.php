<?php

use PHPUnit\Framework\TestCase;
use HackatInnov\Membre;
use HackatInnov\Materiel;
use HackatInnov\Initiation;

class InitiationTest extends TestCase
{
    private $evtInitPHP;

    public function testCreationInitiation()
    {
        $lAnim = new Membre("Friche", "Morgan", "mfriche@mail.com", "06 39 98 23 01");
        $this->evtInitPHP = new Initiation(
            "Introduction au PHP",
            "19/06/2021 13:00",
            "2h",
            "Alan Turing",
            $lAnim,
            "etudiants et jeunes developpeurs",
            40
        );
        $this->assertNotNull($this->evtInitPHP);
    }

    public function testAjouterMaterielQuantiteZero()
    {
        $lAnim = new Membre("Friche", "Morgan", "mfriche@mail.com", "06 39 98 23 01");
        $this->evtInitPHP = new Initiation(
            "Introduction au PHP",
            "19/06/2021 13:00",
            "2h",
            "Alan Turing",
            $lAnim,
            "etudiants et jeunes developpeurs",
            40
        );
        $leMateriel = new Materiel("ordinateur portable");
        $this->evtInitPHP->ajouterMateriel($leMateriel->getLibelle(), 0);
        $this->assertEquals(
            0,
            count($this->evtInitPHP->getLesMateriels()),
            "Materiel ajoute alors que la quantite est egale a 0"
        );
    }

    public function testAjouterParticipantLimiteNombrePlaces()
    {
        $lAnim = new Membre("Friche", "Morgan", "mfriche@mail.com", "06 39 98 23 01");
        $this->evtInitPHP = new Initiation(
            "Introduction au PHP",
            "19/06/2021 13:00",
            "2h",
            "Alan Turing",
            $lAnim,
            "etudiants et jeunes developpeurs",
            40
        );
        $this->evtInitPHP->setNbPlaces(2);

        $leParticipant1 = new Membre("Mallien", "Yannick", "myannick@mail.com", "06 39 98 15 12");
        $this->evtInitPHP->ajouterParticipant($leParticipant1);

        $leParticipant2 = new Membre("Dus", "Dominique", "ddus@mail.com", "06 39 98 00 56");
        $this->evtInitPHP->ajouterParticipant($leParticipant2);

        $leParticipant3 = new Membre("Smith", "Jean", "jsmith@mail.com", "06 39 98 85 17");
        $this->assertFalse(
            $this->evtInitPHP->ajouterParticipant($leParticipant3),
            "mauvaise gestion des places disponibles"
        );
        $this->assertSame(
            2,
            count($this->evtInitPHP->getLesMembresParticipants()),
            "erreur dans l'ajout du 3eme participant"
        );
    }
}
