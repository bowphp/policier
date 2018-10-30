# Policier

<a href="https://bowphp.github.io/docs/policier" title="docs"><img src="https://img.shields.io/badge/docs-read%20docs-blue.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/bowphp/policier" title="version"><img src="https://img.shields.io/packagist/v/bowphp/policier.svg?style=flat-square"/></a>
<a href="https://github.com/bowphp/policier/blob/master/LICENSE" title="license"><img src="https://img.shields.io/github/license/mashape/apistatus.svg?style=flat-square"/></a>
<a href="https://travis-ci.org/bowphp/policier" title="Travis branch"><img src="https://img.shields.io/travis/bowphp/policier/master.svg?style=flat-square"/></a>

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Set or Get configuration](#set-or-get-configuration)
  - [Set Configuration](#set-configuration)
  - [Get configuration](#get-configuration)
  - [Encode Token](#encode-token)
  - [Decode Token](#decode-token)
  - [Parse Token](#parse-token)
  - [Verify Token](#verify-token)
  - [Validate Token](#validate-token)
- [Bow and Policier](#bow-and-policier)
  - [Personnalisation du Middleware](#personnalisation-du-middleware)
  - [Publier le middleware](#publier-le-middleware)

La police permet de valider la demande via [JWT](https://jwt.io)

## Installation

Pour installer la stratégie d'installation, vous devez utiliser `composer` (gestionnaire de paquets PHP) comme ceci.

```bash
composer require bowphp/policier
```

## Configuration

Vous pouvez regarder toutes les options de configuration ici.

```php
return [
  /**
   * Heure d'expiration du token
   */
  "exp" => 3600,

  /**
   * Le token est utilisable après cette heure
   */
  "nbf" => 60,

  /**
   * Le token était émis
   */
  "iat" => 60,

  /**
   * Configure l'émetteur
   */
  "iss" => "localhost",

  /**
   * Configure le public
   */
  "aud" => "localhost",

  /**
   * Algorithme de hachage utilisé
   *
   * HS256, HS384, HS512, RS256, RS384, RS512, ES256, ES384, ES512,
   */
  "alg" => "HS512",

  /**
   * Votre Signature, ce champs est obligatoire pour les autres type de hachage sauf RSA
   */
  'signkey' => null,

  /**
   * Signature en utilisant votre RSA, ce chargera automatique si la clé de hashage est de type RSA
   */
  "keychain" => [
    /**
     * Chemin vers votre clé privée
     */
    "private" => null,

    /**
     * Chemin vers votre clé publique
     */
    "public" => null
  ]
];
```

## Usage

Policier est très simple d'utilisation et possède une API claire. La configuration retourne une singleton.

```php
use Bow\Jwt\Policier;

$configure = require "/path/to/config/file.php";

$policier = Policier::configure($configure);
```

OU

```php
use Bow\Jwt\Policier;

$configure = require "/path/to/config/file.php";

Policier::configure($configure);

$policier = Policier::getInstance();
```

Après la configuration, vous pouvez utiliser le helper `policier`:

```php
policier($action, ...$args);
```

La valeur d'action doit être l'une de ces valeurs: `encode`, `decode`, `parse`, `verify`, `validate`.

## Set or Get configuration

### Set Configuration

Vous pouvez mettre à jour la configuration de base avec la méthode `setConfig`:

```php
$policier->setConfig('exp', time() + 72000);
```

### Get configuration

Vous pouvez également obtenir les informations de configuration avec la méthode `getConfig`:

```php
$policier->getConfig('exp');
```

### Encode Token

Encoder rapidement un token:

```php
$id = uniqid();

$claims = [
  "name" => "Franck",
  "nickname" => "papac",
  "logged" => true
];

$token = $policier->encode($id, $claims);

$token->expiredIn(); // Expired In
$token->getToken(); // Token value

echo $token;
//=> eyJ0eXAiOiJKV1QiLCJhbGciOiI6IjEifQ.eyJpc3MiOiJsb2NhbGhvc3QiLCJhdWQiOiJsb2NhbGhvc3QiLCJqdGkiOi.l7v0bS0rqnK1IeRGRBTFIH5s2TN9KtgD7BLivApq
```

`$token` est une instance de `Bow\Jwt\Token` et implémente la méthode magique `__toString`. Vous pouvez obtenir l'heure d'expiration avec `expiredIn` et `getToken` pour prendre la valeur du token.

Via l'assistant:

```php
policier('encode', $id, $claims);
```

### Decode Token

Même chose pour le décodage de token:

```php
$result = $policier->decode($token);
$result['headers'];

echo $result['claims']['name'];
//=> Franck
```

Via l'assistant:

```php
policier('decode', $token);
```

### Parse Token

```php
$token = $policier->parse($token);

$token->hasHeader("old") // Vérifier si l'en-tête existe
$token->getHeader("alg", $default = null); // Obtenez un en-tête
$token->getHeaders(); // Obtenir tous les en-têtes

$token->hasClaim("name") // Vérifier si la réclamation existe
$token->getClaim("name", $default = null); // Obtenez une réclamation
$token->getClaims(); // Obtenez toutes les réclamations

$token->isExpired(); // Vérifier si le token a expiré

echo $token->getClaim("name");
//=> Franck
```

Via l'assistant:

```php
policier('parse', $token);
```

### Verify Token

Vérifier si le jeton est valide avec tous les attributs JWT.

```php
$verified = $policier->verify($token);

if ($verified) {
  echo "Token est valide";
} else {
  echo "Token n'est pas valide";
}
```

Via l'assistant:

```php
policier('verify', $token);
```

### Validate Token

Validez le jeton avec les informations de réclamation et les informations `exp`.

```php
$claims = [
  "name" => "Franck",
  "nickname" => "papac",
  "logged" => true
];
```

## Bow and Policier

Si vous utilisez [Bow Framework](https://github.com/bowphp/app), vous pouvez utiliser le plugin de configuration `Bow\Jwt\PolicierConfiguration::class` qui lie automatiquement le middleware `Bow\Jwt\PolicierMiddeware::class`. Ce middleware est utilisable via l'alias `api`.

Relier la configuration sur `app\Kernel\Loader.php`:

```php
public function configurations()
{
  return [
    ...
    Bow\Jwt\PolicierConfiguration::class,
    ...
  ];
}
```

Utilisez le middleware:

```php
$app->get('/api', function () {
  $token  = policier()->getToken();
})->middleware('api');
```

Le token a été analysé dans l'instance de Policier dans un processus middleware via la méthode `plug`. Avant l'exécution du middleware, vous pouvez:

- Obtenez le token avec `getToken`
- Décoder le token avec `getDecodeToken` - [More information of token parsed](#decode-token)
- Analyser le token avec `getParsedToken` - [More information of token parsed](#parse-token)

### Personnalisation du Middleware

Notez que vous pouvez créer un autre middleware qui étendra le middleware par defaut `Bow\Jwt\PolicierMiddeware::class`. Ce qui vous donne la possibilité de changer les messages d'erreur en surchargant les methodes `getUnauthorizedMessage`, `getExpirateMessage` et `getCode`.

```bash
php bow add:middleware CustomPolicierMiddleware
```

et ensuite vous pouvez faire ceci:

```php

use Bow\Http\Request;
use Bow\Jwt\PolicierMiddeware;

class CustomPolicierMiddleware extends PolicierMiddeware
{
  /**
   * Get Error message
   *
   * @return array
   */
  public function getUnauthorizedMessage()
  {
    return [
      'message' => 'unauthorized',
      'error' => true
    ];
  }

  /**
   * Get Error message
   *
   * @return array
   */
  public function getExporateMessage()
  {
    return [
      'message' => 'token is expired',
      'expired' => true,
      'error' => true
    ];
  }

  /**
   * Get Expirate response code
   *
   * @return int
   */
  public function getExpirateCode()
  {
    return 403;
  }

  /**
   * Get Unauthorized response code
   *
   * @return int
   */
  public function getUnauthorizedCode()
  {
    return 403;
  }
}
```

### Publier le middleware

Pour publier le middleware personnalisé et écraser celui par defaut de Policier c'est très simple, il suffit seulement d'ajouter le middleware dans le fichier `app/Kernel/Loader.php` avec la clé `api`.

```php
publuc function middlewares()
{
  return [
    ...
    'api' => \App\Middleware\CustomPolicierMiddleware::class,
    ...
  ];
}
```

> N'hésitez pas à donner votre avis sur la qualité de la documentation ou proposez des correctifs.