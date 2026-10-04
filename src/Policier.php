<?php

namespace Policier;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Policier\Token as EncodedToken;

class Policier
{
    /**
     * The configuration
     *
     * @var array
     */
    private array $config;

    /**
     * The Lcobucci JWT configuration
     *
     * @var Configuration|null
     */
    private ?Configuration $jwtConfig = null;

    /**
     * The current Token
     *
     * @var string|null
     */
    private ?string $token = null;

    /**
     * The Policier instance
     *
     * @var Policier|null
     */
    private static ?Policier $instance = null;

    /**
     * Algorithms map
     *
     * @var array<string, class-string<Signer>>
     */
    private array $algs = [
        'RS256' => \Lcobucci\JWT\Signer\Rsa\Sha256::class,
        'RS384' => \Lcobucci\JWT\Signer\Rsa\Sha384::class,
        'RS512' => \Lcobucci\JWT\Signer\Rsa\Sha512::class,
        'HS256' => \Lcobucci\JWT\Signer\Hmac\Sha256::class,
        'HS384' => \Lcobucci\JWT\Signer\Hmac\Sha384::class,
        'HS512' => \Lcobucci\JWT\Signer\Hmac\Sha512::class,
        'ES256' => \Lcobucci\JWT\Signer\Ecdsa\Sha256::class,
        'ES384' => \Lcobucci\JWT\Signer\Ecdsa\Sha384::class,
        'ES512' => \Lcobucci\JWT\Signer\Ecdsa\Sha512::class,
    ];

    private const SYMMETRIC_ALGS = ['HS256', 'HS384', 'HS512'];

    /**
     * Policier constructor
     *
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->config = $config;

        if (!isset($this->algs[$this->config['alg']])) {
            throw new Exception\AlgorithmNotFoundException(
                $this->config['alg'] . ': Algorithm not found'
            );
        }
    }

    /**
     * Get (and lazily build) the lcobucci/jwt configuration container
     */
    private function jwtConfig(): Configuration
    {
        if ($this->jwtConfig !== null) {
            return $this->jwtConfig;
        }

        $signer = new $this->algs[$this->config['alg']]();
        $signKey = $this->getKey();

        if (in_array($this->config['alg'], self::SYMMETRIC_ALGS, true)) {
            $this->jwtConfig = Configuration::forSymmetricSigner(
                $signer,
                InMemory::plainText($signKey)
            );

            return $this->jwtConfig;
        }

        $verifyKey = $this->config['verifykey'] ?? $signKey;

        $this->jwtConfig = Configuration::forAsymmetricSigner(
            $signer,
            InMemory::plainText($signKey),
            InMemory::plainText($verifyKey)
        );

        return $this->jwtConfig;
    }

    /**
     * Configuration
     *
     * @param array $config
     * @return Policier
     */
    public static function configure(array $config): Policier
    {
        if (static::$instance === null) {
            static::$instance = new static($config);
        }

        return static::$instance;
    }

    /**
     * Get instance
     *
     * @return Policier|null
     */
    public static function getInstance(): ?Policier
    {
        return static::$instance;
    }

