/**
 * Configuration du site ICE DOG
 * Fichier centralisé pour gérer les paramètres du site
 */

const CONFIG = {
    // Informations de l'entreprise
    entreprise: {
        nom: "ICE DOG",
        sigle: "ICE DOG",
        description: "Salon de toilettage canin professionnel et bienveillant",
        adresse: "Libreville charbonnage",
        codePostal: "00241",
        ville: "Libreville",
        pays: "Gabon",
        telephone: "065 77 80 10",
        email: "icedog241@gmail.com",
        siteWeb: "https://www.icedog.fr",
        siret: "123 456 789 00000",
        tva: "FR 12 345678901",
        responsable: "Marie Dupont",
        poste: "Directrice"
    },

    // Horaires d'ouverture
    horaires: {
        lundi: { ouverture: "09:00", fermeture: "19:00", ouvert: true },
        mardi: { ouverture: "09:00", fermeture: "19:00", ouvert: true },
        mercredi: { ouverture: "09:00", fermeture: "19:00", ouvert: true },
        jeudi: { ouverture: "09:00", fermeture: "19:00", ouvert: true },
        vendredi: { ouverture: "09:00", fermeture: "19:00", ouvert: true },
        samedi: { ouverture: "09:00", fermeture: "18:00", ouvert: true },
        dimanche: { ouverture: null, fermeture: null, ouvert: false }
    },

    // Services
    services: {
        bain: {
            nom: "Bain & Séchage",
            icon: "🛁",
            description: "Bain professionnel avec produits hypoallergéniques",
            prix: {
                petit: 35000,    // < 5kg
                moyen: 45000,    // 5-25kg
                grand: 55000     // > 25kg
            },
            duree: 45 // minutes
        },
        coupe: {
            nom: "Coupe & Toilettage",
            icon: "✂️",
            description: "Coupes styles personnalisées adaptées à la race",
            prix: {
                petit: 50000,
                moyen: 65000,
                grand: 80000
            },
            duree: 60
        },
        dentaire: {
            nom: "Hygiène Dentaire",
            icon: "🦷",
            description: "Nettoyage professionnel des dents",
            prix: {
                petit: 25000,
                moyen: 30000,
                grand: 35000
            },
            duree: 30
        },
        pattes: {
            nom: "Soins des Pattes",
            icon: "💅",
            description: "Taille des griffes et dégagement des coussinets",
            prix: {
                petit: 20000,
                moyen: 25000,
                grand: 30000
            },
            duree: 20
        },
        forfait: {
            nom: "Forfait Complet",
            icon: "🎀",
            description: "Package premium incluant tous nos services",
            prix: {
                petit: 140000,
                moyen: 180000,
                grand: 220000
            },
            duree: 180
        }
    },

    // Promotions
    promotions: [
        {
            nom: "Premier rendez-vous",
            reduction: 15,
            description: "-15% sur tous les services"
        },
        {
            nom: "Carnet de 5 visites",
            reduction: 10,
            description: "-10% par visite"
        },
        {
            nom: "Parrainage",
            reduction: 20,
            description: "20€ crédités pour vous et votre ami"
        }
    ],

    // Conditions d'annulation
    annulation: {
        plus48h: {
            pourcentage: 0,
            description: "Remboursement complet ou décalage gratuit"
        },
        moins48h: {
            pourcentage: 30,
            description: "Frais de 30% du montant"
        },
        absence: {
            pourcentage: 100,
            description: "Frais de 100% du montant"
        }
    },

    // Heures disponibles pour les réservations
    heuresDisponibles: [
        "09:00", "09:30", "10:00", "10:30", "11:00",
        "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00"
    ],

    // Catégories de poids
    categories: {
        petit: { min: 0, max: 5, label: "Petit Chien (< 5kg)" },
        moyen: { min: 5, max: 25, label: "Chien Moyen (5-25kg)" },
        grand: { min: 25, max: 100, label: "Grand Chien (> 25kg)" }
    },

    // Délai de réservation minimum (en jours)
    delaiReservationMin: 1,

    // Délai de réservation maximum (en jours)
    delaiReservationMax: 90,

    // Réseaux sociaux
    reseauxSociaux: {
        facebook: "https://www.facebook.com/icedog",
        instagram: "https://www.instagram.com/icedog",
        whatsapp: "https://wa.me/33123456789",
        youtube: "https://www.youtube.com/icedog"
    },

    // Adresses email
    emails: {
        contact: "info@icedog.fr",
        reservations: "reservations@icedog.fr",
        support: "support@icedog.fr",
        reclamations: "reclamations@icedog.fr"
    },

    // Couleurs (correspond à la palette CSS)
    couleurs: {
        primary: "#4A90E2",
        primaryDark: "#2E5DA6",
        secondary: "#F39C12",
        accent: "#E74C3C",
        success: "#27AE60",
        bgLight: "#F8F9FA",
        bgWhite: "#FFFFFF",
        textDark: "#2C3E50",
        textLight: "#7F8C8D"
    },

    // Google Maps
    googleMaps: {
        apiKey: "YOUR_GOOGLE_MAPS_API_KEY",
        latitude: 48.8704,
        longitude: 2.2956,
        zoom: 15
    },

    // Google Analytics
    analytics: {
        trackingId: "UA-XXXXXXXXX-X"
    },

    // Paramètres de formulaire
    formulaires: {
        validation: {
            nomMin: 2,
            nomMax: 50,
            telephoneMin: 10,
            telephoneMax: 20,
            messageMin: 10,
            messageMax: 5000
        },
        timeout: 5000 // ms
    },

    // Délai avant envoi du message au serveur
    delaiEnvoi: 300, // ms

    // Version du site
    version: "1.0",

    // Informations SEO
    seo: {
        title: "ICE DOG - Toilettage Canin Professionnel",
        description: "Salon de toilettage canin professionnel à Paris. Services de bain, coupe et spa. Réservez en ligne.",
        keywords: "toilettage chien, salon toilettage, grooming, réservation toilettage",
        author: "ICE DOG",
        language: "fr"
    }
};

