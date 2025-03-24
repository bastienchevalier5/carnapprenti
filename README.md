# Présentation du Projet

Ce projet est une appplication web développée avec Laravel et qui a pour but la gestion des livrets d'apprentissage pour les centres de formation de la CCI de la Mayenne.

## Fonctionnalités

- Connexion pour les apprenants, tuteurs et référents
- Profil et modification des informations (nom, prénom, mail et mot de passe pour les référents et les tuteurs et mot de passe pour les apprenants)
- Ajout, modification et suppression des livrets par les référents par rapport à un apprenant et un modèle de livret qui dépend du groupe de l'apprenant et du site de formation
- Ajout et modification d'un compte-rendu tous les mois par l'apprenant et le tuteur (le référent pourra vérifier et modifier si besoin)
- Téléchargement en PDF du livret

# Installation

## Prérequis

- PHP 8.0 ou supérieur
- Composer
- MySQL ou une autre base de données compatible avec Laravel
- Node.js et npm (pour les assets front-end)


## 1. Cloner le Repository

```bash
cd chemin/vers/votre/projet
git clone https://github.com/bastienchevalier5/carnapprenti.git .
```


## 2. Configurer l'environnement

Copiez le fichier .env.example en .env

```bash
cp .env.example .env
```
Configurez les paramètres de votre environnement, notamment les informations de connexion à la base de données.

```php
// Changer le nom de l'application
APP_NAME='Gestion des livrets d'apprentissage'

// Changer le Timezone de l'application
APP_TIMEZONE='Europe/Paris'

// Changer l'url de  l'application
APP_URL=http://localhost

// Changer les informations sur la langue
APP_LOCALE=fr
APP_FAKER_LOCALE=fr_FR

// Changer les informations pour que cela corresponde à votre base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1 ou l'adresse de votre base de données
DB_PORT=3306
DB_DATABASE=nom_de_votre_base_de_donnees
DB_USERNAME=votre_nom_d'utilisateur
DB_PASSWORD=votre_mot_de_passe

```

## 3. Installer les dépendances

```bash
composer install
npm install
npm run build
npm run dev
```

## 4. Générer la clé de l'application

```bash
php artisan key:generate
```

## 5. Exécuter les migrations

```bash
php artisan migrate
```

## 6. Remplir la base de données

```bash
php artisan db:seed
```
## 7. Logins et mot de passes

Apprenant : 
Email : apprenant@apprenant.fr
Mot de passe : apprenant

Tuteur :
Email : tuteur@tuteur.fr
Mot de passe : tuteur

Référent :
Email : referent@referent.fr
Mot de passe : referent


## 8. Accéder à l'application

Maintenant, vous devrez pouvoir atteindre l'application en allant sur l'url que vous avez indiqué.
