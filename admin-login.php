<?php
/**
 * Page de Connexion Admin ICE DOG
 */

session_start();

// Le code admin à entrer
define('ADMIN_CODE', '2025'); // À modifier

// Vérifier si déjà connecté
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit();
}

$error = '';
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    
    if (empty($code)) {
        $error = 'Veuillez entrer le code d\'accès';
    } else if ($code === ADMIN_CODE) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        header('Location: admin.php');
        exit();
    } else {
        $error = 'Code d\'accès incorrect. Veuillez réessayer.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin ICE DOG - Connexion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, rgba(220, 20, 60, 0.85) 0%, rgba(139, 0, 0, 0.85) 100%), url(icc.png);
            background-size: cover, cover;
            background-position: center, center;
            background-attachment: fixed, fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px;
            width: 100%;
            max-width: 400px;
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

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: #DC143C;
            box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
        }

        .submit-btn {
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

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 20, 60, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .error-message {
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

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #DC143C;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: #8B0000;
        }

        .info-box {
            background: #FFF5F5;
            border-left: 4px solid #DC143C;
            padding: 15px;
            border-radius: 8px;
            margin-top: 30px;
            font-size: 0.85rem;
            color: #333;
        }

        .info-box strong {
            color: #DC143C;
        }

        @media (max-width: 500px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }

            .login-icon {
                font-size: 3rem;
            }
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
    <div class="login-container">
        <div class="login-header">
            <span class="login-icon">🔐</span>
            <h1>Admin ICE DOG</h1>
            <p>Accès Réservé</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="code">Code d'Accès</label>
                <input 
                    type="password" 
                    id="code" 
                    name="code" 
                    placeholder="Entrez le code d'accès" 
                    required 
                    autofocus
                    autocomplete="off"
                >
            </div>

            <button type="submit" class="submit-btn">🔓 Accéder au Tableau de Bord</button>
        </form>

        <div class="back-link">
            <a href="index.html">← Retour au Site</a>
        </div>

        <div class="info-box">
            <strong>💡 Note:</strong> Cette page est réservée aux administrateurs d'ICE DOG. Seuls les utilisateurs ayant le code d'accès peuvent consulter les données d'abonnements, réservations et messages.
        </div>
    </div>
</body>
</html>
