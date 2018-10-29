# Policier

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

### Encode Token

Quicky token token

```php
$id = uniqid();

$claims = [
	"name" => "Franck",
	"nickname" => "papac",
	"logged" => true
];

$token = $policier->encode($id, $claims);
echo $token;
//=> example:
//=> eyJ0eXAiOiJKV1QiLCJhbGciOiI6IjEifQ.eyJpc3MiOiJsb2NhbGhvc3QiLCJhdWQiOiJsb2NhbGhvc3QiLCJqdGkiOi.l7v0bS0rqnK1IeRGRBTFIH5s2TN9KtgD7BLivApq
```

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

$token->getHeader("alg"); // Get one header
$token->getHeaders(); // Get all header
$token->getClaim("name"); // Get one claim
$token->getClaims(); // Get all claims

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