<?php
declare(strict_types=1);

require_once __DIR__ . '/util/bdd.php';

$pdo = connexionBdd();
$message = '';
$typeMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idHackathon   = filter_input(INPUT_POST, 'idHackathon', FILTER_VALIDATE_INT);
    $idAnimateur   = filter_input(INPUT_POST, 'idAnimateur', FILTER_VALIDATE_INT);
    $libelle       = filter_input(INPUT_POST, 'libelle', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $dateHeure     = filter_input(INPUT_POST, 'dateHeure', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $duree         = filter_input(INPUT_POST, 'duree', FILTER_VALIDATE_INT);
    $salle         = filter_input(INPUT_POST, 'salle', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $typePublic    = filter_input(INPUT_POST, 'typePublic', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $typeEvenement = filter_input(INPUT_POST, 'typeEvenement', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $theme         = filter_input(INPUT_POST, 'theme', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $nbPlaces      = filter_input(INPUT_POST, 'nbPlaces', FILTER_VALIDATE_INT);

    if ($idHackathon && $idAnimateur && $libelle && $dateHeure && $duree && $typeEvenement) {
        try {
            $pdo->beginTransaction();

            $sqlEvt = "INSERT INTO EVENEMENT (idHackathon, idAnimateur, libelle, dateHeure, duree, salle, typePublic)
                       VALUES (:idHackathon, :idAnimateur, :libelle, :dateHeure, :duree, :salle, :typePublic)";
            $stmtEvt = $pdo->prepare($sqlEvt);
            $stmtEvt->execute([
                ':idHackathon' => $idHackathon,
                ':idAnimateur' => $idAnimateur,
                ':libelle'     => $libelle,
                ':dateHeure'   => $dateHeure,
                ':duree'       => $duree,
                ':salle'       => $salle,
                ':typePublic'  => $typePublic
            ]);

            $idEvenementCree = (int) $pdo->lastInsertId();

            if ($typeEvenement === 'conference') {
                $sqlConf = "INSERT INTO CONFERENCE (idEvenementConf, theme) VALUES (:id, :theme)";
                $stmtConf = $pdo->prepare($sqlConf);
                $stmtConf->execute([':id' => $idEvenementCree, ':theme' => $theme]);
            } elseif ($typeEvenement === 'initiation') {
                $sqlInit = "INSERT INTO INITIATION (idEvenementInit, nbPlaces) VALUES (:id, :nbPlaces)";
                $stmtInit = $pdo->prepare($sqlInit);
                $stmtInit->execute([':id' => $idEvenementCree, ':nbPlaces' => $nbPlaces]);
            }

            $pdo->commit();
            $message = "L'evenement '$libelle' a ete ajoute avec succes !";
            $typeMessage = "success";

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Erreur lors de l'ajout : " . $e->getMessage();
            $typeMessage = "danger";
        }
    } else {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $typeMessage = "warning";
    }
}

$hackathons = $pdo->query("SELECT id, ville, theme FROM HACKATHON ORDER BY dateHeureDebut DESC")->fetchAll();
$animateurs = $pdo->query("SELECT id, nom, prenom FROM MEMBRE ORDER BY nom ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Hackat'Innov</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="mb-4 text-primary text-center">Ajouter un Evenement Satellite</h2>

            <?php if ($message): ?>
                <div class="alert alert-<?= $typeMessage ?> alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body p-4">
                    <form action="admin_evenement.php" method="POST">

                        <h5 class="border-bottom pb-2 mb-3">Informations Generales</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Hackathon associe *</label>
                                <select name="idHackathon" class="form-select" required>
                                    <option value="">-- Choisir un Hackathon --</option>
                                    <?php foreach ($hackathons as $h): ?>
                                        <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['ville'] . ' - ' . $h['theme']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Animateur responsable *</label>
                                <select name="idAnimateur" class="form-select" required>
                                    <option value="">-- Choisir un Animateur --</option>
                                    <?php foreach ($animateurs as $a): ?>
                                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nom'] . ' ' . $a['prenom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Titre de l'evenement *</label>
                            <input type="text" name="libelle" class="form-control" required placeholder="Ex: Introduction a Docker">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date et Heure *</label>
                                <input type="datetime-local" name="dateHeure" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Duree (en minutes) *</label>
                                <input type="number" name="duree" class="form-control" required min="15" value="120">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Salle</label>
                                <input type="text" name="salle" class="form-control" placeholder="Ex: Hedy Lamarr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type de public</label>
                                <input type="text" name="typePublic" class="form-control" placeholder="Ex: Etudiants debutants">
                            </div>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3">Specificites de l'evenement</h5>

                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="typeEvenement" id="radioConf" value="conference" checked onchange="toggleFields()">
                                <label class="form-check-label" for="radioConf">Conference</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="typeEvenement" id="radioInit" value="initiation" onchange="toggleFields()">
                                <label class="form-check-label" for="radioInit">Initiation</label>
                            </div>
                        </div>

                        <div class="mb-3" id="divTheme">
                            <label class="form-label">Theme de la conference *</label>
                            <input type="text" name="theme" id="inputTheme" class="form-control" required>
                        </div>

                        <div class="mb-3 d-none" id="divPlaces">
                            <label class="form-label">Nombre de places maximum *</label>
                            <input type="number" name="nbPlaces" id="inputPlaces" class="form-control" min="1">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Creer l'evenement</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFields() {
    const isConf = document.getElementById('radioConf').checked;
    const divTheme = document.getElementById('divTheme');
    const inputTheme = document.getElementById('inputTheme');
    const divPlaces = document.getElementById('divPlaces');
    const inputPlaces = document.getElementById('inputPlaces');

    if (isConf) {
        divTheme.classList.remove('d-none');
        inputTheme.required = true;
        divPlaces.classList.add('d-none');
        inputPlaces.required = false;
        inputPlaces.value = '';
    } else {
        divTheme.classList.add('d-none');
        inputTheme.required = false;
        inputTheme.value = '';
        divPlaces.classList.remove('d-none');
        inputPlaces.required = true;
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
