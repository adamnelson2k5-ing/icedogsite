<?php
/**
 * Page Admin ICE DOG - Gestion des abonnements, messages et réservations
 * Avec système de code d'accès intégré
 */

session_start();

// Code admin à entrer
define('ADMIN_CODE', '2025');

// Configuration
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'icedog');

// Classe pour accéder à la base de données - DÉFINIE AU DÉBUT
class AdminDB {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if ($this->conn->connect_error) {
            die('Erreur de connexion: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }

    public function getSubscriptions() {
        $sql = "SELECT * FROM subscriptions ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getContacts() {
        $sql = "SELECT * FROM contacts ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getReservations($start_date = null, $end_date = null) {
        $sql = "SELECT * FROM reservations WHERE 1=1";
        
        if ($start_date) {
            $start_date = $this->conn->real_escape_string($start_date);
            $sql .= " AND appointment_date >= '$start_date'";
        }
        
        if ($end_date) {
            $end_date = $this->conn->real_escape_string($end_date);
            $sql .= " AND appointment_date <= '$end_date'";
        }
        
        $sql .= " ORDER BY appointment_date DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function updatePaymentStatus($reservation_id, $payment_status) {
        $reservation_id = (int)$reservation_id;
        $payment_status = $this->conn->real_escape_string($payment_status);
        
        $sql = "UPDATE reservations SET payment_status = '$payment_status' WHERE id = $reservation_id";
        return $this->conn->query($sql);
    }

    public function updateReservationStatus($reservation_id, $status) {
        $reservation_id = (int)$reservation_id;
        $status = $this->conn->real_escape_string($status);
        
        $sql = "UPDATE reservations SET status = '$status' WHERE id = $reservation_id";
        return $this->conn->query($sql);
    }

    public function updateSubscriptionStatus($subscription_id, $status) {
        $subscription_id = (int)$subscription_id;
        $status = $this->conn->real_escape_string($status);
        
        $sql = "UPDATE subscriptions SET status = '$status' WHERE id = $subscription_id";
        return $this->conn->query($sql);
    }

    public function updateContactStatus($contact_id, $status) {
        $contact_id = (int)$contact_id;
        $status = $this->conn->real_escape_string($status);
        
        $sql = "UPDATE contacts SET status = '$status' WHERE id = $contact_id";
        return $this->conn->query($sql);
    }

    public function close() {
        $this->conn->close();
    }
}

// Vérifier si l'utilisateur est authentifié (avant le logout)
$is_authenticated = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Initialiser les variables de login
$show_login_form = !$is_authenticated;
$login_error = '';

// Traiter la soumission du formulaire de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_code']) && !$is_authenticated) {
    if ($_POST['admin_code'] === ADMIN_CODE) {
        $_SESSION['admin_logged_in'] = true;
        $is_authenticated = true;
        $show_login_form = false;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = 'Code d\'accès incorrect';
        $show_login_form = true;
    }
}

// Traiter les actions AJAX si authentifiée (APRÈS le login)
if ($is_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $db = new AdminDB();
    
    if ($_POST['action'] === 'update_payment_status') {
        $reservation_id = $_POST['reservation_id'] ?? 0;
        $payment_status = $_POST['payment_status'] ?? '';
        
        if ($db->updatePaymentStatus($reservation_id, $payment_status)) {
            echo json_encode(['success' => true, 'message' => 'Statut de paiement mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }
    
    if ($_POST['action'] === 'update_reservation_status') {
        $reservation_id = $_POST['reservation_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if ($db->updateReservationStatus($reservation_id, $status)) {
            echo json_encode(['success' => true, 'message' => 'Réservation ' . ($status === 'confirmed' ? 'confirmée' : 'rejetée')]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }
    
    if ($_POST['action'] === 'update_subscription_status') {
        $subscription_id = $_POST['subscription_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if ($db->updateSubscriptionStatus($subscription_id, $status)) {
            $message_text = '';
            if ($status === 'active') $message_text = 'réactivée';
            elseif ($status === 'paused') $message_text = 'suspendue';
            elseif ($status === 'cancelled') $message_text = 'annulée';
            echo json_encode(['success' => true, 'message' => 'Abonnement ' . $message_text]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }
    
    if ($_POST['action'] === 'update_contact_status') {
        $contact_id = $_POST['contact_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if ($db->updateContactStatus($contact_id, $status)) {
            $message_text = '';
            if ($status === 'new') $message_text = 'marqué comme nouveau';
            elseif ($status === 'read') $message_text = 'marqué comme lu';
            elseif ($status === 'archived') $message_text = 'archivé';
            echo json_encode(['success' => true, 'message' => 'Message ' . $message_text]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
    }
    
    $db->close();
    exit;
}

// MAINTENANT vérifier la déconnexion (APRÈS les actions AJAX)
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $_SESSION['admin_logged_in'] = false;
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Récupérer les données uniquement si authentifié
$subscriptions = [];
$contacts = [];
$reservations = [];
$total_subscriptions = 0;
$active_subscriptions = 0;
$total_contacts = 0;
$new_contacts = 0;
$total_reservations = 0;
$pending_reservations = 0;

// Récupérer les filtres de date
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-90 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+30 days'));

if ($is_authenticated) {
    $db = new AdminDB();
    $subscriptions = $db->getSubscriptions();
    $contacts = $db->getContacts();
    $reservations = $db->getReservations($start_date, $end_date);
    $db->close();

    // Statistiques
    $total_subscriptions = count($subscriptions);
    $active_subscriptions = count(array_filter($subscriptions, fn($s) => $s['status'] === 'active'));
    $total_contacts = count($contacts);
    $new_contacts = count(array_filter($contacts, fn($c) => $c['status'] === 'new'));
    $total_reservations = count($reservations);
    $pending_reservations = count(array_filter($reservations, fn($r) => $r['status'] === 'pending'));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin ICE DOG - Tableau de Bord</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, rgba(220, 20, 60, 0.9) 0%, rgba(139, 0, 0, 0.9) 100%), url(icc.png);
            background-size: cover, cover;
            background-position: center, center;
            background-attachment: fixed, fixed;
            min-height: 100vh;
            padding: 20px;
            margin: 0;
            overflow-x: hidden;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        /* ========== RESPONSIVE IMAGES & MEDIA ========== */
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        video {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .header {
            background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .nav {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .nav a {
            text-decoration: none;
            color: #DC143C;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav a:hover, .nav a.active {
            background: #DC143C;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid #DC143C;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .stat-card .number {
            font-size: 2.5em;
            font-weight: bold;
            color: #DC143C;
            margin-bottom: 5px;
        }

        .stat-card .subtitle {
            color: #999;
            font-size: 0.85em;
        }

        .content {
            padding: 30px;
        }

        .section {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #DC143C;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            color: #666;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .status.active {
            background: #d4edda;
            color: #155724;
        }

        .status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status.new {
            background: #cfe2ff;
            color: #084298;
        }

        .status.confirmed {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status.completed {
            background: #d4edda;
            color: #155724;
        }

        .status.cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .status.paused {
            background: #e2e3e5;
            color: #41464b;
        }

        .status.replied {
            background: #d4edda;
            color: #155724;
        }

        .status.paid {
            background: #d4edda;
            color: #155724;
        }

        .status.unpaid {
            background: #f8d7da;
            color: #842029;
        }

        .payment-status-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background: white;
            color: #333;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 18px;
            padding-right: 35px;
        }

        .payment-status-select:hover {
            border-color: #DC143C;
            box-shadow: 0 2px 8px rgba(220, 20, 60, 0.15);
        }

        .payment-status-select:focus {
            outline: none;
            border-color: #DC143C;
            box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 2px;
            white-space: nowrap;
        }

        .confirm-btn {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .confirm-btn:hover {
            background: #c3e6cb;
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(21, 87, 36, 0.2);
        }

        .reject-btn {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c6cb;
        }

        .reject-btn:hover {
            background: #f5c6cb;
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(132, 32, 41, 0.2);
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 1.1em;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #DC143C;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: #8B0000;
        }

        .message-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                border-radius: 0;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 1.8em;
                margin-bottom: 5px;
            }

            .header p {
                font-size: 0.95em;
            }

            .nav {
                flex-direction: column;
                padding: 15px;
                gap: 10px;
            }

            .nav a {
                display: block;
                text-align: center;
                padding: 10px 12px;
                font-size: 0.95em;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 15px;
            }

            .content {
                padding: 20px;
            }

            .section h2 {
                font-size: 1.5em;
            }

            table {
                font-size: 0.85em;
                width: 100%;
            }

            th, td {
                padding: 8px;
                font-size: 0.85em;
            }

            .message-preview {
                max-width: 150px;
            }

            .action-btn {
                padding: 5px 8px;
                font-size: 11px;
                margin: 1px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 5px;
                background-attachment: scroll;
            }

            .container {
                border-radius: 5px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            }

            .header {
                padding: 15px;
            }

            .header h1 {
                font-size: 1.4em;
                margin-bottom: 5px;
            }

            .header p {
                font-size: 0.85em;
            }

            .nav {
                flex-direction: column;
                padding: 10px;
                gap: 8px;
            }

            .nav a {
                display: block;
                padding: 10px 8px;
                font-size: 0.85em;
                text-align: center;
                width: 100%;
            }

            .nav a.back-button {
                margin-left: 0;
                background: #DC143C;
                color: white;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                padding: 15px;
                gap: 12px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-card h3 {
                font-size: 0.8em;
            }

            .stat-card .number {
                font-size: 2em;
            }

            .content {
                padding: 15px;
            }

            .section h2 {
                font-size: 1.3em;
                margin-bottom: 15px;
            }

            table {
                width: 100%;
                font-size: 0.75em;
            }

            thead {
                display: block;
                position: absolute;
                left: -9999px;
            }

            tbody {
                display: block;
                width: 100%;
            }

            tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            td {
                display: block;
                text-align: right;
                padding: 8px;
                position: relative;
                padding-left: 40%;
                border: none;
                border-bottom: 1px solid #eee;
            }

            td:before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 40%;
                padding: 8px;
                background: #f8f9fa;
                font-weight: 600;
                color: #666;
                text-align: left;
                border-right: 1px solid #ddd;
            }

            .message-preview {
                max-width: 100%;
                white-space: normal;
            }

            .action-btn {
                display: inline-block;
                padding: 6px 8px;
                font-size: 10px;
                margin: 2px 0;
                width: calc(50% - 4px);
            }

            .login-container {
                max-width: 90%;
                padding: 30px 20px;
            }

            .login-icon {
                font-size: 3rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }

            .form-group-login input {
                font-size: 16px;
                padding: 10px 12px;
            }

            .submit-btn-login {
                padding: 10px;
                font-size: 0.95rem;
            }

            .empty-message {
                padding: 30px 15px;
                font-size: 0.95em;
            }
        }

        @media (max-width: 360px) {
            .header h1 {
                font-size: 1.2em;
            }

            .nav a {
                padding: 8px 4px;
                font-size: 0.75em;
            }

            .stat-card .number {
                font-size: 1.8em;
            }

            table {
                font-size: 0.7em;
            }

            td {
                padding: 6px;
                padding-left: 35%;
            }

            td:before {
                width: 35%;
                padding: 6px;
                font-size: 0.75em;
            }

            .action-btn {
                padding: 4px 6px;
                font-size: 9px;
                width: calc(50% - 3px);
            }

            .login-container {
                padding: 20px 15px;
                max-width: 95%;
            }
        }

        /* ========== LOGIN FORM STYLES ========== */
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            display: block;
        }

        .login-header h1 {
            color: #DC143C;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .form-group-login {
            margin-bottom: 20px;
        }

        .form-group-login label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group-login input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-group-login input:focus {
            outline: none;
            border-color: #DC143C;
            box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
        }

        .submit-btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 20, 60, 0.3);
        }

        .error-message-login {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f42;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .back-link-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-link-login a {
            color: #DC143C;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link-login a:hover {
            color: #8B0000;
        }
    </style>
</head>
<style>
    body{
        background-image: url(icc.png);
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }
</style>
<body>
    <?php if ($show_login_form): ?>
        <!-- FORMULAIRE DE CONNEXION -->
        <div style="display: flex; justify-content: center; align-items: center; min-height: 100vh;">
            <div class="login-container">
                <div class="login-header">
                    <span class="login-icon">🔐</span>
                    <h1>Admin ICE DOG</h1>
                    <p>Accès Réservé</p>
                </div>

                <?php if ($login_error): ?>
                    <div class="error-message-login">
                        ⚠️ <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group-login">
                        <label for="code">Code d'Accès</label>
                        <input 
                            type="password" 
                            id="code" 
                            name="admin_code" 
                            placeholder="Entrez le code d'accès" 
                            required 
                            autofocus
                            autocomplete="off"
                        >
                    </div>
                    <button type="submit" class="submit-btn-login">🔓 Accéder au Tableau de Bord</button>
                </form>

                <div class="back-link-login">
                    <a href="index.html">← Retour au Site</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- TABLEAU DE BORD -->
    <div class="container">
        <div class="header">
            <h1>🐕 ICE DOG - Tableau de Bord Admin</h1>
            <p>Gestion centralisée des abonnements, messages et réservations</p>
        </div>

        <div class="nav">
            <a class="nav-link active" onclick="showSection('dashboard')">📊 Tableau de Bord</a>
            <a class="nav-link" onclick="showSection('subscriptions')">📅 Abonnements</a>
            <a class="nav-link" onclick="showSection('contacts')">💬 Messages</a>
            <a class="nav-link" onclick="showSection('reservations')">🗓️ Réservations</a>
            <a class="back-button" href="?logout=1" style="margin-left: auto;">🚪 Déconnexion</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Abonnements</h3>
                <div class="number"><?php echo $total_subscriptions; ?></div>
                <div class="subtitle"><?php echo $active_subscriptions; ?> actifs</div>
            </div>
            <div class="stat-card">
                <h3>Réservations</h3>
                <div class="number"><?php echo $total_reservations; ?></div>
                <div class="subtitle"><?php echo $pending_reservations; ?> en attente</div>
            </div>
            <div class="stat-card">
                <h3>Messages</h3>
                <div class="number"><?php echo $total_contacts; ?></div>
                <div class="subtitle"><?php echo $new_contacts; ?> non lus</div>
            </div>
        </div>

        <div class="content">
            <!-- Section Tableau de Bord -->
            <div id="dashboard" class="section active">
                <h2>📊 Tableau de Bord</h2>
                <p style="color: #666; margin-top: 20px;">Bienvenue sur la page d'administration ICE DOG. Utilisez le menu de navigation ci-dessus pour accéder aux différentes sections.</p>
                <div style="margin-top: 30px; padding: 20px; background: #FFF5F5; border-radius: 8px; border-left: 5px solid #DC143C;">
                    <h3 style="color: #333; margin-bottom: 10px;">Résumé Rapide</h3>
                    <ul style="color: #666; line-height: 1.8;">
                        <li>✓ <strong><?php echo $total_subscriptions; ?></strong> abonnement(s) au total</li>
                        <li>✓ <strong><?php echo $active_subscriptions; ?></strong> abonnement(s) actif(s)</li>
                        <li>✓ <strong><?php echo $total_reservations; ?></strong> réservation(s) enregistrée(s)</li>
                        <li>✓ <strong><?php echo $pending_reservations; ?></strong> réservation(s) en attente de confirmation</li>
                        <li>✓ <strong><?php echo $total_contacts; ?></strong> message(s) de contact</li>
                        <li>✓ <strong><?php echo $new_contacts; ?></strong> message(s) non lu(s)</li>
                    </ul>
                </div>
            </div>

            <!-- Section Abonnements -->
            <div id="subscriptions" class="section">
                <h2>📅 Gestion des Abonnements</h2>
                <?php if (count($subscriptions) > 0): ?>
                    <div style="background: #FFF5F5; padding: 12px; border-radius: 5px; margin-bottom: 15px; color: #333;">
                        <strong>📊 Total:</strong> <?php echo count($subscriptions); ?> abonnement(s) | 
                        <strong>✅ Actifs:</strong> <?php echo count(array_filter($subscriptions, fn($s) => $s['status'] !== 'cancelled')); ?> | 
                        <strong>💰 Revenu Mensuel:</strong> <?php 
                            $monthly_revenue = 0;
                            foreach ($subscriptions as $s) {
                                if ($s['status'] !== 'cancelled') {
                                    $monthly_revenue += $s['price'];
                                }
                            }
                            echo number_format($monthly_revenue, 0, ',', ' '); 
                        ?> FCFA
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Chien</th>
                                <th>Race</th>
                                <th>Type</th>
                                <th>💰 Tarif</th>
                                <th>📅 Début</th>
                                <th>📅 Fin</th>
                                <th>Statut</th>
                                <th>⚙️ Actions</th>
                                <th>📝 Date Création</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscriptions as $sub): ?>
                                <tr>
                                    <td>#<?php echo $sub['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($sub['dog_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($sub['dog_breed'] ?? 'N/A'); ?></td>
                                    <td><?php echo ucfirst($sub['subscription_type']); ?></td>
                                    <td style="font-weight: 600; color: #DC143C;"><?php echo number_format($sub['price'], 0, ',', ' '); ?> FCFA</td>
                                    <td><?php echo date('d/m/Y', strtotime($sub['start_date'])); ?></td>
                                    <td><?php echo $sub['end_date'] ? date('d/m/Y', strtotime($sub['end_date'])) : 'N/A'; ?></td>
                                    <td><span class="status <?php echo $sub['status']; ?>"><?php echo ucfirst($sub['status']); ?></span></td>
                                    <td>
                                        <?php if ($sub['status'] === 'active'): ?>
                                            <button class="action-btn confirm-btn" onclick="updateSubscriptionStatus(<?php echo $sub['id']; ?>, 'active', <?php echo $sub['price']; ?>)">✅ Garder</button>
                                            <button class="action-btn reject-btn" onclick="updateSubscriptionStatus(<?php echo $sub['id']; ?>, 'cancelled', <?php echo $sub['price']; ?>)">❌ Annuler</button>
                                        <?php elseif ($sub['status'] === 'paused'): ?>
                                            <button class="action-btn confirm-btn" onclick="updateSubscriptionStatus(<?php echo $sub['id']; ?>, 'active', <?php echo $sub['price']; ?>)">▶️ Réactiver</button>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 12px;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($sub['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-message">📭 Aucun abonnement enregistré pour le moment</div>
                <?php endif; ?>
            </div>

            <!-- Section Messages -->
            <div id="contacts" class="section">
                <h2>💬 Messages de Contact</h2>
                <?php if (count($contacts) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Sujet</th>
                                <th>Message</th>
                                <th>Statut</th>
                                <th>Actions</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td>#<?php echo $contact['id']; ?></td>
                                    <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                    <td><a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>"><?php echo htmlspecialchars($contact['email']); ?></a></td>
                                    <td><?php echo htmlspecialchars($contact['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($contact['subject'] ?? 'N/A'); ?></td>
                                    <td><div class="message-preview" title="<?php echo htmlspecialchars($contact['message']); ?>"><?php echo htmlspecialchars(substr($contact['message'], 0, 50)); ?>...</div></td>
                                    <td><span class="status <?php echo $contact['status']; ?>"><?php echo ucfirst($contact['status']); ?></span></td>
                                    <td>
                                        <button class="action-btn" style="background: #2196F3; color: white; padding: 6px 10px; font-size: 12px;" onclick="openMessageModal(<?php echo htmlspecialchars(json_encode($contact)); ?>)">👁️ Voir</button>
                                        <?php if ($contact['status'] === 'new'): ?>
                                            <button class="action-btn confirm-btn" onclick="updateContactStatus(<?php echo $contact['id']; ?>, 'read')">✅ Lire</button>
                                        <?php else: ?>
                                            <button class="action-btn reject-btn" onclick="updateContactStatus(<?php echo $contact['id']; ?>, 'new')">↩️ Non lu</button>
                                        <?php endif; ?>
                                        <button class="action-btn reject-btn" onclick="updateContactStatus(<?php echo $contact['id']; ?>, 'archived')">📦 Archiver</button>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-message">📭 Aucun message de contact reçu</div>
                <?php endif; ?>
            </div>

            <!-- Modal pour afficher le message complet -->
            <div id="messageModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); overflow: auto;">
                <div style="background-color: white; margin: 5% auto; padding: 30px; border: 1px solid #888; width: 90%; max-width: 700px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); min-height: auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="margin: 0; color: #DC143C; font-size: 1.5rem;">📩 Détails du Message</h2>
                        <button onclick="closeMessageModal()" style="background: #ddd; border: none; font-size: 24px; cursor: pointer; border-radius: 4px; padding: 0 8px;">×</button>
                    </div>
                    <div style="max-height: calc(100vh - 200px); overflow-y: auto;">
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #333;">👤 Nom:</strong>
                            <p id="modal_name" style="margin: 5px 0; color: #666; word-break: break-word;"></p>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #333;">📧 Email:</strong>
                            <p id="modal_email" style="margin: 5px 0; color: #666; word-break: break-all;"></p>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #333;">📱 Téléphone:</strong>
                            <p id="modal_phone" style="margin: 5px 0; color: #666;"></p>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #333;">🎯 Sujet:</strong>
                            <p id="modal_subject" style="margin: 5px 0; color: #666; word-break: break-word;"></p>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #333;">💬 Message:</strong>
                            <div id="modal_message" style="margin: 5px 0; color: #666; background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #DC143C; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word;"></div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #333;">📅 Date:</strong>
                            <p id="modal_date" style="margin: 5px 0; color: #666;"></p>
                        </div>
                    </div>
                    <div style="text-align: right; margin-top: 20px;">
                        <button onclick="closeMessageModal()" style="background: #DC143C; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; font-size: 14px; min-width: 100px;">Fermer</button>
                    </div>
                </div>
            </div>

            <!-- Section Réservations -->
            <div id="reservations" class="section">
                <h2>🗓️ Gestion des Réservations</h2>
                
                <!-- Filtres de Date -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #DC143C;">
                    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                        <div>
                            <label for="start_date" style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">📅 Du:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        </div>
                        <div>
                            <label for="end_date" style="display: block; font-weight: 600; color: #333; margin-bottom: 5px;">📅 Au:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        </div>
                        <button type="submit" style="padding: 8px 20px; background: #DC143C; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;"> 🔍 Filtrer</button>
                        <a href="admin.php" style="padding: 8px 20px; background: #6c757d; color: white; border-radius: 5px; text-decoration: none; font-weight: 600;">↻ Réinitialiser</a>
                    </form>
                </div>

                <?php if (count($reservations) > 0): ?>
                    <div style="background: #FFF5F5; padding: 12px; border-radius: 5px; margin-bottom: 15px; color: #333;">
                        <strong>📊 Total:</strong> <?php echo count($reservations); ?> réservation(s) | 
                        <strong>💰 Revenu:</strong> <?php 
                            $total_revenue = 0;
                            $paid_revenue = 0;
                            foreach ($reservations as $r) {
                                $total_revenue += $r['price'];
                                if ($r['payment_status'] === 'paid') {
                                    $paid_revenue += $r['price'];
                                }
                            }
                            echo number_format($total_revenue, 0, ',', ' '); 
                        ?> FCFA | <strong>✅ Payé:</strong> <?php echo number_format($paid_revenue, 0, ',', ' '); ?> FCFA
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Chien</th>
                                <th>Race</th>
                                <th>Service</th>
                                <th>📅 Date</th>
                                <th>🕐 Heure</th>
                                <th>💰 Montant</th>
                                <th>💳 Statut Paiement</th>
                                <th>Statut</th>
                                <th>⚙️ Actions</th>
                                <th>📝 Créé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_date = '';
                            foreach ($reservations as $res): 
                                $res_date = date('d/m/Y', strtotime($res['appointment_date']));
                                if ($current_date !== $res_date) {
                                    if ($current_date !== '') {
                                        echo '<tr style="background: #f8f9fa;"><td colspan="11" style="padding: 5px; text-align: center; color: #999; font-weight: 600;"></td></tr>';
                                    }
                                    $current_date = $res_date;
                                    echo '<tr style="background: #FFF5F5;"><td colspan="11" style="padding: 10px; color: #DC143C; font-weight: 600;">📅 ' . $current_date . '</td></tr>';
                                }
                            ?>
                                <tr>
                                    <td>#<?php echo $res['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($res['dog_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($res['dog_breed'] ?? 'N/A'); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $res['service_type'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($res['appointment_date'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($res['appointment_time'])); ?></td>
                                    <td style="font-weight: 600; color: #DC143C;"><?php echo number_format($res['price'], 0, ',', ' '); ?> FCFA</td>
                                    <td>
                                        <select class="payment-status-select" data-reservation-id="<?php echo $res['id']; ?>" onchange="updatePaymentStatus(<?php echo $res['id']; ?>, this.value)">
                                            <option value="unpaid" <?php echo $res['payment_status'] === 'unpaid' ? 'selected' : ''; ?>>❌ Impayé</option>
                                            <option value="pending" <?php echo $res['payment_status'] === 'pending' ? 'selected' : ''; ?>>⏳ En Attente</option>
                                            <option value="paid" <?php echo $res['payment_status'] === 'paid' ? 'selected' : ''; ?>>✅ Payé</option>
                                        </select>
                                    </td>
                                    <td><span class="status <?php echo $res['status']; ?>"><?php echo ucfirst($res['status']); ?></span></td>
                                    <td>
                                        <?php if ($res['status'] === 'pending'): ?>
                                            <button class="action-btn confirm-btn" onclick="updateReservationStatus(<?php echo $res['id']; ?>, 'confirmed', <?php echo $res['price']; ?>)">✅ Confirmer</button>
                                            <button class="action-btn reject-btn" onclick="updateReservationStatus(<?php echo $res['id']; ?>, 'cancelled', <?php echo $res['price']; ?>)">❌ Rejeter</button>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 12px;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($res['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-message">📭 Aucune réservation enregistrée pour cette période</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Masquer toutes les sections
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));

            // Afficher la section sélectionnée
            document.getElementById(sectionId).classList.add('active');

            // Mettre à jour les liens de navigation
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => link.classList.remove('active'));
            event.target.classList.add('active');

            // Scroll vers le haut
            window.scrollTo(0, 0);
        }

        // Fonctions pour gérer la modal de message
        function openMessageModal(contact) {
            document.getElementById('modal_name').textContent = contact.name;
            document.getElementById('modal_email').innerHTML = '<a href="mailto:' + contact.email + '">' + contact.email + '</a>';
            document.getElementById('modal_phone').textContent = contact.phone || 'N/A';
            document.getElementById('modal_subject').textContent = contact.subject || 'N/A';
            document.getElementById('modal_message').textContent = contact.message;
            document.getElementById('modal_date').textContent = contact.created_at;
            document.getElementById('messageModal').style.display = 'block';
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Fermer la modal quand on clique en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Fonction pour mettre à jour le statut de paiement
        function updatePaymentStatus(reservationId, paymentStatus) {
            const select = document.querySelector(`[data-reservation-id="${reservationId}"]`);
            select.disabled = true;
            select.style.opacity = '0.5';

            const formData = new FormData();
            formData.append('action', 'update_payment_status');
            formData.append('reservation_id', reservationId);
            formData.append('payment_status', paymentStatus);

            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    select.style.opacity = '1';
                    select.disabled = false;
                    
                    // Afficher une notification
                    alert('✅ Statut de paiement mis à jour avec succès!');
                    
                    // Optionnel: changer la couleur du sélecteur selon le statut
                    if (paymentStatus === 'paid') {
                        select.style.background = '#d4edda';
                        select.style.color = '#155724';
                    } else if (paymentStatus === 'pending') {
                        select.style.background = '#fff3cd';
                        select.style.color = '#856404';
                    } else if (paymentStatus === 'unpaid') {
                        select.style.background = '#f8d7da';
                        select.style.color = '#842029';
                    }
                } else {
                    select.style.opacity = '1';
                    select.disabled = false;
                    alert('❌ Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                select.style.opacity = '1';
                select.disabled = false;
                alert('❌ Erreur lors de la mise à jour');
            });
        }

        // Fonction pour mettre à jour le statut de la réservation
        function updateReservationStatus(reservationId, status, price) {
            price = price || 0;
            let actionText = status === 'confirmed' ? 'confirmer' : 'rejeter';
            let statusText = status === 'confirmed' ? 'confirmée' : 'rejetée';
            
            let message = `Êtes-vous sûr de vouloir ${actionText} cette réservation ?\n\n💰 Montant exact: ${Number(price).toLocaleString('fr-FR')} FCFA`;
            
            if (confirm(message)) {
                const formData = new FormData();
                formData.append('action', 'update_reservation_status');
                formData.append('reservation_id', reservationId);
                formData.append('status', status);

                fetch('admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Response:', text);
                    const data = JSON.parse(text);
                    if (data.success) {
                        let resultMessage = status === 'cancelled' 
                            ? `✅ Réservation annulée\n💰 Montant annulé: ${Number(price).toLocaleString('fr-FR')} FCFA`
                            : '✅ ' + data.message;
                        alert(resultMessage);
                        location.reload();
                    } else {
                        alert('❌ ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('❌ Erreur: ' + error.message);
                });
            }
        }

        // Fonction pour mettre à jour le statut de l'abonnement
        function updateSubscriptionStatus(subscriptionId, status, price) {
            price = price || 0;
            let message = '';
            let resultAction = '';
            
            if (status === 'active') {
                message = 'garder cet abonnement actif';
                resultAction = 'conservé';
            } else if (status === 'paused') {
                message = 'suspendre cet abonnement';
                resultAction = 'suspendu';
            } else if (status === 'cancelled') {
                message = 'annuler cet abonnement';
                resultAction = 'annulé';
            }
            
            const fullMessage = `Êtes-vous sûr de vouloir ${message} ?\n\n💰 Montant exact: ${Number(price).toLocaleString('fr-FR')} FCFA`;
            
            if (confirm(fullMessage)) {
                const formData = new FormData();
                formData.append('action', 'update_subscription_status');
                formData.append('subscription_id', subscriptionId);
                formData.append('status', status);

                fetch('admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Response:', text);
                    const data = JSON.parse(text);
                    if (data.success) {
                        let resultMessage = `✅ Abonnement ${resultAction}\n💰 Montant: ${Number(price).toLocaleString('fr-FR')} FCFA`;
                        alert(resultMessage);
                        location.reload();
                    } else {
                        alert('❌ ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('❌ Erreur: ' + error.message);
                });
            }
        }

        function updateContactStatus(contactId, status) {
            let message = '';
            if (status === 'read') message = 'marquer ce message comme lu';
            else if (status === 'new') message = 'marquer ce message comme non lu';
            else if (status === 'archived') message = 'archiver ce message';
            
            if (confirm('Êtes-vous sûr de vouloir ' + message + ' ?')) {
                const formData = new FormData();
                formData.append('action', 'update_contact_status');
                formData.append('contact_id', contactId);
                formData.append('status', status);

                fetch('admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Response:', text);
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('❌ ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('❌ Erreur: ' + error.message);
                });
            }
        }
    </script>
    <?php endif; ?>
</body>
</html>
