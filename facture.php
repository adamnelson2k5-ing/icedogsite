<?php
/**
 * Génération de Facture PDF - ICE DOG
 * Facture générée après confirmation de réservation
 */

// Récupérer les données POST (formulaire HTML)
$data = $_POST;

if (!isset($data['nom']) || !isset($data['service'])) {
    http_response_code(400);
    echo 'Erreur: Données incomplètes';
    exit;
}

// Données des tarifs
$tarifs = [
    'bain' => ['nom' => 'Bain & Séchage', 'petit' => 35000, 'moyen' => 45000, 'grand' => 55000],
    'coupe' => ['nom' => 'Coupe & Toilettage', 'petit' => 50000, 'moyen' => 65000, 'grand' => 80000],
    'forfait' => ['nom' => 'Forfait Complet (Rendez-vous Standard)', 'montant' => 20000],
    'abonnement' => ['nom' => 'Abonnement Mensuel', 'montant' => 15000, 'type' => 'abonnement']
];

$categories = [
    'petit' => 'Petit (0-15 kg)',
    'moyen' => 'Moyen (15-30 kg)',
    'grand' => 'Grand (+ 30 kg)'
];

// Déterminer la catégorie de poids
$poids = (float)($data['poids'] ?? 25);
$categorie = 'petit';
if ($poids > 15 && $poids <= 30) $categorie = 'moyen';
elseif ($poids > 30) $categorie = 'grand';

// Récupérer le service et le prix
$service = $data['service'] ?? 'forfait';
$serviceData = $tarifs[$service];

// Déterminer le prix en fonction du type de service
if (isset($serviceData['type']) && $serviceData['type'] === 'abonnement') {
    $prix = $serviceData['montant'];
    $isAbonnement = true;
} elseif (isset($serviceData['montant'])) {
    $prix = $serviceData['montant'];
    $isAbonnement = false;
} else {
    $prix = $serviceData[$categorie];
    $isAbonnement = false;
}

// Générer un numéro de facture unique
$numeroFacture = 'FAC-' . date('Y') . '-' . strtoupper(substr(md5($data['nom'] . time()), 0, 6));
$dateFacture = date('d/m/Y');

