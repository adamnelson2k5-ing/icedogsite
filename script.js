// ========== MENU MOBILE TOGGLE ==========
const menuToggle = document.getElementById('menuToggle');
const navLinks = document.getElementById('navLinks');

menuToggle.addEventListener('click', function() {
    this.classList.toggle('active');
    navLinks.classList.toggle('active');
});

// Fermer le menu quand on clique sur un lien
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
        menuToggle.classList.remove('active');
        navLinks.classList.remove('active');
    });
});

// Fermer le menu quand on clique ailleurs
document.addEventListener('click', function(event) {
    const isClickInsideMenu = navLinks.contains(event.target);
    const isClickOnMenuToggle = menuToggle.contains(event.target);
    
    if (!isClickInsideMenu && !isClickOnMenuToggle && navLinks.classList.contains('active')) {
        menuToggle.classList.remove('active');
        navLinks.classList.remove('active');
    }
});

// ========== RESERVATION FORM ==========
const reservationForm = document.getElementById('reservationForm');
const formMessage = document.getElementById('formMessage');

if (reservationForm) {
    reservationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Récupérer les données du formulaire
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validation basique
        if (!data.nom || !data.email || !data.telephone || !data.nomChien) {
            showMessage(formMessage, 'Veuillez remplir tous les champs obligatoires.', 'error');
            return;
        }

        // Validation du checkbox conditions
        const conditionsCheckbox = document.getElementById('conditions');
        if (!conditionsCheckbox || !conditionsCheckbox.checked) {
            showMessage(formMessage, '⚠️ Veuillez accepter les conditions d\'utilisation et la politique de confidentialité.', 'error');
            conditionsCheckbox?.focus();
            return;
        }
        
        // Validation email
        if (!isValidEmail(data.email)) {
            showMessage(formMessage, 'Veuillez entrer une adresse email valide.', 'error');
            return;
        }
        
        // Validation date (seulement pour les réservations)
        if (data.service === 'forfait') {
            const selectedDate = new Date(data.date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                showMessage(formMessage, 'Veuillez sélectionner une date future.', 'error');
                return;
            }
        }
        
        // Déterminer l'action (réservation ou abonnement)
        let action = (data.service === 'abonnement') ? 'create_subscription' : 'create_reservation';
        
        // Ajouter le montant en fonction du service
        let price = (data.service === 'abonnement') ? 15000 : 20000;
        
        let successMessage = (data.service === 'abonnement') 
            ? `✅ Merci ${data.nom}! Votre demande d'abonnement pour ${data.nomChien} a été reçue. Redirection vers le paiement...`
            : `✅ Merci ${data.nom}! Votre réservation pour ${data.nomChien} est confirmée. Redirection vers le paiement...`;
        
        // Afficher le message de traitement
        showMessage(formMessage, (action === 'create_subscription') ? '⏳ Traitement de votre abonnement...' : '⏳ Traitement de votre réservation...', 'success');
        
        // Envoyer les données à l'API
        const formDataToSend = new FormData();
        formDataToSend.append('action', action);
        formDataToSend.append('price', price);
        Object.keys(data).forEach(key => {
            formDataToSend.append(key, data[key]);
        });
        
        fetch('api.php', {
            method: 'POST',
            body: formDataToSend
        })
        .then(response => {
            // Vérifier si la réponse est ok
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                showMessage(formMessage, successMessage, 'success');
                
                // Stocker les données dans sessionStorage pour la page de confirmation
                sessionStorage.setItem('reservationData', JSON.stringify(data));
                
                // Rediriger vers la page de confirmation après 2 secondes (pour réservations et abonnements)
                setTimeout(() => {
                    window.location.href = 'confirmation.html';
                }, 2000);
            } else {
                showMessage(formMessage, '❌ Erreur: ' + result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showMessage(formMessage, '❌ Erreur de connexion au serveur: ' + error.message, 'error');
        });
    });
}

