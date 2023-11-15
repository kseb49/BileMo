# BileMo - Sélection de téléphones mobiles haut de gamme

## Presentation du projet
API REST BileMO réalisé avec [**Symfony 6**](https://symfony.com/).

Réalisé dans le cadre de la formation _développeur d'application PHP/symfony_ d'OpenClassrooms.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/8e1d62294ec4422aa6a77919083d84f4)](https://app.codacy.com/gh/kseb49/BileMo/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

## Description
**BileMo** est une entreprise offrant toute une sélection de téléphones mobiles haut de gamme en B2B.
Une API REST fournie à toutes les plateformes qui le souhaitent l’accès au catalogue.

Les clients de l’API sont authentifiés via JWT.


## Prè-requis

PHP
[**PHP 8.1**](https://www.php.net/downloads) ou supèrieur

MySQL
**MySQL 8.0** ou supèrieur.

Composer
[**Composer 2.4**](https://getcomposer.org/download/) ou supèrieur.

## Installation

Cloner le projet

```https://github.com/kseb49/BileMo.git```

Installer les dépendances

 ```composer install```

_Variables d'environnements_ : Configurer un fichier _.env.local_:
 ```Dotenv
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
```
Pour une mise en production :

```Dotenv
APP_ENV=prod
APP_SECRET=!new32characterskey!
```
Créer les clefs SSL [_aide_](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#generate-the-ssl-keys)

`symfony console lexik:jwt:generate-keypair`

Configurer les chemins vers les clefs et la passphrase dans le _env.local_ [_aide_](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#configuration):

```dotenv
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=yourPassphrase
```
Plus d'infos : [Documentation officielle JWT](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#getting-started)

 Créez la base de données et les tables:

```symfony console doctrine:database:create```

```symfony console doctrine:migrations:migrate```

Charger les données initiales

```symfony console doctrine:fixtures:load```

Lancer le serveur symfony

`symfony server:start`
## Rôles
Les endpoints suivants nécessitent un rôle administrateur :

<span style="color: #2ecc71">POST</span>
`/api/users`

<span style="color: #e74c3c">DELETE</span>
`/api/users/{{id}}`


Les [données initiales](https://github.com/kseb49/BileMo#installation) octroient ce rôle à un client sur deux.

## Documentation

La documentation est disponible au lien suivant :

`/api/doc`

