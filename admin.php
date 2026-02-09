<?php
/**
 * API ICE DOG - Gestion des réservations et formulaires de contact
 */

// Démarrer la mise en buffer pour éviter les erreurs HTML
ob_start();

// Configuration des en-têtes
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestion des erreurs - NE PAS afficher les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'icedog');
define('EMAIL_CONTACT', 'icedog241@gmail.com');
define('EMAIL_ADMIN', 'adamnelson2k5@gmail.com');

// Fonction pour retourner une réponse JSON
function sendJson($success, $message, $data = null) {
    ob_clean(); // Nettoyer tout output précédent
    header('Content-Type: application/json; charset=utf-8');
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

// Connexion à la DB
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        sendJson(false, 'Erreur de connexion à la base de données');
    }
    $conn->set_charset("utf8");
    return $conn;
}

// Vérifier si les tables existent
function verifyTables($conn) {
    $tables_needed = ['reservations', 'subscriptions', 'contacts'];
    
    foreach ($tables_needed as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            return false;
        }
    }
    return true;
}

// Fonction pour envoyer les emails
function sendEmail($to, $subject, $message, $senderEmail = null) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . EMAIL_CONTACT . "\r\n";
    $headers .= "Reply-To: " . EMAIL_CONTACT . "\r\n";
    $headers .= "X-Mailer: ICE DOG API\r\n";
    
    $success = mail($to, $subject, $message, $headers);
    
    if ($success) {
        error_log("Email envoyé à $to: $subject");
    } else {
        error_log("Erreur lors de l'envoi de l'email à $to: $subject");
    }
    
    return $success;
}

// Fonction pour générer un HTML formaté pour les emails de réservation
function generateReservationEmail($data) {
    $date = isset($data['date']) ? date('d/m/Y', strtotime($data['date'])) : 'N/A';
    $html = "
    <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
        <h2 style='color: #DC143C;'>Nouvelle Réservation - ICE DOG</h2>
        
        <h3>Informations du Client</h3>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 10px; border: 1px solid #ddd;'>Nom</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['nom']) . "</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd;'>Email</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['email']) . "</td>
            </tr>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 10px; border: 1px solid #ddd;'>Téléphone</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['telephone']) . "</td>
            </tr>
        </table>
        
        <h3 style='margin-top: 20px;'>Informations du Chien</h3>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 10px; border: 1px solid #ddd;'>Nom du Chien</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['nomChien']) . "</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd;'>Race</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['race']) . "</td>
            </tr>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 10px; border: 1px solid #ddd;'>Âge</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['age']) . " ans</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd;'>Poids</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['poids']) . " kg</td>
            </tr>
        </table>
        
        <h3 style='margin-top: 20px;'>Détails de la Réservation</h3>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 10px; border: 1px solid #ddd;'>Service</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . (htmlspecialchars($data['service']) === 'forfait' ? 'Forfait Complet (20 000 F)' : 'Abonnement Mensuel (15 000 F)') . "</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd;'>Date</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>$date</td>
            </tr>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 10px; border: 1px solid #ddd;'>Heure</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['heure']) . "</td>
            </tr>
        </table>";
    
    if (!empty($data['message'])) {
        $html .= "<h3 style='margin-top: 20px;'>Message Spécial</h3>
        <p style='background-color: #f5f5f5; padding: 10px; border-left: 4px solid #DC143C;'>" . htmlspecialchars($data['message']) . "</p>";
    }
    
    $html .= "<hr style='margin-top: 20px; border: none; border-top: 1px solid #ddd;'>
    <p style='font-size: 12px; color: #666;'>Cet email a été généré automatiquement par le système ICE DOG. Merci de ne pas le modifier.</p>
    </div>";
    
    return $html;
}

// Fonction pour générer un HTML formaté pour les emails de contact
function generateContactEmail($data) {
    $html = "
    <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
        <h2 style='color: #DC143C;'>Nouveau Message de Contact - ICE DOG</h2>
        
        <table style='width: 100%; border-collapse: collapse;'>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 10px; border: 1px solid #ddd;'>Nom</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['nom']) . "</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd;'>Email</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['email']) . "</td>
            </tr>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 10px; border: 1px solid #ddd;'>Sujet</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['sujet']) . "</td>
            </tr>
        </table>
        
        <h3 style='margin-top: 20px;'>Message</h3>
        <p style='background-color: #f5f5f5; padding: 15px; border-left: 4px solid #DC143C;'>" . nl2br(htmlspecialchars($data['message'])) . "</p>
        
        <hr style='margin-top: 20px; border: none; border-top: 1px solid #ddd;'>
        <p style='font-size: 12px; color: #666;'>Cet email a été généré automatiquement par le système ICE DOG. Répondez à l'adresse email du client.</p>
    </div>";
    
    return $html;
}

