<?php
declare(strict_types=1);

include_once __DIR__ . "/../util/bdd.php";

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

try {
    $pdo = connexionBdd();

    // Récupération sécurisée (filtrage)
    $critere = filter_input(INPUT_GET, 'recherche', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

    // Requête préparée (Sécurité : Empêche l'IA SQL)
    $sql = 'SELECT DATE_FORMAT(dateHeureDebut, "%d/%m/%Y") AS dateDebut, ville, theme
            FROM HACKATHON
            WHERE ville LIKE :critere OR theme LIKE :critere
            ORDER BY dateHeureDebut, ville';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':critere', '%' . $critere . '%', PDO::PARAM_STR);
    $stmt->execute();

    $rows = $stmt->fetchAll();

    echo json_encode($rows, JSON_THROW_ON_ERROR);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erreur' => 'Erreur serveur lors de la récupération des données.']);
}
?>
