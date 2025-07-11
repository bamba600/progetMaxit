# Instructions d'installation MAXITSA

## 1. Base de données

### Configuration de la base de données :
```sql
CREATE DATABASE maxit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

La base de données est déjà configurée avec toutes les tables nécessaires :
- `utilisateur` : Gestion des utilisateurs
- `profil` : Profils des utilisateurs (client, service commercial)
- `compte` : Comptes bancaires
- `transaction` : Transactions financières

## 2. Configuration du serveur web

### Serveur de développement PHP :
```bash
cd /home/bamba/Téléchargements/Proget_MAxit/public
php -S localhost:8000
```

### Apache (avec mod_rewrite) :
```apache
<VirtualHost *:80>
    ServerName maxitsa.local
    DocumentRoot /path/to/project/public
    
    <Directory /path/to/project/public>
        AllowOverride All
        Require all granted
        
        # Réécriture d'URL
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [L,QSA]
    </Directory>
</VirtualHost>
```

### Nginx :
```nginx
server {
    listen 80;
    server_name maxitsa.local;
    root /path/to/project/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\. {
        deny all;
    }
}
```

## 3. Configuration de l'environnement

Le fichier `.env` est déjà configuré avec les paramètres suivants :
```
DB_HOST=localhost
DB_NAME=maxit
DB_USER=root
DB_PASS=bamba
DB_DRIVER=mysql
AUTH_URL=http://localhost:8000
```

## 4. Permissions des fichiers

Assurez-vous que les dossiers suivants ont les permissions d'écriture :
```bash
chmod 755 public/uploads
chmod 755 vendor
```

## 5. Test de l'installation

### Démarrage du serveur de développement :
```bash
cd /home/bamba/Téléchargements/Proget_MAxit/public
php -S localhost:8000
```

### URLs principales :
- **Connexion** : http://localhost:8000/connexion
- **Création de compte** : http://localhost:8000/creer-compte
- **Tableau de bord** : http://localhost:8000/tableau-de-bord

## 6. Fonctionnalités implémentées

### ✅ Créer un compte principal
- Formulaire de création avec validation
- Upload d'images CNI (recto/verso)
- Validation des données (login unique, CNI unique, etc.)
- Création automatique du compte principal
- Connexion automatique après création

### ✅ Connexion
- Système d'authentification sécurisé
- Vérification des mots de passe hashés
- Session utilisateur
- Redirection vers le tableau de bord

### ✅ Tableau de bord
- Affichage des informations du compte principal
- Statistiques (nombre de comptes, solde total)
- Liste des dernières transactions
- Interface utilisateur moderne avec Tailwind CSS

### ✅ Gestion des erreurs
- Validation côté serveur
- Messages d'erreur contextuels
- Gestion des uploads de fichiers
- Redirection appropriée en cas d'erreur

## 7. Structure du projet

```
public/           # Point d'entrée web
├── index.php     # Bootstrap de l'application
├── uploads/      # Fichiers uploadés
└── .htaccess     # Configuration Apache

app/
├── core/         # Classes core du framework
├── config/       # Configuration de l'application
└── ...

src/
├── controller/   # Contrôleurs
├── entity/       # Entités métier
├── repository/   # Couche d'accès aux données
└── service/      # Services métier

templates/        # Templates de vues
├── connexion.php
├── creationCompte.php
├── tablauDeBord.php
└── layout/

routes/           # Définition des routes
database/         # Scripts SQL
```

## 8. Sécurité

- Mots de passe hashés avec password_hash()
- Validation des données d'entrée
- Protection contre les uploads malveillants
- Session sécurisée
- Protection des fichiers sensibles (.env, composer.json)

## 9. Prochaines étapes

Pour continuer le développement, vous pouvez :
1. Ajouter la gestion des comptes secondaires
2. Implémenter les transactions (dépôt, retrait, paiement)
3. Ajouter la génération de PDF pour les relevés
4. Implémenter la récupération de mot de passe
5. Ajouter des statistiques avancées
6. Améliorer l'interface utilisateur
