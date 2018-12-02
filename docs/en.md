# Policier

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

Policier allows to validate the request via [JWT](https://jwt.io).

## Installation

To install the installation policy, you must use `composer` (PHP package manager) like this.

```bash
composer require bowphp/policier
```

## Configuration

You can look at all the configuration options here.

```php
return [
  /**
   * Token expiration time
   */
  "exp" => 3600,

  /**
   * The token can be used after this time
   */
  "nbf" => 60,

  /**
   * The token was issued
   */
  "iat" => 60,

  /**
   * Configure the transmitter
   */
  "iss" => "localhost",

  /**
   * Configure the audience
   */
  "aud" => "localhost",

  /**
   * Hash algorithm used
   *
   * HS256, HS384, HS512, RS256, RS384, RS512, ES256, ES384, ES512,
   */
  "alg" => "HS512",

  /**
   * Your Signature, this field is required for other types of hash except RSA
   */
  'signkey' => null,

  /**
   * Signature using your RSA, this will load automatically if the hash key is RSA type
   */
  "keychain" => [
    /**
     * Path to your private key
     */
    "private" => null,

    /**
     * Path to your public key
     */
    "public" => null
  ],
  
  /**
   * Policier middleware name
   */
  'middleware_name' => 'api',
];
```

## Usage

Policier is very easy to use and has a clear API. The configuration returns a singleton.

```php
use Bow\Jwt\Policier;

$configure = require "/path/to/config/file.php";

$policier = Policier::configure($configure);
```

You can also do like this:

```php
use Bow\Jwt\Policier;

$configure = require "/path/to/config/file.php";

Policier::configure($configure);

$policier = Policier::getInstance();
```

After configuration, you can use the `policier` helper:

```php
policier($action, ...$args);
```

The value of action must be one of `encode`, `decode`, `parse`, `verify`, `validate`.

## Set or Get configuration

### Set Configuration

You can update configuration base with `setConfig` method:

```php
$policier->setConfig('exp', time() + 72000);
```

### Get configuration

You can also get configuration with `getConfig` method:

```php
$policier->getConfig('exp');
```

### Encode Token

Token encoding is very quickly:

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

`$ token` is an instance of `Bow\Jwt\Token` and implements the `__toString` magic method. You can get the expiration time with `expiredIn` and ` getToken` to take the value of the token.

Via helper:

```php
policier('encode', $id, $claims);
```

### Decode Token

Same thing for token decoding:

```php
$result = $policier->decode($token);
$result['headers'];

echo $result['claims']['name'];
//=> Franck
```

Via helper:

```php
policier('decode', $token);
```

### Parse Token

```php
$token = $policier->parse($token);

$token->hasHeader("old") // Check if the header exists
$token->getHeader("alg", $default = null); // Get a header
$token->getHeaders(); // Get all headers

$token->hasClaim("name") // Check if the claim exists
$token->getClaim("name", $default = null); // Get a claim
$token->getClaims(); // Get all the complaints

$token->isExpired(); // Check if the token has expired

echo $token->getClaim("name");
//=> Franck
```

Via helper:

```php
policier('parse', $token);
```

### Verify Token

Check if the token is valid with all JWT attributes.

```php
$verified = $policier->verify($token);

if ($verified) {
  echo "Token est valide";
} else {
  echo "Token n'est pas valide";
}
```

Via helper:

```php
policier('verify', $token);
```

### Validate Token

Validate the token with claim information and `exp` information.

```php
$claims = [
  "name" => "Franck",
  "nickname" => "papac",
  "logged" => true
];

$validated = $policier->validate($token, $claims);

if ($validated) {
  echo "Les informations sont valides";
} else {
  echo "Les informations ne sont pas valides";
}
```

Via helper:

```php
$claims = [
  "name" => "Franck",
  "nickname" => "papac",
  "logged" => true
];

policier('validate', $token, $claims);
```

## Bow Framework and Policier

If you're using [Bow Framework](https://github.com/bowphp/app), you can use the `Bow\Jwt\PoliceConfiguration::class` configuration plugin that automatically binds the `Bow\Jwt\PoliceMiddleware::class`. This middleware is usable via the `api` alias.

Connect the configuration on `app\Kernel\Loader.php`:

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

Use the middleware:

```php
$app->get('/api', function () {
  $token  = policier()->getToken();
})->middleware('api');
```

The token was parsed in the instance of Police in a middleware process via the `plug` method. Before running the middleware, you can:

- Get the token with `getToken`
- Decode the token with `getDecodeToken` - [More info of token parsed](#decode-token)
- Analyze the token with `getParsedToken` - [More info of token parsed](#parse-token)

### Customization of Middleware

Note that you can create another middleware that will extend the default middleware to `Bow\Jwt\PoliceMiddleware::class`. This gives you the ability to change error messages by overriding the `getUnauthorizedMessage`,` getExpirateMessage`, `getExpirateCode`, and` getUnauthorizedCode` methods.

```bash
php bow add:middleware CustomPolicierMiddleware
```

and then you can do this:

```php

use Bow\Http\Request;
use Bow\Jwt\PolicierMiddleware;

class CustomPolicierMiddleware extends PolicierMiddleware
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
  public function getExpirationMessage()
  {
    return [
      'message' => 'token is expired',
      'expired' => true,
      'error' => true
    ];
  }

  /**
   * Get Unauthorized response code
   *
   * @return int
   */
  public function getUnauthorizedStatusCode()
  {
    return 403;
  }

  /**
   * Get Expirate response code
   *
   * @return int
   */
  public function getExpirationStatusCode()
  {
    return 403;
  }
}
```

### Publish the middleware

To publish the custom middleware and overwrite the default one of Police is very simple, just add the middleware in the file `app/Kernel/Loader.php` with the key` api`.

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

> Feel free to give your opinion on the quality of the documentation or suggest corrections.