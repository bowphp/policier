# Policier

<a href="https://bowphp.github.io/docs/policier" title="docs"><img src="https://img.shields.io/badge/docs-read%20docs-blue.svg?style=flat-square"/></a>
<a href="https://packagist.org/packages/bowphp/policier" title="version"><img src="https://img.shields.io/packagist/v/bowphp/policier.svg?style=flat-square"/></a>
<a href="https://github.com/bowphp/policier/blob/master/LICENSE" title="license"><img src="https://img.shields.io/github/license/mashape/apistatus.svg?style=flat-square"/></a>
<a href="https://travis-ci.org/bowphp/policier" title="Travis branch"><img src="https://img.shields.io/travis/bowphp/policier/master.svg?style=flat-square"/></a>

Police allows to validate the request via [JWT](https://jwt.io)

## Installation

For install policier, you must use `composer` (PHP package manager) like this. 

```bash
composer require bowphp/policier
```

## Configuration

You can look all configurations options here.

```php
return [
  /**
   * Token expiration time
   */
  "exp" => 3600,

  /**
   * Token is usable after this time
   */
  "nbf" => 60,

  /**
   * Token was issue
   */
  "iat" => 60,

  /**
   * Configures the issuer
   */
  "iss" => "localhost",

  /**
   * Configures the audience
   */
  "aud" => "localhost",

  /**
   * Hashing algorithm being used
   *
   * HS256, HS384, HS512, RS256, RS384, RS512, ES256, ES384, ES512,
   */
  "alg" => "HS512",

  /**
   * Signature using your
   */
  'signkey' => null,

  /**
   * Signature using your RSA
   */
  "keychain" => [
    /**
     * Path to your private key
     */
    "private" => null,

    /**
     * Ptah to your public key
     */
    "public" => null
  ]
];
```

## Utilisation

Policier is very simply for use and have a clear API.

### Configuration

```php
use Bow\Jwt\Policier;

$configure = require "/path/to/config/file.php";

$policier = Policier::configure($configure);
```

Or

```php
use Bow\Jwt\Policier;

$configure = require "/path/to/config/file.php";

Policier::configure($configure);

$policier = Policier::getInstance();
```

After configuration you can use the `policier` helper:

```php
policier($action, ...$args);
```

The action value must be take one of those values: `encode`, `decode`, `parse`, `verify`, `validate`.

## Update and Get configuration

### Update

You can update base config with the `setConfig` method:

```php
$policier->setConfig('exp', time() + 72000);
```

### Set configuration

You can also get the configuration information with the `getConfig` method:

```php
$policier->getConfig('exp');
``` 

### Encode Token

Quicky encode token

```php
$id = uniqid();

$claims = [
	"name" => "Franck",
	"nickname" => "papac",
	"logged" => true
];

$token = $policier->encode($id, $claims);

$token->getExpirateTime(); // Expired In
$token->getToken(); // Token value

echo $token;
//=> example:
//=> eyJ0eXAiOiJKV1QiLCJhbGciOiI6IjEifQ.eyJpc3MiOiJsb2NhbGhvc3QiLCJhdWQiOiJsb2NhbGhvc3QiLCJqdGkiOi.l7v0bS0rqnK1IeRGRBTFIH5s2TN9KtgD7BLivApq
```

`$token` is instance of `Bow\Jwt\Token` and it implement `__toString` magic method. You can get expiration time with `getExpirateTime` and `getToken` to take token value.

Via the helper:

```php
policier('encode', $id, $claims);
```

### Decode Token

Same for decode token.

```php
$result = $policier->decode($token);
$result['headers'];

echo $result['claims']['name'];
//=> Franck
```

Via the helper:

```php
policier('decode', $token);
```

### Parse Token

```php
$token = $policier->parse($token);

$token->hasHeader("old") // Check if header exists
$token->getHeader("alg", $default = null); // Get one header
$token->getHeaders(); // Get all header

$token->hasClaim("name") // Check if claim exists
$token->getClaim("name", $default = null); // Get one claim
$token->getClaims(); // Get all claims

$token->isExpired(); // Check is expirate

echo $token->getClaim("name");
//=> Franck
```

Via the helper:

```php
policier('parse', $token);
```

### Verify Token

Verify if the token is valide with all JWT attribute.

```php
$verified = $policier->verify($token);
if ($verified) {
	echo "Token is valide";
} else {
	echo "Token is not valide";
}
```

Via the helper:

```php
policier('verify', $token);
```

### Validate Token

Validate token with claim information and `exp` information.

```php
$claims = [
	"name" => "Franck",
	"nickname" => "papac",
	"logged" => true
];

$validated = $policier->validate($token, $claims);

if ($validated) {
	echo "Token is valide";
} else {
	echo "Token is not valide";
}
```

Via the helper:

```php
policier('validate', $token, $claims);
```