# Policier

<a href="https://bowphp.github.io/docs/policier" title="docs"><img src="https://img.shields.io/badge/docs-read%20docs-blue.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/bowphp/policier" title="version"><img src="https://img.shields.io/packagist/v/bowphp/policier.svg?style=flat-square"/></a>
<a href="https://github.com/bowphp/policier/blob/master/LICENSE" title="license"><img src="https://img.shields.io/github/license/mashape/apistatus.svg?style=flat-square"/></a>
<a href="https://travis-ci.org/bowphp/policier" title="Travis branch"><img src="https://img.shields.io/travis/bowphp/policier/master.svg?style=flat-square"/></a>


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
   * Signature en utilisant votre
   */
  'signkey' => null,

  /**
   * Signature en utilisant votre RSA
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

Policier est très simple d'utilisation et possède une API claire.

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

Après la configuration, vous pouvez utiliser l’assistant `policier`:

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

Encoder rapidement le token

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

Même chose pour le décodage de token.

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
  echo "Token is valide";
} else {
  echo "Token is not valide";
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

Si vous utilisez [Bow Framework](https://github.com/bowphp/app), vous pouvez utiliser le plugin de configuration `Bow\Jwt\PolicierConfiguration::class` et lie automatiquement le middleware `Bow\Jwt\PolicierMiddeware::class` qui est utilisable via alias `api`.

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

> N'hésitez pas à donner votre avis sur la qualité de la documentation ou proposez des correctifs.