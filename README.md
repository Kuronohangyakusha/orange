# 🏦 Orange Banking API

Une API REST complète pour un système bancaire moderne développée avec Laravel 10. Cette application permet la gestion des clients, comptes bancaires, transactions et paiements sécurisés.

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation de l'API](#utilisation-de-lapi)
- [Documentation API](#documentation-api)
- [Tests](#tests)
- [Architecture](#architecture)
- [Sécurité](#sécurité)

## ✨ Fonctionnalités

### 👥 Gestion des utilisateurs
- Inscription et connexion des clients
- Gestion des rôles (Client, Marchand)
- Authentification via téléphone et mot de passe
- Vérification OTP par email

### 💳 Gestion des comptes
- Création automatique de comptes courants
- Consultation des soldes en temps réel
- Historique des transactions
- Génération de codes de paiement uniques
- Support des comptes marchands avec codes QR

### 💰 Transactions financières
- **Paiements par code** : Transferts vers des marchands via codes de paiement
- **Transferts par téléphone** : Envois d'argent entre comptes clients
- **Dépôts et retraits** : Gestion des mouvements de fonds
- **Historique complet** : Traçabilité de toutes les opérations

### 🛡️ Sécurité
- Authentification Sanctum (Bearer Token)
- Chiffrement des mots de passe (Bcrypt)
- Validation des données d'entrée
- Protection contre les attaques CSRF
- Limitation du taux de requêtes (Throttle)

### 📧 Notifications
- Emails de bienvenue avec informations de compte
- Notifications de transactions
- Codes QR pour accès rapide aux paiements

## 🛠️ Technologies utilisées

- **Framework** : Laravel 10.x
- **Base de données** : PostgreSQL
- **Authentification** : Laravel Sanctum
- **Documentation** : Swagger/OpenAPI (L5-Swagger)
- **Génération QR Code** : Bacon QR Code
- **Tests** : PHPUnit
- **Cache** : Système de cache Laravel
- **Queues** : Traitement asynchrone des emails

## 🚀 Installation

### Prérequis
- PHP 8.1 ou supérieur
- Composer
- PostgreSQL
- Node.js & NPM (pour les assets frontend)

### Étapes d'installation

1. **Cloner le repository**
   ```bash
   git clone <repository-url>
   cd orange-banking-api
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configuration de l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configuration de la base de données**
   Éditer le fichier `.env` :
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=orange_banking
   DB_USERNAME=votre_username
   DB_PASSWORD=votre_password
   ```

5. **Migration et seeding**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Génération de la documentation Swagger**
   ```bash
   php artisan l5-swagger:generate
   ```

7. **Démarrage du serveur**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8001
   ```

## ⚙️ Configuration

### Variables d'environnement importantes

```env
# Application
APP_NAME="Orange Banking API"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://127.0.0.1:8001

# Base de données
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=orange_banking
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Mail (pour les notifications)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Swagger
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_UI_DOC_EXPANSION=none
```

## 📖 Utilisation de l'API

### Authentification

#### 1. Inscription d'un client
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "nom": "Dupont",
  "prenom": "Jean",
  "email": "jean.dupont@example.com",
  "telephone": "0123456789",
  "password": "password123"
}
```

#### 2. Vérification OTP
```http
POST /api/v1/auth/verify
Content-Type: application/json

{
  "telephone": "0123456789",
  "otp": "123456"
}
```

#### 3. Connexion
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "telephone": "0123456789",
  "password": "password123"
}
```

### Opérations bancaires

#### Récupérer ses comptes
```http
GET /api/v1/comptes
Authorization: Bearer {token}
```

#### Consulter le solde
```http
GET /api/v1/comptes/{id}/solde
Authorization: Bearer {token}
```

#### Effectuer un paiement par code
```http
POST /api/v1/comptes/{id}/paiement-code
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "PAY123456",
  "montant": 50.00
}
```

#### Transférer par téléphone
```http
POST /api/v1/comptes/{id}/transfert-tel
Authorization: Bearer {token}
Content-Type: application/json

{
  "numero_tel": "0987654321",
  "montant": 25.00
}
```

## 📚 Documentation API

La documentation complète de l'API est disponible via Swagger UI :

**URL** : `http://127.0.0.1:8001/api/documentation`

### Points d'accès principaux

- **Authentification** : `/api/v1/auth/*`
- **Comptes** : `/api/v1/comptes/*`
- **Transactions** : `/api/v1/transactions/*`

## 🧪 Tests

### Exécution des tests
```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter AccessControlTest

# Tests avec couverture
php artisan test --coverage
```

### Tests disponibles
- **Tests d'accès** : Vérification des autorisations
- **Tests d'authentification** : Inscription, connexion, OTP
- **Tests de transactions** : Paiements et transferts
- **Tests de notifications** : Envois d'emails

## 🏗️ Architecture

### Structure du projet

```
app/
├── Console/           # Commandes Artisan
├── Exceptions/        # Gestion des erreurs
├── Http/
│   ├── Controllers/   # Contrôleurs API
│   ├── Middleware/    # Middlewares personnalisés
│   └── Requests/      # Validation des requêtes
├── Jobs/              # Tâches asynchrones (emails)
├── Mail/              # Templates d'emails
├── Models/            # Modèles Eloquent
├── Observers/         # Observers de modèles
├── Policies/          # Politiques d'autorisation
├── Repositories/      # Couche d'accès aux données
├── Services/          # Logique métier
├── Traits/            # Traits réutilisables
└── Utils/             # Utilitaires
```

### Pattern architectural

- **Repository Pattern** : Séparation de la logique d'accès aux données
- **Service Layer** : Encapsulation de la logique métier
- **Observer Pattern** : Réactions automatiques aux changements de modèles
- **Trait Pattern** : Réutilisation de code dans les contrôleurs

## 🔒 Sécurité

### Mesures de sécurité implémentées

- **Authentification robuste** : Laravel Sanctum avec tokens Bearer
- **Validation stricte** : Validation de toutes les entrées utilisateur
- **Protection CSRF** : Protection automatique des formulaires
- **Rate Limiting** : Limitation des tentatives de connexion et paiements
- **Chiffrement** : Mots de passe hashés avec Bcrypt
- **Sanitisation** : Nettoyage automatique des entrées

### Bonnes pratiques

- Utilisation de prepared statements
- Échappement automatique des requêtes SQL
- Validation côté serveur
- Gestion sécurisée des sessions
- Audit trail des transactions

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📝 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 📞 Support

Pour toute question ou problème :
- Ouvrir une issue sur GitHub
- Contacter l'équipe de développement

---

**Développé avec ❤️ par l'équipe Orange Banking**
