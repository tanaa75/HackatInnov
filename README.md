# Étude de cas HackatInnov - Réponses

## A.1
A faire plus tard.

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
A faire plus tard.

## A.4
A faire plus tard.

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

## Questions supplémentaires (Jury)

**1. Justifiez l’utilisation d’une jointure de type LEFT JOIN pour lister l’ensemble des événements satellites.**  
Un `LEFT JOIN` (ou jointure ouverte) permet de récupérer toutes les lignes de la table de gauche (ici la table-mère `EVENEMENT`), même s'il n'y a pas de correspondance dans les tables filles de droite (`INITIATION` ou `CONFERENCE`). Si on avait utilisé un simple `INNER JOIN`, on perdrait les événements qui n'ont pas encore été définis spécifiquement ou on n'afficherait que des conférences et aucune initiation, car la jointure stricte masquerait les relations "vides".

**2. Pourquoi le choix de la date et l’heure comme clé d’un dictionnaire est-il pertinent pour le planning d’un participant ?**  
Dans un tableau associatif PHP (dictionnaire), la règle d'or est que chaque clé est absolument unique. En définissant l'heure de début d'une activité comme clé, le dictionnaire garantit techniquement l'unicité des créneaux. Si un participant tente par erreur d'assister à deux activités qui démarrent à la même heure, la deuxième viendra simplement écraser la première ligne, ce qui assure que le planning affiché sera toujours cohérent et sans conflit temporel.

**3. Expliquez comment le déclencheur tg_check_capacite garantit que la limite de places d’un hackathon est respectée.**  
Le déclencheur `tg_check_capacite` est paramétré sur l'événement logique `BEFORE INSERT` sur la table `INSCRIRE`. Avant de rajouter l'inscription d'un membre à une activité, le SGBD va compter dynamiquement les participants existants. Ensuite, il va comparer ce chiffre avec le champ `nbPlacesMax` de la table `HACKATHON`. Si la jauge est dépassée, un `SIGNAL SQLSTATE` bloque l'insertion, protégeant ainsi l'intégrité de la limite de personnes.

**4. En quoi l’export des données personnelles des membres vers une application publique constitue-t-il une violation du RGPD ?**  
Le RGPD base sa philosophie sur le principe strict de "minimisation" de la donnée : on ne distribue que ce qui est utile. Ici l'API est destinée à une lecture publique via application mobile, il n'y a aucune raison d'exposer les numéros de téléphone et emails. De plus, c'est une violation manifeste parce que la loi informatique stipule qu'il est formellement interdit de stocker des emails en clair dans une base MySQL locale sans l'accord direct de la CNIL.

**5. Démontrez l’impact d’une saisie utilisateur contenant des guillemets sur une chaîne JSON construite manuellement.**  
Le format JSON est un protocole de chaîne de paires "clé" : "valeur" où chaque valeur est isolée par des doubles guillemets. Si un organisateur construit textuellement son compte rendu, comme par exemple `{"libelle": "` + $saisie + `"}`, et qu'il tape comme titre `L'atelier "Blockchain"`, ça donne `{"libelle": "L'atelier "Blockchain""}`. Les guillemets présents dans le titre vont fermer précipitamment la structure JSON. L'application mobile (Vue.js) ne reconnaîtra plus son format, ce qui entraînera une erreur de syntaxe fatale (SyntaxError) et fera planter tout le front-end. 

**6. Pourquoi la fonction native json_encode est-elle préférable à une construction de flux par concaténation ?**  
La fonction native `json_encode()` en PHP convertit directement en flux JSON un objet ou un tableau (array), garantissant ainsi les standards de la norme. Surtout, si un utilisateur met un guillemet double ou des antislashs, la fonction les "échappe" avec succès (elle les transforme en `\"`), évitant littéralement toutes les brisures du format ou les crashs côté script Client abordés à la question précédente.

**7. Quel est l’intérêt d’utiliser des transactions SQL lors de l’ajout d’un événement dans les tables mère et fille ?**  
L'ajout d'un événement se fait sur une structure hiérarchique avec deux requêtes `INSERT` successives (l'une pour la table générique mère, et l'autre pour la table spécifique fille). L'intérêt fondamental de la transaction SQL (`BEGIN`, `COMMIT`, `ROLLBACK`) est sécuritaire. Elle va garantir l'atomicité systémique. Si la requête pour "la mère" passe mais que le réseau tombe au moment d'insérer dans "la fille", la transaction va s'annuler d'elle-même (Rollback). Elle empêche les données "orphelines" non cohérentes dans la base.

**8. Expliquez comment le mécanisme des requêtes préparées avec PDO neutralise physiquement une injection SQL.**  
Les requêtes préparées coupent le lien de dangerosité entre la commande SQL et les données. La protection opère dans PDO : lorsqu'on ajoute un paramètre à requêter, PDO gère la manipulation et prend notre variable pour rajouter un système d'antislashs (`\`) derrière tous les caractères dangereux comme les quotes, les tirets ou les chevrons. En faisant ça, le pirate est impuissant car il ne peut plus sortir de la variable textuelle.

**9. Quel est l’intérêt de placer le serveur de bases de données dans une zone privée, séparée de la DMZ ?**  
L'architecture DMZ vise à héberger exclusivement les composantes qui ont vocation à communiquer directement avec l'extérieur ou le public (comme le Serveur Web ou API). Mettre la base de données dans le LAN restreint permet de créer un cloisonnement défensif (un deuxième pare-feu physique). Si un pirate identifie une faille sur un système web, ou lance un Déni de Service (DDoS), la base MySQL, vitale et centrale, demeure isolée, invisible d'Internet, et totalement protégée derrière le réseau interne fermé.

**10. Décrivez la méthodologie TDD (Test Driven Development) utilisée pour corriger les erreurs de la classe Initiation.**  
Cette méthode agile implique de concevoir le développement via des tests automatisés (par exemple ici avec l'outil de référence PHPUnit). Au lieu de programmer d'instinct, les problèmes originels de logiques "invisibles" nous ont été signalés par le framework (bouton rouge). Nous avons lu ces erreurs dans le test (un ajout de participant qui persistait au-delà de la capacité, un matériel au compte mal établi), et par la suite modifié les conditions de notre classe métier `Initiation` (`< nbPlaces` ou `unMateriel > 0`) jusqu'à ce que les conditions passent au "Vert".
