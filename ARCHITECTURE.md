# Architecture du Coffre-fort Numérique - Jour 2

```
┌─────────────────────────────────────────────────────────────────┐
│                         FRONTEND WEB                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  index.html  │  │ /s/{token}   │  │ Bootstrap 5  │          │
│  │  (Accueil)   │  │  (Partage)   │  │   + Icons    │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                            ▼ HTTP
┌─────────────────────────────────────────────────────────────────┐
│                        API REST (Slim)                           │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    public/index.php                       │  │
│  │  ┌────────────┐  ┌────────────┐  ┌────────────┐         │  │
│  │  │   CORS     │  │   Routes   │  │   Errors   │         │  │
│  │  │ Middleware │  │  Routing   │  │  Handler   │         │  │
│  │  └────────────┘  └────────────┘  └────────────┘         │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                        CONTROLLERS                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │     File     │  │    Folder    │  │    Share     │          │
│  │  Controller  │  │  Controller  │  │  Controller  │          │
│  │              │  │              │  │              │          │
│  │ • list       │  │ • list       │  │ • list       │          │
│  │ • show       │  │ • show       │  │ • create     │          │
│  │ • upload     │  │ • create     │  │ • delete     │          │
│  │ • download   │  │ • update     │  │ • showPublic │          │
│  │ • delete     │  │ • delete     │  │ • download   │          │
│  │ • stats      │  │ • getFiles   │  │   Public     │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                        REPOSITORIES                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │     File     │  │    Folder    │  │    Share     │          │
│  │  Repository  │  │  Repository  │  │  Repository  │          │
│  │              │  │              │  │              │          │
│  │ • listFiles  │  │ • listFolders│  │ • listShares │          │
│  │ • find       │  │ • find       │  │ • find       │          │
│  │ • create     │  │ • create     │  │ • findByToken│          │
│  │ • delete     │  │ • update     │  │ • create     │          │
│  │ • totalSize  │  │ • delete     │  │ • delete     │          │
│  │ • quotaBytes │  │ • getFiles   │  │ • canDownload│          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                           SERVICES                               │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              EncryptionService (AES-256-CBC)              │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │  │
│  │  │ encryptFile  │  │ decryptFile  │  │   encrypt    │   │  │
│  │  │              │  │              │  │   decrypt    │   │  │
│  │  └──────────────┘  └──────────────┘  └──────────────┘   │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                        ORM (Medoo)                               │
└─────────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                      BASE DE DONNÉES (MySQL)                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │    files     │  │   folders    │  │    shares    │          │
│  │              │  │              │  │              │          │
│  │ • id         │  │ • id         │  │ • id         │          │
│  │ • filename   │  │ • name       │  │ • file_id    │          │
│  │ • stored_name│  │ • parent_id  │  │ • token      │          │
│  │ • size       │  │ • created_at │  │ • expires_at │          │
│  │ • mime_type  │  └──────────────┘  │ • max_down.  │          │
│  │ • folder_id  │       ▲            │ • downloads  │          │
│  │ • is_encrypt │       │ FK         │ • created_at │          │
│  │ • uploaded_at│       │            └──────────────┘          │
│  └──────────────┘       │                    ▲                  │
│         │               │                    │ FK               │
│         └───────────────┘────────────────────┘                  │
│             Clé étrangère (folder_id, file_id)                  │
└─────────────────────────────────────────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                      SYSTÈME DE FICHIERS                         │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    storage/uploads/                       │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │  │
│  │  │ f_abc123.pdf │  │ f_def456.txt │  │ f_ghi789.enc │   │  │
│  │  │  (Normal)    │  │  (Normal)    │  │  (Chiffré)   │   │  │
│  │  └──────────────┘  └──────────────┘  └──────────────┘   │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘


FLUX DE DONNÉES - UPLOAD AVEC CHIFFREMENT
═══════════════════════════════════════════

1. Client (Web/JavaFX/cURL)
   │
   ├─ POST /files
   │  • file: fichier binaire
   │  • folder_id: 1
   │  • encrypt: 1
   │
   ▼
2. FileController::upload()
   │
   ├─ Vérifier le quota
   │  (totalSize + fileSize < quotaBytes ?)
   │
   ├─ Sauvegarder temporairement
   │  storage/uploads/temp_f_xxxxx
   │
   ├─ EncryptionService::encryptFile()
   │  │
   │  ├─ Lire le fichier original
   │  ├─ Générer un IV aléatoire
   │  ├─ Chiffrer avec AES-256-CBC
   │  ├─ Sauvegarder (IV + données chiffrées)
   │  │
   │  ▼
   │  storage/uploads/f_xxxxx.enc
   │
   ├─ FileRepository::create()
   │  • filename: "document.pdf"
   │  • stored_name: "f_xxxxx.enc"
   │  • is_encrypted: 1
   │  • folder_id: 1
   │
   ▼
3. Réponse JSON
   {
     "message": "File uploaded",
     "id": 42
   }


FLUX DE DONNÉES - TÉLÉCHARGEMENT
════════════════════════════════

1. Client
   │
   ├─ GET /files/42/download
   │
   ▼
2. FileController::download()
   │
   ├─ FileRepository::find(42)
   │  • is_encrypted: 1
   │  • stored_name: "f_xxxxx.enc"
   │
   ├─ EncryptionService::decryptFile()
   │  │
   │  ├─ Lire le fichier chiffré
   │  ├─ Extraire l'IV
   │  ├─ Déchiffrer avec AES-256-CBC
   │  ├─ Sauvegarder temporairement
   │  │
   │  ▼
   │  storage/uploads/temp_download_yyyyy
   │
   ├─ Envoyer le fichier déchiffré
   │  • Content-Type: application/pdf
   │  • Content-Disposition: attachment
   │
   ├─ Supprimer le fichier temporaire
   │
   ▼
3. Client reçoit le fichier original


FLUX DE DONNÉES - PARTAGE PUBLIC
═════════════════════════════════

1. Créer un partage
   │
   ├─ POST /shares
   │  {
   │    "file_id": 42,
   │    "expires_at": "2025-12-31 23:59:59",
   │    "max_downloads": 10
   │  }
   │
   ▼
2. ShareController::create()
   │
   ├─ Générer token sécurisé
   │  random_bytes(16) → hex
   │
   ├─ ShareRepository::create()
   │
   ▼
3. Réponse
   {
     "token": "a1b2c3d4...",
     "url": "/s/a1b2c3d4..."
   }

4. Utilisateur visite /s/a1b2c3d4...
   │
   ├─ ShareController::showPublic()
   │  │
   │  ├─ Vérifier token
   │  ├─ Vérifier expiration
   │  ├─ Vérifier max_downloads
   │  │
   │  ▼
   │  Afficher page HTML (Bootstrap)
   │
   ▼
5. Clic sur "Télécharger"
   │
   ├─ GET /s/a1b2c3d4.../download
   │
   ├─ ShareController::downloadPublic()
   │  │
   │  ├─ Incrémenter compteur
   │  │  downloads += 1
   │  │
   │  ├─ Rediriger vers
   │  │  /files/42/download
   │  │
   │  ▼
   └─> (voir flux de téléchargement)


ENDPOINTS API (17 routes)
══════════════════════════

FILES (6)
├─ GET    /files              Liste tous les fichiers
├─ GET    /files/{id}         Détails d'un fichier
├─ POST   /files              Upload (+ encrypt option)
├─ GET    /files/{id}/download Télécharger
├─ DELETE /files/{id}         Supprimer
└─ GET    /stats              Statistiques

FOLDERS (6)
├─ GET    /folders            Liste tous les dossiers
├─ GET    /folders/{id}       Détails d'un dossier
├─ POST   /folders            Créer un dossier
├─ PUT    /folders/{id}       Modifier un dossier
├─ DELETE /folders/{id}       Supprimer un dossier
└─ GET    /folders/{id}/files Fichiers du dossier

SHARES (5)
├─ GET    /shares             Liste des partages
├─ POST   /shares             Créer un partage
├─ DELETE /shares/{id}        Supprimer un partage
├─ GET    /s/{token}          Page publique (HTML)
└─ GET    /s/{token}/download Téléchargement public


SÉCURITÉ
════════

✓ Chiffrement AES-256-CBC
✓ IV aléatoire par fichier
✓ Clé configurable (.env)
✓ Tokens sécurisés (random_bytes)
✓ Validation des entrées
✓ Foreign keys (cascade)
✓ CORS configuré
✓ Gestion d'erreurs


TECHNOLOGIES
════════════

Backend:
├─ PHP 8+
├─ Slim Framework 4
├─ Medoo ORM
├─ OpenSSL (chiffrement)
└─ MySQL 8

Frontend:
├─ Bootstrap 5
├─ Bootstrap Icons
├─ HTML5/CSS3
└─ JavaScript vanilla

Outils:
├─ Composer (dépendances)
├─ Postman (tests)
└─ Git (versioning)
