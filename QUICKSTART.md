# 🚀 Guide de démarrage rapide - Jour 2

## Installation

### 1. Configuration de la base de données

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Modifier .env avec vos paramètres MySQL
DB_NAME=file_vault
DB_USER=root
DB_PASS=votremotdepasse

# Générer une clé de chiffrement forte
ENCRYPTION_KEY=votre-cle-secrete-tres-longue-et-aleatoire-32-chars-minimum
```

### 2. Migration de la base de données

Exécutez le script SQL pour créer les tables :

```bash
mysql -u root -p file_vault < database/migration_day2.sql
```

Ou via phpMyAdmin : Importer `database/migration_day2.sql`

### 3. Vérifier les dépendances

```bash
composer install
```

### 4. Lancer le serveur

```bash
# PHP built-in server
php -S localhost:8000 -t public

# Ou avec XAMPP/WAMP, accéder via
# http://localhost/Coffre-fort_num-rique/public/
```

## Test rapide

### Via script PHP

```bash
php test_api.php
```

### Via cURL

```bash
# Créer un dossier
curl -X POST http://localhost/folders \
  -H "Content-Type: application/json" \
  -d '{"name":"Documents","parent_id":null}'

# Upload un fichier (non chiffré)
curl -X POST http://localhost/files \
  -F "file=@test.pdf" \
  -F "folder_id=1" \
  -F "encrypt=0"

# Upload un fichier chiffré
curl -X POST http://localhost/files \
  -F "file=@secret.pdf" \
  -F "folder_id=1" \
  -F "encrypt=1"

# Créer un partage
curl -X POST http://localhost/shares \
  -H "Content-Type: application/json" \
  -d '{"file_id":1,"expires_at":"2025-12-31 23:59:59","max_downloads":10}'

# Lister les fichiers
curl http://localhost/files

# Statistiques
curl http://localhost/stats
```

### Via Postman

1. Importer la collection : `postman_collection.json`
2. Modifier l'URL de base si nécessaire
3. Tester les endpoints

## Accès web

- **Page d'accueil** : http://localhost/index.html
- **API root** : http://localhost/
- **Documentation OpenAPI** : `openapi.yaml` (à ouvrir avec Swagger Editor)

## Fonctionnalités disponibles

✅ CRUD complet sur les dossiers  
✅ Upload de fichiers avec ou sans chiffrement AES-256  
✅ Organisation hiérarchique (dossiers parents/enfants)  
✅ Quotas de stockage  
✅ Partage public avec token sécurisé  
✅ Expiration et limitation de téléchargements  
✅ Déchiffrement automatique lors du téléchargement  
✅ Interface web Bootstrap responsive

## Structure des URLs

| Endpoint               | Méthode        | Description           |
| ---------------------- | -------------- | --------------------- |
| `/files`               | GET            | Liste des fichiers    |
| `/files`               | POST           | Upload fichier        |
| `/files/{id}`          | GET            | Détails fichier       |
| `/files/{id}/download` | GET            | Télécharger           |
| `/files/{id}`          | DELETE         | Supprimer             |
| `/folders`             | GET            | Liste dossiers        |
| `/folders`             | POST           | Créer dossier         |
| `/folders/{id}`        | GET/PUT/DELETE | CRUD dossier          |
| `/folders/{id}/files`  | GET            | Fichiers du dossier   |
| `/shares`              | GET/POST       | Gérer partages        |
| `/shares/{id}`         | DELETE         | Supprimer partage     |
| `/s/{token}`           | GET            | Page publique         |
| `/s/{token}/download`  | GET            | Téléchargement public |
| `/stats`               | GET            | Statistiques          |

## Troubleshooting

### Erreur 500

- Vérifier les logs PHP
- Vérifier que le dossier `storage/uploads` existe et est accessible en écriture
- Vérifier la connexion à la base de données dans `.env`

### Upload échoue

```bash
# Vérifier les permissions
chmod 777 storage/uploads

# Ou sur Windows
# Propriétés > Sécurité > Modifier > Accorder le contrôle total
```

### Chiffrement échoue

- Vérifier que l'extension OpenSSL est activée dans PHP
- Vérifier que `ENCRYPTION_KEY` est définie dans `.env`

### 404 sur les routes

- Vérifier que le module Apache `mod_rewrite` est activé
- Vérifier le fichier `.htaccess` dans `public/`

## Prochaines étapes

Le Jour 3 ajoutera :

- Client JavaFX avec interface graphique
- Authentification utilisateur
- Gestion des permissions
- Plus de fonctionnalités...

## Support

Consultez `README_DAY2.md` pour plus de détails.