// ========== CONTACT FORM ==========
const contactForm = document.getElementById('contactForm');
const contactFormMessage = document.getElementById('contactFormMessage');

if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Récupérer les données
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validation
        if (!data.nom || !data.email || !data.sujet || !data.message) {
            showMessage(contactFormMessage, 'Veuillez remplir tous les champs.', 'error');
            return;
        }
        
        if (!isValidEmail(data.email)) {
            showMessage(contactFormMessage, 'Veuillez entrer une adresse email valide.', 'error');
            return;
        }
        
        // Afficher le message de traitement
        showMessage(contactFormMessage, '⏳ Envoi du message...', 'success');
        
        // Envoyer les données à l'API
        const formDataToSend = new FormData();
        formDataToSend.append('action', 'create_contact');
        Object.keys(data).forEach(key => {
            formDataToSend.append(key, data[key]);
        });
        
        fetch('api.php', {
            method: 'POST',
            body: formDataToSend
        })
        .then(response => {
            // Vérifier si la réponse est ok
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                showMessage(contactFormMessage, '✅ Votre message a été envoyé avec succès! Nous vous répondrons dans les 24 heures.', 'success');
                
                // Réinitialiser
                contactForm.reset();
                
                // Masquer après 8 secondes
                setTimeout(() => {
                    contactFormMessage.classList.remove('success', 'error');
                }, 8000);
            } else {
                showMessage(contactFormMessage, '❌ Erreur: ' + result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showMessage(contactFormMessage, '❌ Erreur de connexion au serveur: ' + error.message, 'error');
        });
    });
}

// ========== UTILITAIRES ==========
function showMessage(element, message, type) {
    element.textContent = message;
    element.className = 'form-message ' + type;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString + 'T00:00:00').toLocaleDateString('fr-FR', options);
}

// ========== DATE PICKER - MIN DATE ==========
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    if (dateInput) {
        // Définir la date minimale à aujourd'hui
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        dateInput.min = `${yyyy}-${mm}-${dd}`;
    }
});

// ========== ANIMATION DÉFILEMENT ==========
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeInUp 0.8s ease forwards';
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observer tous les service cards et gallery items au démarrage
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.service-card, .gallery-item, .info-card').forEach(element => {
        element.style.opacity = '0';
        observer.observe(element);
    });
});

// ========== SMOOTH SCROLL POUR LES ANCRES ==========
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ========== CALCUL DYNAMIQUE DES TARIFS ==========
const poidInput = document.getElementById('poids');
const serviceSelect = document.getElementById('service');

function updateTarif() {
    const poids = parseFloat(poidInput.value) || 0;
    let categorie = 'moyen';
    
    if (poids < 5) {
        categorie = 'petit';
    } else if (poids > 25) {
        categorie = 'grand';
    }
    
    const tarifs = {
        'bain': { petit: 35000, moyen: 45000, grand: 55000 },
        'coupe': { petit: 50000, moyen: 65000, grand: 80000 },
        'dentaire': { petit: 25000, moyen: 30000, grand: 35000 },
        'pattes': { petit: 20000, moyen: 25000, grand: 30000 },
        'forfait': { petit: 140000, moyen: 180000, grand: 220000 }
    };
    
    const service = serviceSelect.value;
    if (service && tarifs[service]) {
        console.log(`Prix pour ${service} (${categorie}): ${tarifs[service][categorie]}€`);
    }
}

if (poidInput) {
    poidInput.addEventListener('change', updateTarif);
}

if (serviceSelect) {
    serviceSelect.addEventListener('change', updateTarif);
}

// ========== SCROLL TO TOP BUTTON ==========
const scrollTopButton = document.createElement('button');
scrollTopButton.innerHTML = '↑';
scrollTopButton.className = 'scroll-to-top';
scrollTopButton.style.cssText = `
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #4A90E2, #2E5DA6);
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    display: none;
    z-index: 999;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    font-weight: bold;
`;