// Générer l'HTML de la facture
$html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture ICE DOG</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Arial", sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
            background: white;
            padding: 20px;
        }
        
        @media print {
            body {
                padding: 0;
                background: white;
            }
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border: 3px solid #DC143C;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        /* En-tête */
        .header {
            background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 32px;
            font-weight: bold;
        }
        
        .company-info {
            font-size: 12px;
            opacity: 0.95;
        }
        
        .invoice-title {
            text-align: right;
        }
        
        .invoice-title h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .invoice-details {
            font-size: 13px;
            text-align: right;
        }
        
        /* Contenu */
        .content {
            padding: 30px;
        }
        
        /* Sections */
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%);
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            margin-bottom: 12px;
            border-radius: 5px;
            font-size: 13px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px dotted #E8D4C4;
        }
        
        .info-label {
            font-weight: bold;
            color: #8B0000;
            width: 150px;
            min-width: 150px;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        /* Tableau des services */
        .service-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .service-table th {
            background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        
        .service-table td {
            padding: 12px;
            border-bottom: 1px solid #E8D4C4;
        }
        
        .service-table tr:hover {
            background: rgba(210, 105, 30, 0.05);
        }
        
        .service-name {
            font-weight: bold;
            color: #333;
        }
        
        .price {
            text-align: right;
            font-weight: bold;
            color: #FF3333;
            font-size: 16px;
        }
        
        /* Total */
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
            margin-bottom: 25px;
        }
        
        .total-box {
            background: linear-gradient(135deg, rgba(220, 20, 60, 0.15) 0%, rgba(255, 51, 51, 0.1) 100%);
            border: 2px solid #DC143C;
            border-radius: 8px;
            padding: 20px 30px;
            text-align: right;
            min-width: 300px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .total-row.final {
            border-top: 2px solid #DC143C;
            padding-top: 12px;
            font-size: 18px;
            font-weight: bold;
            color: #FF3333;
        }
        
        .total-label {
            font-weight: bold;
            color: #8B0000;
        }
        
        .total-value {
            text-align: right;
            color: #333;
        }
        
        /* Conditions */
        .conditions {
            background: #FFF5F5;
            border-left: 4px solid #DC143C;
            padding: 15px;
            margin: 20px 0;
            font-size: 12px;
            line-height: 1.8;
            border-radius: 3px;
        }
        
        .conditions-title {
            font-weight: bold;
            color: #DC143C;
            margin-bottom: 10px;
        }
        
        .condition-item {
            margin: 5px 0;
            color: #1a1a1a;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #1a1a1a 0%, #8B0000 100%);
            color: white;
            padding: 20px 30px;
            text-align: center;
            font-size: 11px;
        }
        
        .footer-contact {
            font-weight: bold;
            color: white;
            margin-bottom: 5px;
        }
        
        .footer-note {
            margin-top: 8px;
            font-size: 10px;
            color: #C4A080;
        }
        
        /* Boutons */
        .button-group {
            text-align: center;
            margin-top: 20px;
            display: none;
        }
        
        @media screen {
            .button-group {
                display: block;
                padding: 20px;
            }
        }
        
        .btn {
            background: #DC143C;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #8B0000;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-download {
            background: #FF3333;
        }
        
        .btn-download:hover {
            background: #CC0000;
        }
        
        /* Impression */
        @media print {
            .container {
                box-shadow: none;
                border: 1px solid #999;
            }
            .button-group {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <div>
                <div class="logo">🐕 ICE DOG</div>
                <div class="company-info">Salon de Toilettage Canin Professionnel<br>Libreville - Gabon</div>
            </div>
            <div class="invoice-title">
                <h1>FACTURE</h1>
                <div class="invoice-details">
                    <div><strong>N°:</strong> ' . htmlspecialchars($numeroFacture) . '</div>
                    <div><strong>Date:</strong> ' . htmlspecialchars($dateFacture) . '</div>
                </div>
            </div>
        </div>
        
        <!-- Contenu -->
        <div class="content">
            <!-- Client et Chien -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <div class="section">
                    <div class="section-title">CLIENT</div>
                    <div class="info-row">
                        <div class="info-label">Nom:</div>
                        <div class="info-value"><strong>' . htmlspecialchars($data['nom'] ?? '') . '</strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value">' . htmlspecialchars($data['email'] ?? 'N/A') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Téléphone:</div>
                        <div class="info-value">' . htmlspecialchars($data['telephone'] ?? 'N/A') . '</div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">CHIEN</div>
                    <div class="info-row">
                        <div class="info-label">Nom:</div>
                        <div class="info-value"><strong>' . htmlspecialchars($data['nomChien'] ?? '') . '</strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Race:</div>
                        <div class="info-value">' . htmlspecialchars($data['race'] ?? '') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Âge / Poids:</div>
                        <div class="info-value">' . htmlspecialchars($data['age'] ?? '?') . ' ans / ' . htmlspecialchars($data['poids'] ?? '?') . ' kg</div>
                    </div>
                </div>
            </div>
            
            <!-- Service -->
            <div class="section">
                <div class="section-title">SERVICE COMMANDÉ</div>
                <table class="service-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="service-name">' . htmlspecialchars($serviceData['nom']) . '</td>
                            <td class="price">' . number_format($prix, 0, ',', ' ') . ' CFA</td>
                        </tr>
                    </tbody>
                </table>
                ' . ($isAbonnement ? '<p style="font-size: 0.9rem; color: #555; margin-top: 10px;"><strong>Note:</strong> Abonnement mensuel - Je passe une fois par mois pour le toilettage complet.</p>' : '') . '
            </div>
            
            <!-- Rendez-vous -->
            ' . (isset($data['date']) ? '
            <div class="section">
                <div class="section-title">RENDEZ-VOUS CONFIRMÉ</div>
                <div class="info-row">
                    <div class="info-label">Date:</div>
                    <div class="info-value"><strong>' . date('d/m/Y', strtotime($data['date'])) . '</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Heure:</div>
                    <div class="info-value"><strong>' . htmlspecialchars($data['heure'] ?? 'À convenir') . '</strong></div>
                </div>
            </div>
            ' : '') . '
            
            <!-- Total -->
            <div class="total-section">
                <div class="total-box">
                    <div class="total-row final">
                        <span>MONTANT À PAYER:</span>
                        <span>' . number_format($prix, 0, ',', ' ') . ' CFA</span>
                    </div>
                </div>
            </div>
            
            <!-- Conditions -->
            <div class="conditions">
                <div class="conditions-title">📋 MODES DE PAIEMENT</div>
                <div class="condition-item">💳 <strong>Airtel Money:</strong> 074847972</div>
                <div class="condition-item">💳 <strong>Moov Money:</strong> 065 77 80 10</div>
                <div class="condition-item">💵 <strong>Espèces:</strong> À payer après le service</div>
                <div class="condition-item" style="margin-top: 15px; background: #FFF0F5; padding: 10px; border-radius: 5px;">
                    <strong style="color: #DC143C;">⚠️ Important:</strong> Après votre paiement, veuillez envoyer la preuve de transaction (capture d\'écran) via WhatsApp au <strong>065 77 80 10</strong> avec votre nom complet.
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-contact">🐕 ICE DOG - Salon de Toilettage Canin Professionnel</div>
            <div>📍 Libreville, Charbonnage, Gabon | 📞 065 77 80 10 | ✉️ icedog241@gmail.com</div>
            <div class="footer-note">
                Facture N° ' . htmlspecialchars($numeroFacture) . ' - Générée le ' . date('d/m/Y à H:i:s') . '<br>
                Merci pour votre confiance! Nous avons hâte de vous rencontrer.
            </div>
        </div>
    </div>
    
    <div class="button-group">
        <button class="btn btn-download" onclick="imprimerFacture()">🖨️ Imprimer</button>
        <button class="btn" onclick="telechargerFacturePDF()">📥 Télécharger PDF</button>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"><\/script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"><\/script>
    
    <script>
        function imprimerFacture() {
            window.print();
        }

        function telechargerFacturePDF() {
            const btn = event && event.target ? event.target : document.querySelector(".btn-download");
            const btnText = btn ? btn.textContent : "Télécharger PDF";
            if (btn) {
                btn.textContent = "⏳ Génération...";
                btn.disabled = true;
            }
            
            const container = document.querySelector(".container");
            const numeroFacture = "FAC-" + new Date().getTime();
            
            if (!container) {
                alert("Erreur: Contenu de la facture non trouvé");
                return;
            }
            
            if (typeof window.html2canvas === "undefined") {
                alert("Bibliotheque de generation PDF non chargee. Utilisez Imprimer.");
                window.print();
                return;
            }
            
            html2canvas(container, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: "#ffffff",
                logging: false
            }).then(function(canvas) {
                try {
                    const imgData = canvas.toDataURL("image/png");
                    const imgWidth = 210;
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;
                    
                    if (typeof window.jspdf === "undefined") {
                        alert("jsPDF non charge");
                        return;
                    }
                    
                    const jsPDFModule = window.jspdf;
                    const jsPDFClass = jsPDFModule.jsPDF;
                    const pdf = new jsPDFClass("p", "mm", "a4");
                    
                    let heightLeft = imgHeight;
                    let position = 0;
                    
                    pdf.addImage(imgData, "PNG", 0, position, imgWidth, imgHeight);
                    heightLeft -= 297;
                    
                    while (heightLeft > 0) {
                        position = heightLeft - imgHeight;
                        pdf.addPage();
                        pdf.addImage(imgData, "PNG", 0, position, imgWidth, imgHeight);
                        heightLeft -= 297;
                    }
                    
                    pdf.save(numeroFacture + "_icedog.pdf");
                    
                    if (btn) {
                        btn.textContent = btnText;
                        btn.disabled = false;
                    }
                } catch (innerErr) {
                    console.error("Erreur PDF:", innerErr);
                    if (btn) {
                        btn.textContent = btnText;
                        btn.disabled = false;
                    }
                    alert("Erreur lors de la creation du PDF: " + innerErr.message);
                }
            }).catch(function(err) {
                console.error("Erreur canvas:", err);
                if (btn) {
                    btn.textContent = btnText;
                    btn.disabled = false;
                }
                alert("Erreur lors de la generation du PDF. Utilisez Imprimer et enregistrez en PDF.");
            });
        }
        
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                telechargerFacturePDF();
            }, 1000);
        });
    <\/script>
</body>
</html>';

header('Content-Type: text/html; charset=utf-8');
echo $html;
?>