/**
 * Fonction pour obtenir le prix en fonction du poids
 * @param {string} serviceKey - Clé du service
 * @param {number} poids - Poids du chien en kg
 * @returns {number} Prix du service
 */
function getPrix(serviceKey, poids) {
    const service = CONFIG.services[serviceKey];
    if (!service) return 0;

    let categorie = 'moyen';
    if (poids < 5) categorie = 'petit';
    else if (poids > 25) categorie = 'grand';

    return service.prix[categorie] || 0;
}

/**
 * Fonction pour obtenir la catégorie de poids
 * @param {number} poids - Poids en kg
 * @returns {string} Catégorie (petit, moyen, grand)
 */
function getCategoriePoids(poids) {
    if (poids < 5) return 'petit';
    if (poids > 25) return 'grand';
    return 'moyen';
}

/**
 * Fonction pour vérifier si le salon est ouvert maintenant
 * @returns {boolean} Ouverture actuelle
 */
function estOuvertMaintenant() {
    const maintenant = new Date();
    const jour = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'][maintenant.getDay()];
    const horaire = CONFIG.horaires[jour];

    if (!horaire.ouvert) return false;

    const heure = String(maintenant.getHours()).padStart(2, '0');
    const minute = String(maintenant.getMinutes()).padStart(2, '0');
    const maintenant_str = `${heure}:${minute}`;

    return maintenant_str >= horaire.ouverture && maintenant_str <= horaire.fermeture;
}

/**
 * Fonction pour formater une adresse
 * @returns {string} Adresse formatée
 */
function getAdresseFormatee() {
    return `${CONFIG.entreprise.adresse}, ${CONFIG.entreprise.codePostal} ${CONFIG.entreprise.ville}, ${CONFIG.entreprise.pays}`;
}

/**
 * Fonction pour formater les horaires
 * @returns {string} Horaires formatés
 */
function getHorairesFormates() {
    let horairesStr = "";
    for (const [jour, infos] of Object.entries(CONFIG.horaires)) {
        if (infos.ouvert) {
            horairesStr += `${jour}: ${infos.ouverture} - ${infos.fermeture}\n`;
        } else {
            horairesStr += `${jour}: Fermé\n`;
        }
    }
    return horairesStr;
}

/**
 * Fonction pour calculer la réduction
 * @param {number} prix - Prix original
 * @param {number} pourcentage - Pourcentage de réduction
 * @returns {number} Prix réduit
 */
function calculerReduction(prix, pourcentage) {
    return prix - (prix * pourcentage / 100);
}

// Export pour utilisation
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
}
