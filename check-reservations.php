<?php
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli('localhost', 'root', '', 'icedog');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Erreur connexion: ' . $conn->connect_error]));
}

// Vérifier les réservations
$result = $conn->query("SELECT id, dog_name, dog_breed, service_type, appointment_date, status, payment_status, created_at FROM reservations ORDER BY id DESC LIMIT 10");

$reservations = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
}

echo json_encode([
    'total' => count($reservations),
    'reservations' => $reservations
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
