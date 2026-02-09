<?php
// Script d'installation de la base de données

header('Content-Type: application/json; charset=utf-8');

try {
    // Connexion à MySQL sans base
    $conn = new mysqli('localhost', 'root', '', 'mysql');
    
    if ($conn->connect_error) {
        throw new Exception("Erreur connexion: " . $conn->connect_error);
    }
    
    // Lire et exécuter database.sql
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Diviser les requêtes
    $queries = array_filter(
        array_map('trim', explode(';', $sql)),
        function($q) { return !empty($q) && strpos($q, '--') !== 0; }
    );
    
    $success = 0;
    $errors = [];
    
    foreach ($queries as $query) {
        if ($conn->query($query) === false) {
            $errors[] = "Erreur: " . $conn->error . " | Query: " . substr($query, 0, 50);
        } else {
            $success++;
        }
    }
    
    // Vérifier les tables
    $result = $conn->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='icedog'");
    $tables = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tables[] = $row['TABLE_NAME'];
        }
    }
    
    $response = [
        'success' => count($errors) === 0,
        'message' => $success . ' requêtes exécutées',
        'errors' => $errors,
        'tables' => $tables
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
