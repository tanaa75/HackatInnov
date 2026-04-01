# Étude de cas HackatInnov - Réponses

## A.1
L’association **Hackat’Innov** organise des marathons de programmation (hackathons) de 48 heures. Le projet consiste à mettre en place une solution informatique complète permettant de gérer les événements, les inscriptions des participants, la constitution des équipes et le vote final du jury, tout en respectant les contraintes de cybersécurité (DICP) et de protection des données (RGPD).

**Proposition d'évolution du schéma relationnel (MLD) :**
*   **INSCRIPTION** (#idHackathon, #idMembre, dateSaisie, texteLibreCompetences, numSeq)
*   **EQUIPE** (id, nom, #idProjet, #idHackathon, #idChefProjet)
*   **AFFECTION** (#idEquipe, #idMembre)
*   **VOTE** (#idMembreJury, #idEquipe, note)
*   *Note : Le nombre de places et la date limite sont ajoutés à la table HACKATHON.*

## A.2.1
La structure de la base de données respecte la règle de gestion « il ne peut y avoir qu’une seule phase qui débute à une heure donnée pour un hackathon précis » grâce à la clé étrangère idPhase en référence à id de PHASE.

## A.2.2
```sql
CREATE TABLE PLANNING (
    idHackathon INT, 
    dateHeureDebut DATETIME,
    idPhase INT NOT NULL, 
    duree INT NOT NULL,
    CONSTRAINT pk_planning PRIMARY KEY (idHackathon, dateHeureDebut),
    CONSTRAINT fk_planning_hackathon FOREIGN KEY (idHackathon) REFERENCES HACKATHON (id) ON DELETE CASCADE,
    CONSTRAINT fk_planning_phase FOREIGN KEY (idPhase) REFERENCES PHASE (id)
);
```

## A.3.1
Le choix de la "date et heure de début" comme clé du dictionnaire est pertinent car, dans un tableau associatif (dictionnaire), chaque clé est unique. Cela force la particularité des activités affichées : si deux activités devaient se chevaucher à la même heure, la structure de données ne permettrait d'en conserver qu'une seule, donc évitant ainsi les conflits d'affichage dans le planning.

## A.3.2
```php
 $planningParParticipant = array();

 foreach ($h->getLesPhases() as $phase) {
    $planningParParticipant[$phase->getDateHeure()] = $phase->getLibelle();
}

 foreach ($h->getLesEvenements() as $evt) {
    if ($evt instanceof Initiation) {
        if ($evt->estInscrit($m->getMel())) {
            $planningParParticipant[$evt->getDateHeure()] = $evt->getLibelle();
        }
    }
}
 
```

## A.4
**1. Contrôle de capacité (Hackathon) :**
```sql
CREATE TRIGGER tg_check_capacite
BEFORE INSERT ON INSCRIPTION
FOR EACH ROW
BEGIN
    DECLARE v_inscrits INT;
    DECLARE v_max INT;
    SELECT COUNT(*) INTO v_inscrits FROM INSCRIPTION WHERE idHackathon = NEW.idHackathon;
    SELECT nbPlacesMax INTO v_max FROM HACKATHON WHERE id = NEW.idHackathon;
    IF v_inscrits >= v_max THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Capacité maximale du hackathon atteinte.';
    END IF;
END;
```

**2. Audit de sécurité (Preuve) :**
```sql
CREATE TRIGGER tg_audit_votes
AFTER UPDATE ON VOTE
FOR EACH ROW
BEGIN
    INSERT INTO LOG_VOTES (idEquipe, ancienneNote, nouvelleNote, dateModif, auteur)
    VALUES (OLD.idEquipe, OLD.note, NEW.note, NOW(), USER());
END;
```

## B.1.1
```php
if ($unLibelleMateriel != null && $uneQuantite > 0) {
```

## B.1.2
```php
public function __construct($libelle, $dateHeure, $duree, $salle, $lAnimateur, $leTypePublic, $nb) {
    parent::__construct($libelle, $dateHeure, $duree, $salle, $lAnimateur, $leTypePublic);
    $this->nbPlaces = $nb;
    $this->lesMateriels = array();               
    $this->lesMembresParticipants = array();    
}
```

## B.1.3
```php
public function ajouterParticipant($unMembre) {
    if (count($this->lesMembresParticipants) < $this->nbPlaces) {
        array_push($this->lesMembresParticipants, $unMembre);
        return true;  
    }
    return false; 
}
```

## B.1.4
```php
private function lesParticipantsToJson() {
    $tab = array();
    foreach ($this->lesMembresParticipants as $membre) {
        $tab[] = [
            "nom"    => $membre->getNom(),
            "prenom" => $membre->getPrenom() 
        ]; 
    }
    return json_encode($tab); 
}
```

## B.1.5
```php
public function toJson() {
    $res = parent::toJson();
    $res .= ",\n \"nbPlaces\" : " . $this->nbPlaces;
    $res .= ",\n " . $this->lesMaterielsToJson();
    if (!empty($this->lesMembresParticipants)) {
        $res .= ",\n \"participants\" : " . $this->lesParticipantsToJson();  
    }
    return $res . "\n }"; 
}
```

## B.2
| Clé | Type | Valeur | Type |
| --- | --- | --- | --- |
| Date et heure de début | String | Libellé de l’initiation | String |

Le dictionnaire permet d'afficher un planning trié par heure pour un participant donné. Chaque clé est unique (une heure = une activité), ce qui garantit qu'on ne peut pas avoir deux activités au même moment dans le planning affiché.

## B.3.1
La méthode `toJson()` de `Membre` exporte les champs `mel` et `telephone`. Ce sont des données personnelles au sens du RGPD. Les diffuser vers une application publique sans authentification viole le principe de minimisation des données : on ne doit exposer que ce qui est strictement nécessaire.

## B.3.2
En JSON, les guillemets `"` servent de délimiteurs pour les chaînes. Si la chaîne elle-même contient des guillemets, ils doivent être échappés avec un antislash : `\"`. Quand on construit du JSON à la main par concaténation, on oublie facilement de gérer ces cas. C'est pourquoi il faut toujours utiliser `json_encode()`.

## B.3.3
La fonction native PHP `json_encode()` se prémunit contre cette faille. Elle transforme automatiquement les guillemets en `\"`, les antislashs en `\\`, et gère tous les caractères spéciaux pour produire un JSON toujours valide.

## C.1
La ligne `{{lesHackathons.length}}` affiche dynamiquement le nombre de hackathons retournés par l'API après la recherche, pour que l'utilisateur sache combien de résultats correspondent à son critère.

## C.2
```html
 <table>
    <tr>
        <th>Date</th>
        <th>Ville</th>
        <th>Thème</th>  
    </tr>
    <tr v-for="hackathon in lesHackathons" :key="hackathon.id">
        <td>{{hackathon.dateDebut}}</td>
        <td>{{hackathon.ville}}</td>
        <td>{{hackathon.theme}}</td>  
    </tr>
</table>
```

```php
 
$sql = 'SELECT DATE_FORMAT(dateHeureDebut, "%d/%m/%Y") AS dateDebut, ville, theme
        FROM HACKATHON
        WHERE ville LIKE :critere OR theme LIKE :critere
        ORDER BY dateHeureDebut, ville';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':critere', "%$critere%", PDO::PARAM_STR);
```

## C.3.a
Le `LEFT OUTER JOIN` conserve tous les enregistrements de la table de gauche (`EVENEMENT`), même si certains n'ont pas de correspondance dans les tables filles (`INITIATION` ou `CONFERENCE`). Avec un simple `JOIN`, tout événement sans données dans les tables filles serait perdu des résultats.

## C.3.b 
```sql
SELECT E.libelle, I.nbPlaces, C.theme, E.dateHeure, E.salle, M.nom, M.prenom
FROM EVENEMENT E
LEFT OUTER JOIN INITIATION I  ON E.id = I.idEvenementInit
LEFT OUTER JOIN CONFERENCE C  ON E.id = C.idEvenementConf
JOIN MEMBRE M ON E.idAnimateur = M.id
ORDER BY E.dateHeure ASC;
```

## C.4
```sql
CREATE VIEW vue_succes_initiations (idEvenementInit, libelle, nbInscrits) AS
    SELECT I.idEvenementInit, E.libelle, COUNT(INS.idMembre) AS nbInscrits
    FROM INITIATION I
    JOIN EVENEMENT E ON I.idEvenementInit = E.id
    LEFT JOIN INSCRIRE INS ON I.idEvenementInit = INS.idEvenementInit
    GROUP BY I.idEvenementInit, E.libelle;
```

## C.5.1
La faille est une Injection SQL. La variable `$critere` reçue depuis l'URL est directement collée dans la requête SQL. Un pirate peut saisir `Paris' OR 1=1 -- ` pour récupérer toutes les données de la table.

## C.5.2
```php
$critere = filter_input(INPUT_GET, 'recherche', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

$sql = 'SELECT DATE_FORMAT(dateHeureDebut, "%d/%m/%Y") AS dateDebut, ville, theme
        FROM HACKATHON
        WHERE ville LIKE :critere OR theme LIKE :critere
        ORDER BY dateHeureDebut, ville';

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':critere', '%' . $critere . '%', PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll();
echo json_encode($rows);
```

## D.1
Un pirate pourrait voter plusieurs fois pour un même projet ou modifier les votes d'autres membres du jury, faussant ainsi les résultats. Cela entraînerait des contestations des participants, une perte de crédibilité de l'organisation et potentiellement des litiges juridiques si des prix ou des financements sont en jeu.

## D.2
Placer le serveur de base de données hors de la DMZ (en zone privée) offre une double protection :
- Si le serveur Web (en DMZ) est piraté, un second pare-feu bloque l'accès à la base de données.
- Les données sensibles (votes, membres, résultats) ne sont jamais directement accessibles depuis Internet.
- L'attaquant devrait franchir deux barrières distinctes pour atteindre les données.

## D.3
| Point de vigilance | Élément(s) concerné(s) | Pourquoi |
| --- | --- | --- |
| Sécuriser le service HTTP (HTTPS) | Sécuriser le service HTTP (HTTPS) | Le HTTPS chiffre les échanges entre l'app mobile et le serveur |
| Utiliser des requêtes préparées | Code PHP (API) | C'est dans le code qu'on construit les requêtes SQL |
| Se prémunir des injections SQL | Code PHP + Serveur MySQL | L'injection vient du code mais impacte la base |
| Wi-Fi sécurisé | Smartphones (zone publique) | Pour éviter les attaques Man-in-the-Middle sur le réseau |
| Intégrité des données / Droits d'accès | Serveur MySQL (zone privée) | C'est là que les données sont stockées et qu'on configure les droits |
| Mise à jour des bibliothèques | Serveur Web (PHP, Apache) + MySQL | Les failles connues sont corrigées dans les mises à jour |

## Analyse de l'impact de l'Injection SQL

Au lieu de renvoyer uniquement le hackathon de la ville recherchée (ex: Paris), l'API a renvoyé la totalité des enregistrements de la table `HACKATHON`. L'injection a permis de contourner le filtre de recherche de l'application, exposant ainsi l'intégralité des données, ce qui constitue une brèche de confidentialité typique (faille d'Injection SQL de type INBAND selon l'OWASP).

Suite à la concaténation de la chaîne malveillante `$critere = "Paris' OR 1=1 -- "`, voici la requête qui a été "fabriquée" par le serveur PHP et envoyée à MySQL :

```sql
SELECT id, dateHeureDebut, ville, theme
FROM HACKATHON
WHERE ville = 'Paris' OR 1=1 -- ' OR theme = 'Paris' OR 1=1 -- '
```

- **L'apostrophe (`'`) :** Elle sert à "casser" ou fermer prématurément la chaîne de caractères (les guillemets) ouverte par le développeur dans le code PHP. Cela permet de "sortir" de la simple recherche de texte pour insérer du code SQL exécutable juste après.
- **`OR 1=1` :** C'est une tautologie (une condition qui est toujours VRAIE mathématiquement). La clause WHERE de la requête devient soudainement VRAIE pour chaque ligne de la table, poussant MySQL à renvoyer 100% des lignes de la base.
- **Les deux tirets (`-- `) :** C'est le marqueur de commentaire en SQL (avec l'espace obligatoire après). Il court-circuite et ignore toute la suite de la requête originale écrite par le développeur (notamment le `' OR theme = '...`), évitant ainsi à MySQL de planter ou d'afficher une erreur de syntaxe sur les guillemets résiduels.

## Questions supplémentaires 

**1. Justifiez l'utilisation d'une jointure de type LEFT JOIN pour lister l'ensemble des événements satellites.**  
Le LEFT JOIN permet de garder tous les événements de la table mère (EVENEMENT) même s'ils n'ont pas encore de détails dans les tables filles (INITIATION ou CONFERENCE). Sinon avec un JOIN classique on ne verrait que les événements déjà remplis.

**2. Pourquoi le choix de la date et l'heure comme clé d'un dictionnaire est-il pertinent pour le planning d'un participant ?**  
Dans un dictionnaire chaque clé doit être unique. En utilisant l'heure comme clé, si on essaie de mettre deux activités au même moment, la nouvelle écrase l'ancienne. Ca garantit qu'il n'y a pas de doublon d'heure dans le planning affiché.

**3. Expliquez comment le déclencheur tg_check_capacite garantit que la limite de places d'un hackathon est respectée.**  
Le trigger vérifie en BEFORE INSERT sur la table INSCRIRE si le nombre d'inscrits ne dépasse pas la colonne nbPlacesMax de la table HACKATHON. Si c'est le cas, il bloque l'enregistrement pour pas qu'il y ait trop de monde. (Erreur : il regarde HACKATHON au lieu de INITIATION).

**4. En quoi l'export des données personnelles des membres vers une application publique constitue-t-il une violation du RGPD ?**  
On ne doit pas envoyer des infos comme l'email ou le téléphone si c'est pas utile (principe de minimisation). Surtout que la loi interdit de stocker des emails sans avoir l'accord écrit de la CNIL pour chaque utilisateur. (Erreur : confusion sur l'obligation CNIL).

**5. Démontrez l'impact d'une saisie utilisateur contenant des guillemets sur une chaîne JSON construite manuellement.**  
Si on fait du JSON à la main par concaténation, un guillemet dans le texte va fermer la valeur trop tôt. Par exemple `{"titre": "L'atelier "IA""}` va faire une erreur de syntaxe et l'application ne pourra plus lire les données.

**6. Pourquoi la fonction native json_encode est-elle préférable à une construction de flux par concaténation ?**  
Parce que json_encode s'occupe de tout transformer proprement, surtout les caractères spéciaux. On n'a pas à se soucier des guillemets ou des retours à la ligne, PHP le fait tout seul et le JSON est toujours correct.

**7. Quel est l'intérêt d'utiliser des transactions SQL lors de l'ajout d'un événement dans les tables mère et fille ?**  
Ca permet de lier les deux requêtes INSERT. Si la deuxième échoue, la première est annulée (rollback). Comme ça on n'a pas d'événement "moitié créé" dans la base de données, c'est tout ou rien.

**8. Expliquez comment le mécanisme des requêtes préparées avec PDO neutralise physiquement une injection SQL.**  
PDO analyse la requête avant d'envoyer les données. Il va automatiquement rajouter des antislashs devant les apostrophes du pirate pour qu'elles soient lues comme du texte simple et pas comme une commande SQL. (Erreur : confusion avec l'échappement manuel).

**9. Quel est l'intérêt de placer le serveur de bases de données dans une zone privée, séparée de la DMZ ?**  
C'est pour la sécurité. Si quelqu'un arrive à pirater le serveur web qui est exposé (DMZ), il ne pourra pas accéder directement à la base de données car elle est protégée derrière un autre réseau privé non accessible depuis dehors.

**10. Décrivez la méthodologie TDD (Test Driven Development) utilisée pour corriger les erreurs de la classe Initiation.**  
On crée d'abord un test qui échoue, puis on code juste ce qu'il faut pour que le test passe (au vert). Ca permet de corriger les bugs un par seul en étant sûr que le reste fonctionne toujours.