document.body.appendChild(scrollTopButton);

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        scrollTopButton.style.display = 'block';
    } else {
        scrollTopButton.style.display = 'none';
    }
});

scrollTopButton.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

scrollTopButton.addEventListener('mouseover', function() {
    this.style.transform = 'scale(1.1)';
    this.style.background = 'linear-gradient(135deg, #2E5DA6, #4A90E2)';
});

scrollTopButton.addEventListener('mouseout', function() {
    this.style.transform = 'scale(1)';
    this.style.background = 'linear-gradient(135deg, #4A90E2, #2E5DA6)';
});

// ========== HOVER EFFECTS ==========
document.querySelectorAll('.service-card, .info-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transition = 'all 0.3s ease';
    });
});

// ========== VALIDATION EN TEMPS RÉEL ==========
const emailInputs = document.querySelectorAll('input[type="email"]');
emailInputs.forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value && !isValidEmail(this.value)) {
            this.style.borderColor = '#E74C3C';
            this.title = 'Email invalide';
        } else {
            this.style.borderColor = '';
            this.title = '';
        }
    });
});

const telInputs = document.querySelectorAll('input[type="tel"]');
telInputs.forEach(input => {
    input.addEventListener('blur', function() {
        const tel = this.value.replace(/\D/g, '');
        if (this.value && tel.length < 10) {
            this.style.borderColor = '#E74C3C';
            this.title = 'Numéro de téléphone invalide';
        } else {
            this.style.borderColor = '';
            this.title = '';
        }
    });
});

// ========== AFFICHAGE DES HEURES DISPONIBLES ==========
document.getElementById('date')?.addEventListener('change', function() {
    const selectedDate = new Date(this.value + 'T00:00:00');
    const dayOfWeek = selectedDate.getDay();
    const heure = document.getElementById('heure');
    
    // Dimanche = 0
    if (dayOfWeek === 0) {
        heure.disabled = true;
        heure.innerHTML = '<option value="">Fermé le dimanche</option>';
    } else {
        heure.disabled = false;
        // Réinitialiser les options
        const baseOptions = [
            { value: '', text: 'Sélectionnez une heure' },
            { value: '09:00', text: '09:00' },
            { value: '09:30', text: '09:30' },
            { value: '10:00', text: '10:00' },
            { value: '10:30', text: '10:30' },
            { value: '11:00', text: '11:00' },
            { value: '14:00', text: '14:00' },
            { value: '14:30', text: '14:30' },
            { value: '15:00', text: '15:00' },
            { value: '15:30', text: '15:30' },
            { value: '16:00', text: '16:00' },
            { value: '16:30', text: '16:30' },
            { value: '17:00', text: '17:00' }
        ];
        
        heure.innerHTML = '';
        baseOptions.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option.value;
            opt.text = option.text;
            heure.appendChild(opt);
        });
    }
});

// ========== ANALYTICS BASIQUE ==========
window.addEventListener('load', function() {
    console.log('Site ICE DOG chargé avec succès');
    console.log('Heure de chargement:', new Date().toLocaleTimeString('fr-FR'));
});

// Log les interactions utilisateur
document.addEventListener('click', function(e) {
    if (e.target.tagName === 'A' || e.target.classList.contains('btn')) {
        console.log('Interaction utilisateur:', e.target.textContent);
    }
});

// ========== GESTION DE L'ÉTAT DU FORMULAIRE ==========
const inputs = document.querySelectorAll('input, textarea, select');
let hasUnsavedChanges = false;

inputs.forEach(input => {
    input.addEventListener('change', function() {
        hasUnsavedChanges = true;
    });
});

window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges && 
        (document.getElementById('reservationForm').style.display !== 'none' || 
         document.getElementById('contactForm').style.display !== 'none')) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// ========== FOCUS MANAGEMENT ==========
document.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        document.body.classList.add('keyboard-focused');
    }
});

document.addEventListener('mousedown', function() {
    document.body.classList.remove('keyboard-focused');
});