// Récupérer l'action demandée
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Route: Créer une réservation
if ($action === 'create_reservation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'nom' => $_POST['nom'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'nomChien' => $_POST['nomChien'] ?? '',
            'race' => $_POST['race'] ?? '',
            'age' => intval($_POST['age'] ?? 0),
            'poids' => floatval($_POST['poids'] ?? 0),
            'service' => $_POST['service'] ?? 'forfait',
            'date' => $_POST['date'] ?? '',
            'heure' => $_POST['heure'] ?? '',
            'message' => $_POST['message'] ?? ''
        ];

        $conn = getDBConnection();
        
        if (!verifyTables($conn)) {
            throw new Exception('Tables de base de données manquantes');
        }
        
        $sql = "INSERT INTO reservations (
            dog_name, dog_breed, dog_age, dog_weight, 
            service_type, appointment_date, appointment_time, price, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erreur prepare: " . $conn->error);
        }
        
        // Bind: s=string, i=int, d=double
        $price = (double)$_POST['price'];
        $stmt->bind_param(
            'ssidsssd',
            $data['nomChien'],
            $data['race'],
            $data['age'],
            $data['poids'],
            $data['service'],
            $data['date'],
            $data['heure'],
            $price
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur execute: " . $stmt->error);
        }
        
        // Sauvegarder le contact client
        $sql_contact = "INSERT INTO contacts (name, email, phone, subject, message, status, created_at) 
                       VALUES (?, ?, ?, ?, ?, 'new', NOW())";
        $stmt_contact = $conn->prepare($sql_contact);
        $subject = "Nouvelle réservation - " . $data['nomChien'];
        $stmt_contact->bind_param('sssss', $data['nom'], $data['email'], $data['telephone'], $subject, $data['message']);
        $stmt_contact->execute();
        
        // Envoyer l'email à l'admin
        $adminSubject = "Nouvelle Réservation - " . htmlspecialchars($data['nomChien']);
        $adminHtml = generateReservationEmail($data);
        sendEmail(EMAIL_ADMIN, $adminSubject, $adminHtml);
        
        // Envoyer un email de confirmation au client
        $clientSubject = "Confirmation de votre réservation - ICE DOG";
        $clientHtml = "<div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
            <h2>Merci pour votre réservation !</h2>
            <p>Bonjour <strong>" . htmlspecialchars($data['nom']) . "</strong>,</p>
            <p>Nous avons bien reçu votre demande de réservation pour <strong>" . htmlspecialchars($data['nomChien']) . "</strong>.</p>
            <p><strong>Récapitulatif:</strong></p>
            <ul>
                <li>Date: " . date('d/m/Y', strtotime($data['date'])) . "</li>
                <li>Heure: " . htmlspecialchars($data['heure']) . "</li>
                <li>Service: " . (htmlspecialchars($data['service']) === 'forfait' ? 'Forfait Complet (20 000 F)' : 'Abonnement Mensuel (15 000 F)') . "</li>
            </ul>
            <p>Nous vous contacterons sous peu pour confirmer votre rendez-vous.</p>
            <p>Cordialement,<br><strong>L'équipe ICE DOG</strong></p>
        </div>";
        sendEmail($data['email'], $clientSubject, $clientHtml);
        
        $conn->close();
        sendJson(true, 'Réservation enregistrée');
    } catch (Exception $e) {
        sendJson(false, 'Erreur de réservation: ' . $e->getMessage());
    }
}

