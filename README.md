# Micro-Learning

Micro-Learning est une plateforme de formation courte développée avec Symfony.

## Ce que contient le projet

- Symfony 7.x
- Doctrine ORM + Migrations
- Symfony Workflow pour le cycle de vie des tutoriels
- Sécurité avec authentification utilisateur et rôles
- Interface Tailwind CSS
- Formulaires imbriqués pour les étapes de tutoriels
- Gestion de la modération (soumission, validation, rejet)

## Structure principale

- `src/Entity/` : entités `User`, `Tutorial`, `Step`, `Admin`
- `src/Repository/` : accès aux données
- `src/Form/` : formulaires Symfony
- `src/Controller/` : contrôleurs front et admin
- `templates/` : interfaces Twig
- `config/packages/workflow.yaml` : configuration du workflow
- `migrations/` : migrations de base de données

## Lancer le projet

1. Installer les dépendances :

```bash
composer install
```

2. Configurer la base de données :

- Copier `.env` en `.env.local`
- Modifier `DATABASE_URL` avec les informations de votre base PostgreSQL

3. Exécuter les migrations :

```bash
php bin/console doctrine:migrations:migrate
```

4. Créer un compte utilisateur ou admin :

```bash
php bin/console app:create-user
```

5. Lancer le projet avec une seule commande :

```bash
composer start
```

Cette commande démarre le serveur Symfony local. Si vous n'avez pas le binaire Symfony installé, utilisez :

```bash
php -S localhost:8000 -t public
```

6. Ouvrir le projet dans le navigateur :

```text
http://localhost:8000
```

## Utilisation

- Se connecter via `/login`
- Créer un tutoriel depuis `/tutorials/create`
- Soumettre un tutoriel pour validation
- Les admins peuvent approuver ou rejeter depuis `/admin/tutorials/review`

## Remarque

Le projet utilise une table `app_user` pour éviter les conflits avec le mot réservé SQL `user`.
