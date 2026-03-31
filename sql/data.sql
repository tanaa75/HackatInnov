-- Jeu d'essai pour HackatInnov
USE hackatinnov;

INSERT INTO HACKATHON (dateHeureDebut, dateHeureFin, lieu, ville, theme, affiche, objectifs, nbPlacesMax, dateLimiteInscription)
VALUES ('2026-06-11 17:00:00', '2026-06-13 18:00:00', 'Campus Tech', 'Paris', 'IA & Santé', 'affiche_ia.png', 'Créer des solutions IA pour le diagnostic médical', 100, '2026-06-01');

INSERT INTO MEMBRE (nom, prenom, mel, telephone) VALUES
('Friche', 'Morgan', 'mfriche@mail.com', '0601020304'),
('Gelin', 'Louison', 'lgelin@mail.com', '0611223344'),
('Mallien', 'Yannick', 'myannick@mail.com', '0699887766');

INSERT INTO EVENEMENT (idHackathon, idAnimateur, libelle, dateHeure, duree, salle, typePublic) VALUES
(1, 1, 'Introduction à la cybersécurité', '2026-06-12 10:00:00', 120, 'Alan Turing', 'Débutants'),
(1, 2, 'Les méthodes agiles', '2026-06-12 14:00:00', 120, 'Hedy Lamarr', 'Chefs de projets novices');

INSERT INTO INITIATION (idEvenementInit, nbPlaces) VALUES (1, 40);
INSERT INTO CONFERENCE (idEvenementConf, theme) VALUES (2, 'Gestion de projet');

INSERT INTO INSCRIRE (idEvenementInit, idMembre) VALUES (1, 3);
