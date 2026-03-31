-- Structure de la base de données HackatInnov
CREATE DATABASE IF NOT EXISTS hackatinnov DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hackatinnov;

CREATE TABLE ORGANISATEUR (
    id INT AUTO_INCREMENT PRIMARY KEY,
    statut VARCHAR(50),
    nom VARCHAR(100) NOT NULL,
    siteWeb VARCHAR(255),
    mel VARCHAR(150) NOT NULL
);

CREATE TABLE MEMBRE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    mel VARCHAR(150) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    dateNaissance DATE,
    portfolio VARCHAR(255)
);

CREATE TABLE HACKATHON (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dateHeureDebut DATETIME NOT NULL,
    dateHeureFin DATETIME NOT NULL,
    lieu VARCHAR(100),
    ville VARCHAR(50) NOT NULL,
    theme VARCHAR(150) NOT NULL,
    affiche VARCHAR(255),
    objectifs TEXT,
    nbPlacesMax INT,
    dateLimiteInscription DATE
);

CREATE TABLE ORGANISER (
    idHackathon INT,
    idOrganisateur INT,
    PRIMARY KEY (idHackathon, idOrganisateur),
    FOREIGN KEY (idHackathon) REFERENCES HACKATHON(id) ON DELETE CASCADE,
    FOREIGN KEY (idOrganisateur) REFERENCES ORGANISATEUR(id) ON DELETE CASCADE
);

CREATE TABLE EVENEMENT (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idHackathon INT NOT NULL,
    idAnimateur INT NOT NULL,
    libelle VARCHAR(150) NOT NULL,
    dateHeure DATETIME NOT NULL,
    duree INT NOT NULL,
    salle VARCHAR(50),
    typePublic VARCHAR(100),
    FOREIGN KEY (idHackathon) REFERENCES HACKATHON(id) ON DELETE CASCADE,
    FOREIGN KEY (idAnimateur) REFERENCES MEMBRE(id)
);

CREATE TABLE CONFERENCE (
    idEvenementConf INT PRIMARY KEY,
    theme VARCHAR(150) NOT NULL,
    FOREIGN KEY (idEvenementConf) REFERENCES EVENEMENT(id) ON DELETE CASCADE
);

CREATE TABLE INITIATION (
    idEvenementInit INT PRIMARY KEY,
    nbPlaces INT NOT NULL,
    FOREIGN KEY (idEvenementInit) REFERENCES EVENEMENT(id) ON DELETE CASCADE
);

CREATE TABLE INSCRIRE (
    idEvenementInit INT,
    idMembre INT,
    PRIMARY KEY (idEvenementInit, idMembre),
    FOREIGN KEY (idEvenementInit) REFERENCES INITIATION(idEvenementInit) ON DELETE CASCADE,
    FOREIGN KEY (idMembre) REFERENCES MEMBRE(id) ON DELETE CASCADE
);

CREATE TABLE PHASE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL
);

CREATE TABLE PLANNING (
    idHackathon INT,
    dateHeureDebut DATETIME,
    idPhase INT NOT NULL,
    duree INT NOT NULL,
    PRIMARY KEY (idHackathon, dateHeureDebut),
    FOREIGN KEY (idHackathon) REFERENCES HACKATHON(id) ON DELETE CASCADE,
    FOREIGN KEY (idPhase) REFERENCES PHASE(id)
);
