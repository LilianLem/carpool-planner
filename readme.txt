=== Installation du projet ===

- Récupérer les fichiers du repo GitHub
- Taper "composer install" dans un terminal à la racine du projet

- Créer un fichier config.php à la racine, et compléter ce modèle :
const WEBSITE_EMAIL_HOST = 'hote_de_serveur_mail';
const WEBSITE_EMAIL_PORT = port_de_serveur_mail;
const WEBSITE_EMAIL = 'adresse_mail_d_envoi';
const WEBSITE_EMAIL_PASSWORD = 'mot_de_passe_de_l_adresse_mail';

- Créer une base de données carpoolplanner et importer les données fournies sur demande
- Modifier DatabaseManager.php dans le répertoire model avec les logins et l'emplacement de la base de données