// Route: Créer un abonnement
if ($action === 'create_subscription' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'nom' => $_POST['nom'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'nomChien' => $_POST['nomChien'] ?? '',
            'race' => $_POST['race'] ?? '',
            'age' => intval($_POST['age'] ?? 0),
            'poids' => floatval($_POST['poids'] ?? 0),
            'type' => $_POST['type'] ?? 'monthly'
        ];

        $conn = getDBConnection();
        
        if (!verifyTables($conn)) {
            throw new Exception('Tables de base de données manquantes');
        }
        
        $price = 15000; // Prix de l'abonnement mensuel
        $start_date = date('Y-m-d');
        
        $sql = "INSERT INTO subscriptions (
            dog_name, dog_breed, dog_age, dog_weight, 
            subscription_type, start_date, price, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erreur prepare: " . $conn->error);
        }
        
        // Bind: s=string, i=int, d=double
        $stmt->bind_param(
            'ssidssd',
            $data['nomChien'],
            $data['race'],
            $data['age'],
            $data['poids'],
            $data['type'],
            $start_date,
            $price
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur execute: " . $stmt->error);
        }
        
        // Sauvegarder le contact client
        $sql_contact = "INSERT INTO contacts (name, email, phone, subject, message, status, created_at) 
                       VALUES (?, ?, ?, ?, ?, 'new', NOW())";
        $stmt_contact = $conn->prepare($sql_contact);
        $subject = "Nouvel abonnement - " . $data['nomChien'];
        $message = "Demande d'abonnement mensuel";
        $stmt_contact->bind_param('sssss', $data['nom'], $data['email'], $data['telephone'], $subject, $message);
        $stmt_contact->execute();
        
        // Envoyer l'email à l'admin
        $adminSubject = "Nouvel Abonnement - " . htmlspecialchars($data['nomChien']);
        $adminHtml = "<div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
            <h2 style='color: #DC143C;'>Nouvel Abonnement Mensuel - ICE DOG</h2>
            
            <h3>Informations du Client</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr style='background-color: #f5f5f5;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Nom</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['nom']) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Email</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['email']) . "</td>
                </tr>
                <tr style='background-color: #f5f5f5;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Téléphone</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['telephone']) . "</td>
                </tr>
            </table>
            
            <h3 style='margin-top: 20px;'>Informations du Chien</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr style='background-color: #f5f5f5;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Nom du Chien</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['nomChien']) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Race</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['race']) . "</td>
                </tr>
                <tr style='background-color: #f5f5f5;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Âge</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['age']) . " ans</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Poids</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($data['poids']) . " kg</td>
                </tr>
            </table>
            
            <h3 style='margin-top: 20px;'>Détails de l'Abonnement</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr style='background-color: #f5f5f5;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Type</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Abonnement Mensuel</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Prix</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>15 000 F par mois</td>
                </tr>
                <tr style='background-color: #f5f5f5;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'>Date de Début</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . date('d/m/Y') . "</td>
                </tr>
            </table>
            
            <hr style='margin-top: 20px; border: none; border-top: 1px solid #ddd;'>
            <p style='font-size: 12px; color: #666;'>Cet email a été généré automatiquement par le système ICE DOG.</p>
        </div>";
        sendEmail(EMAIL_ADMIN, $adminSubject, $adminHtml);
        
        // Envoyer un email de confirmation au client
        $clientSubject = "Confirmation de votre abonnement mensuel - ICE DOG";
        $clientHtml = "<div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
            <h2>Bienvenue dans nos abonnés !</h2>
            <p>Bonjour <strong>" . htmlspecialchars($data['nom']) . "</strong>,</p>
            <p>Nous avons bien reçu votre demande d'abonnement mensuel pour <strong>" . htmlspecialchars($data['nomChien']) . "</strong>.</p>
            <p><strong>Détails de votre abonnement:</strong></p>
            <ul>
                <li>Type: Abonnement Mensuel</li>
                <li>Prix: 15 000 F par mois (au lieu de 20 000 F)</li>
                <li>Fréquence: Une visite par mois</li>
                <li>Avantage: Tarif réduit et passage mensuel garanti</li>
            </ul>
            <p>Nous vous contacterons sous peu pour programmer vos rendez-vous mensuels.</p>
            <p>Merci de votre confiance !<br><strong>L'équipe ICE DOG</strong></p>
        </div>";
        sendEmail($data['email'], $clientSubject, $clientHtml);
        
        $conn->close();
        sendJson(true, 'Abonnement enregistré');
    } catch (Exception $e) {
        sendJson(false, 'Erreur d\'abonnement: ' . $e->getMessage());
    }
}

// Route: Créer un message de contact
if ($action === 'create_contact' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'nom' => $_POST['nom'] ?? '',
            'email' => $_POST['email'] ?? '',
            'sujet' => $_POST['sujet'] ?? '',
            'message' => $_POST['message'] ?? ''
        ];

        // Validation
        if (!$data['nom'] || !$data['email'] || !$data['sujet'] || !$data['message']) {
            sendJson(false, 'Tous les champs sont obligatoires');
        }

        $conn = getDBConnection();
        
        if (!verifyTables($conn)) {
            throw new Exception('Tables de base de données manquantes');
        }
        
        $sql = "INSERT INTO contacts (name, email, phone, subject, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'new', NOW())";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erreur prepare: " . $conn->error);
        }
        
        $phone = ''; // Pas de téléphone pour les messages de contact
        $stmt->bind_param('sssss', $data['nom'], $data['email'], $phone, $data['sujet'], $data['message']);
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur execute: " . $stmt->error);
        }
        
        // Envoyer l'email à l'admin
        $adminSubject = "Nouveau Message de Contact - " . htmlspecialchars($data['sujet']);
        $adminHtml = generateContactEmail($data);
        sendEmail(EMAIL_ADMIN, $adminSubject, $adminHtml);
        
        // Envoyer un email de confirmation au client
        $clientSubject = "Nous avons reçu votre message - ICE DOG";
        $clientHtml = "<div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
            <h2>Merci pour votre message !</h2>
            <p>Bonjour <strong>" . htmlspecialchars($data['nom']) . "</strong>,</p>
            <p>Nous avons bien reçu votre message concernant: <strong>" . htmlspecialchars($data['sujet']) . "</strong></p>
            <p>Notre équipe examinera votre demande et vous répondra dans les 24 heures.</p>
            <p>Si votre question est urgente, n'hésitez pas à nous appeler au <strong>065 77 80 10</strong>.</p>
            <p>Cordialement,<br><strong>L'équipe ICE DOG</strong></p>
        </div>";
        sendEmail($data['email'], $clientSubject, $clientHtml);
        
        $conn->close();
        sendJson(true, 'Message envoyé avec succès');
    } catch (Exception $e) {
        sendJson(false, 'Erreur de contact: ' . $e->getMessage());
    }
}

// Réponse par défaut - Action non reconnue
if (empty($action)) {
    sendJson(false, 'Action non spécifiée');
} else {
    sendJson(false, 'Action non reconnue: ' . $action);
}
?>


