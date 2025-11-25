# Coffre-fort Numérique - Jour 2

## 🎯 Fonctionnalités implémentées

### Backend (PHP/Slim)

✅ **CRUD Dossiers**
- Création, lecture, modification, suppression de dossiers
- Hiérarchie de dossiers (parent_id)
- Liste des fichiers par dossier

✅ **Upload avec chiffrement**
- Option de chiffrement AES-256-CBC lors de l'upload
- Déchiffrement automatique lors du téléchargement
- Service `EncryptionService` réutilisable

✅ **Gestion des quotas**
- Vérification du quota lors de l'upload
- Endpoint `/stats` pour visualiser l'utilisation

✅ **Partage public**
- Génération de liens publics `/s/{token}`
- Expiration configurable
- Limitation du nombre de téléchargements
- Page web élégante avec Bootstrap

### Frontend Web

✅ **Page d'accueil publique**
- Design moderne avec Bootstrap 5
- Présentation des fonctionnalités
- Page responsive et professionnelle

✅ **Page de partage public**
- Interface élégante pour télécharger les fichiers partagés
- Affichage des informations (taille, téléchargements restants)
- Expiration visible

## 📁 Structure des fichiers créés

```
src/
├── Controller/
│   ├── FileController.php (mis à jour)
│   ├── FolderController.php (nouveau)
│   └── ShareController.php (nouveau)
├── Model/
│   ├── FileRepository.php
│   ├── FolderRepository.php (nouveau)
│   └── ShareRepository.php (nouveau)
└── Service/
    └── EncryptionService.php (nouveau)

public/
├── index.php (mis à jour)
└── index.html (nouveau)

database/
└── migration_day2.sql (nouveau)
```

## 🗄️ Migration de la base de données

Exécutez le script SQL pour créer les nouvelles tables :

```bash
mysql -u votre_utilisateur -p votre_base < database/migration_day2.sql
```

Ou via phpMyAdmin, importez le fichier `database/migration_day2.sql`.

### Tables ajoutées :
- `folders` : Gestion des dossiers avec hiérarchie
- `shares` : Liens de partage publics avec token

### Colonnes ajoutées à `files` :
- `folder_id` : Lien vers le dossier parent
- `is_encrypted` : Indicateur de chiffrement

## 🚀 API Endpoints

### Fichiers
- `GET /files` - Liste tous les fichiers
- `GET /files/{id}` - Détails d'un fichier
- `POST /files` - Upload (form-data: file, folder_id, encrypt)
- `GET /files/{id}/download` - Télécharger
- `DELETE /files/{id}` - Supprimer
- `GET /stats` - Statistiques (quota, taille totale)

### Dossiers
- `GET /folders` - Liste des dossiers
- `GET /folders/{id}` - Détails d'un dossier
- `POST /folders` - Créer (JSON: {name, parent_id})
- `PUT /folders/{id}` - Modifier
- `DELETE /folders/{id}` - Supprimer
- `GET /folders/{id}/files` - Fichiers du dossier

### Partages
- `GET /shares` - Liste des partages
- `POST /shares` - Créer (JSON: {file_id, expires_at, max_downloads})
- `DELETE /shares/{id}` - Supprimer
- `GET /s/{token}` - Page publique de téléchargement
- `GET /s/{token}/download` - Télécharger via lien public

## 📝 Exemples d'utilisation

### Upload d'un fichier chiffré dans un dossier

```bash
curl -X POST http://localhost/files \
  -F "file=@document.pdf" \
  -F "folder_id=1" \
  -F "encrypt=1"
```

### Créer un dossier

```bash
curl -X POST http://localhost/folders \
  -H "Content-Type: application/json" \
  -d '{"name": "Documents confidentiels", "parent_id": null}'
```

### Créer un partage public

```bash
curl -X POST http://localhost/shares \
  -H "Content-Type: application/json" \
  -d '{
    "file_id": 5,
    "expires_at": "2025-12-31 23:59:59",
    "max_downloads": 10
  }'
```

Réponse :
```json
{
  "message": "Share created",
  "id": 1,
  "token": "a1b2c3d4e5f6...",
  "url": "/s/a1b2c3d4e5f6..."
}
```

## 🔐 Configuration du chiffrement

La clé de chiffrement peut être configurée via la variable d'environnement :

```env
ENCRYPTION_KEY=votre-cle-secrete-tres-longue-et-complexe
```

**⚠️ Important :** Changez cette clé en production et conservez-la précieusement. 
Sans cette clé, vous ne pourrez plus déchiffrer vos fichiers !

## 🎨 Interface Web

- **Page d'accueil** : `http://localhost/index.html`
- **API racine** : `http://localhost/` (liste des endpoints)
- **Lien de partage** : `http://localhost/s/{token}`

## 🔄 Prochaines étapes (Jour 3+)

- [ ] Client JavaFX avec interface graphique
- [ ] Authentification utilisateur
- [ ] Permissions et rôles
- [ ] Corbeille avec restauration
- [ ] Prévisualisation de fichiers
- [ ] Recherche avancée
- [ ] Logs d'activité

## 📚 Technologies utilisées

- **Backend** : PHP 8+, Slim Framework 4
- **ORM** : Medoo
- **Base de données** : MySQL
- **Chiffrement** : OpenSSL (AES-256-CBC)
- **Frontend** : Bootstrap 5, Bootstrap Icons
- **API** : REST JSON

## 🧪 Test rapide

1. Exécutez la migration SQL
2. Créez un dossier via l'API
3. Uploadez un fichier chiffré dans ce dossier
4. Créez un partage public
5. Visitez l'URL `/s/{token}` pour télécharger

## 💡 Notes de développement

### Chiffrement
Le service `EncryptionService` utilise AES-256-CBC avec un IV aléatoire pour chaque fichier. 
L'IV est stocké avec les données chiffrées (format: `base64(iv::encrypted_data)`).

### Partages publics
Les tokens sont générés avec `random_bytes(16)` convertis en hexadécimal (32 caractères).
Le système vérifie automatiquement l'expiration et le nombre de téléchargements.

### Organisation des fichiers
Les fichiers physiques sont stockés dans `storage/uploads/` avec un nom unique généré par `uniqid()`.
Les fichiers chiffrés ont l'extension `.enc` ajoutée.

---

Développé pour le cours SLAM - Projet pédagogique