    /**
     * Plug token
     *
     * @deprecated 3.x
     * @param string $token
     * @return void
     */
    public function plug(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Use the token for working on
     *
     * @param string $token
     * @return void
     */
    public function useToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Get plug token
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Get parsed token
     *
     * @return EncodedToken
     */
    public function getParsedToken(): EncodedToken
    {
        return $this->parse($this->token);
    }

    /**
     * Get decode token
     *
     * @return EncodedToken
     */
    public function getDecodeToken(): EncodedToken
    {
        return $this->decode($this->token);
    }

    /**
     * Get the key
     *
     * @return string
     */
    public function getKey(): string
    {
        $keystring = $this->config['signkey'] ?? null;

        if (is_null($keystring)) {
            throw new Exception\InvalidSecretKeyException("You secret key is invalid or not define.");
        }

        return $keystring;
    }

    /**
     * Get signature
     *
     * @return Signer
     */
    public function getSignature(): Signer
    {
        return $this->jwtConfig()->signer();
    }

    /**
     * Update config
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);

        static::$instance = new static($this->config);
    }

    /**
     * Get Config
     *
     * @param string $key
     * @return mixed
     */
    public function getConfig(string $key): mixed
    {
        return $this->config[$key] ?? null;
    }

    /**
     * Create new token
     *
     * @param int|string $id
     * @param array $claims
     * @return EncodedToken
     */
    public function encode(int|string $id, array $claims): EncodedToken
    {
        $now = new DateTimeImmutable();

        $builder = $this->jwtConfig()->builder()
            ->issuedBy($this->config['iss'])
            ->permittedFor($this->config['aud'])
            ->identifiedBy((string) $id)
            ->issuedAt($now)
            ->expiresAt($now->modify('+' . (int) $this->config['exp'] . ' seconds'));

        if (isset($this->config['sub'])) {
            $builder = $builder->relatedTo($this->config['sub']);
        }

        if (isset($this->config['nbf']) && !is_null($this->config['nbf'])) {
            $builder = $builder->canOnlyBeUsedAfter($now->modify('+' . (int) $this->config['nbf'] . ' seconds'));
        }

        foreach ($claims as $key => $value) {
            if (in_array($key, RegisteredClaims::ALL, true)) {
                continue;
            }

            $value = is_array($value) || is_object($value) || $value instanceof \Iterator
                ? json_encode($value)
                : $value;

            $builder = $builder->withClaim($key, $value);
        }

        return new EncodedToken(
            $builder->getToken($this->jwtConfig()->signer(), $this->jwtConfig()->signingKey())
        );
    }

    /**
     * Decode token
     *
     * @param string|null $token
     * @return EncodedToken
     */
    public function decode(?string $token = null): EncodedToken
    {
        return $this->parse($token);
    }

    /**
     * Verify token signature
     *
     * @param string|null $token
     * @return bool
     */
    public function verify(?string $token = null): bool
    {
        $token = $this->normalizeToken($token);

        try {
            $parsed = $this->jwtConfig()->parser()->parse($token);
        } catch (\Throwable) {
            return false;
        }

        $constraint = new SignedWith(
            $this->jwtConfig()->signer(),
            $this->jwtConfig()->verificationKey()
        );

        return $this->jwtConfig()->validator()->validate($parsed, $constraint);
    }

    /**
     * Parse token
     *
     * @param string|null $token
     * @return EncodedToken
     */
    public function parse(?string $token = null): EncodedToken
    {
        $token = $this->normalizeToken($token);
        $parsed = $this->jwtConfig()->parser()->parse($token);

        if (!$parsed instanceof UnencryptedToken) {
            throw new \RuntimeException('Encrypted tokens are not supported.');
        }

        return new EncodedToken($parsed);
    }

    /**
     * Validate token claims
     *
     * @param string $token
     * @param int|string $id
     * @return bool
     */
    public function validate(string $token, int|string $id): bool
    {
        try {
            $parsed = $this->jwtConfig()->parser()->parse($token);
        } catch (\Throwable) {
            return false;
        }

        $constraints = [
            // Without SignedWith the signature is never verified, so a token
            // forged with any key would pass claim validation (CWE-347).
            new SignedWith(
                $this->jwtConfig()->signer(),
                $this->jwtConfig()->verificationKey()
            ),
            new LooseValidAt($this->clock()),
            new IssuedBy($this->config['iss']),
            new PermittedFor($this->config['aud']),
            new IdentifiedBy((string) $id),
        ];

        return $this->jwtConfig()->validator()->validate($parsed, ...$constraints);
    }

    /**
     * Check if token is expired
     *
     * @param string|null $token
     * @return bool
     */
    public function isExpired(?string $token = null): bool
    {
        $token = $this->normalizeToken($token);

        return $this->parse($token)->isExpired();
    }

    /**
     * Verify the signature and expiry, then return the parsed token.
     *
     * Unlike decode()/parse(), which never check the signature, this refuses
     * a token whose signature is invalid or that has expired. Use it whenever
     * you read claims you intend to trust.
     *
     * @param string|null $token
     * @return EncodedToken
     */
    public function authenticate(?string $token = null): EncodedToken
    {
        $token = $this->normalizeToken($token);

        if (!$this->verify($token)) {
            throw new Exception\TokenInvalidException('The token signature is invalid.');
        }

        $parsed = $this->parse($token);

        if ($parsed->isExpired()) {
            throw new Exception\TokenExpiredException('The token is expired.');
        }

        return $parsed;
    }

    /**
     * Check the token was issued by, and is permitted for, the configured
     * issuer/audience. Does not verify the signature on its own.
     *
     * @param string $token
     * @return bool
     */
    public function matchesConfiguredAudience(string $token): bool
    {
        try {
            $parsed = $this->jwtConfig()->parser()->parse($token);
        } catch (\Throwable) {
            return false;
        }

        return $this->jwtConfig()->validator()->validate(
            $parsed,
            new IssuedBy($this->config['iss']),
            new PermittedFor($this->config['aud'])
        );
    }

    /**
     * A PSR clock used by the time-based JWT constraints.
     *
     * @return \Psr\Clock\ClockInterface
     */
    private function clock(): \Psr\Clock\ClockInterface
    {
        return new class implements \Psr\Clock\ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable();
            }
        };
    }

    /**
     * __callStatic
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $policier = static::getInstance();

        if (method_exists($policier, $method)) {
            return call_user_func_array([$policier, $method], $args);
        }

        throw new \BadMethodCallException('Method "' . $method . '" not define');
    }

    /**
     * Normalize the token
     *
     * @param string|null $token
     * @return string
     */
    private function normalizeToken(?string $token): string
    {
        if (is_null($token)) {
            $token = $this->token;
        }

        if (is_null($token)) {
            throw new \InvalidArgumentException("Please set the token from useToken or pass the token");
        }

        return $token;
    }
}
