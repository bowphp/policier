<?php

namespace Policier;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Keychain;
use Policier\Token as EncodedToken;

class Policier
{
    /**
     * The configuration
     *
     * @var array
     */
    private $config;

    /**
     * The Lcobucci Token builder
     *
     * @var Builder
     */
    private $builder;

    /**
     * The Lcobucci Token Parser
     *
     * @var Parser
     */
    private $parser;

    /**
     * The algorithm defined
     *
     * @var mixed
     */
    private $alg;

    /**
     * The Data validation instance
     *
     * @var ValidationData
     */
    private $validator;

    /**
     * The current Token
     *
     * @var string
     */
    private $token;

    /**
     * The Policier instance
     *
     * @var Policier
     */
    private static $instance;

    /**
     * Key list
     *
     * @var array
     */
    private $algs = [
        'RS256' => \Lcobucci\JWT\Signer\Rsa\Sha256::class,
        'RS384' => \Lcobucci\JWT\Signer\Rsa\Sha384::class,
        'RS512' => \Lcobucci\JWT\Signer\Rsa\Sha512::class,
        'HS256' => \Lcobucci\JWT\Signer\Hmac\Sha256::class,
        'HS384' => \Lcobucci\JWT\Signer\Hmac\Sha384::class,
        'HS512' => \Lcobucci\JWT\Signer\Hmac\Sha512::class,
        'ES256' => \Lcobucci\JWT\Signer\Ecdsa\Sha256::class,
        'ES384' => \Lcobucci\JWT\Signer\Ecdsa\Sha384::class,
        'ES512' => \Lcobucci\JWT\Signer\Ecdsa\Sha512::class
    ];

    /**
     * Policier constructor
     *
     * @param array $config
     *
     * @return void
     */
    private function __construct(array $config)
    {
        $this->config = $config;
        $this->builder = new Builder();
        $this->parser = new Parser();

        if (!isset($this->algs[$this->config['alg']])) {
            throw new Exception\AlgorithmNotFoundException(
                $this->config['alg'] . ': Algorithm not found'
            );
        }

        $this->alg = new $this->algs[$this->config['alg']]();

        $this->validator = new ValidationData();
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
     * @return Policier
     */
    public static function getInstance()
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
    public function plug(string $token)
    {
        $this->token = $token;
    }

    /**
     * Use the token for working on
     *
     * @param string $token
     * @return void
     */
    public function useToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get plug token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get parsed token
     *
     * @return Token
     */
    public function getParsedToken()
    {
        return $this->parse($this->token);
    }

    /**
     * Get decode token
     *
     * @return array
     */
    public function getDecodeToken()
    {
        return $this->decode($this->token);
    }

    /**
     * Get the key
     *
     * @return string|Key
     */
    public function getKey(): string|Key
    {
        $signkey = $this->config['signkey'];

        if (is_null($signkey)) {
            throw new Exception\InvalidSecretKeyException(
                "You secret key is invalid or not define."
            );
        }

        if (!in_array($this->config["alg"], ['RS256', 'RS384', 'RS512'])) {
            return $signkey;
        }

        if (!isset($this->config["rsa_key"])) {
            throw new Exception\InvalidSecretKeyException(
                "RSA pem file or content is not provider."
            );
        }

        $key_content = $this->config["rsa_key"];

        if (is_file($key_content)) {
            $key_content = file_get_contents(
                $key_content
            );
        }

        return new Key($key_content, $this->config["rsa_passphrase"] ?? null);
    }

    /**
     * Get signature
     *
     * @return mixed
     */
    public function getSignature()
    {
        return $this->alg;
    }

    /**
     * Update config
     *
     * @param array $config
     * @return Policier
     */
    public function setConfig(array $config): Policier
    {
        $this->config = array_merge($this->config, $config);

        static::$instance = new static($this->config);

        return static::$instance;
    }

    /**
     * Get Config
     *
     * @param string $key
     * @return mixed
     */
    public function getConfig(string $key)
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
        $this->builder->unsign();
        $this->builder->setIssuer($this->config['iss']);
        $this->builder->setAudience($this->config['aud']);
        $this->builder->setId(is_null($id) ? md5(uniqid() . '-' . time()) : $id, true);
        $this->builder->setIssuedAt(time());
        $this->builder->setExpiration(time() + $this->config['exp']);

        if (isset($this->config['sub'])) {
            $this->builder->setSubject($this->config['sub']);
        }

        if (isset($this->config['nbf']) && !is_null($this->config['nbf'])) {
            $this->builder->setNotBefore(time() + $this->config['nbf']);
        }

        // Bind claim information before encoding
        foreach ($claims as $key => $value) {
            $value = is_array($value) || is_object($value) || $value instanceof \Iterator
                ? json_encode($value)
                : $value;

            $this->builder->set($key, $value);
        }

        // Make signature
        $this->builder->sign($this->getSignature(), $this->getKey());

        return new EncodedToken(
            $this->builder->getToken()
        );
    }

    /**
     * Decode token
     *
     * @param ?string $token
     * @return EncodedToken
     */
    public function decode(?string $token = null): EncodedToken
    {
        $token = $this->normalizeToken($token);

        return new EncodedToken(
            $this->parser->parse($token)
        );
    }

    /**
     * Verify token
     *
     * @param ?string $token
     * @return bool
     */
    public function verify(?string $token = null): bool
    {
        $token = $this->normalizeToken($token);

        return $this->parser->parse($token)->verify(
            $this->getSignature(),
            $this->getKey()
        );
    }

    /**
     * Parse token
     *
     * @param string $token
     * @return EncodedToken
     */
    public function parse(?string $token = null): EncodedToken
    {
        $token = $this->normalizeToken($token);

        return new EncodedToken(
            $this->parser->parse($token)
        );
    }

    /**
     * Validate token
     *
     * @param string $token
     * @param int|string $id
     * @return bool
     */
    public function validate(string $token, int|string $id): bool
    {
        $token = $this->parser->parse($token);

        $this->validator->setIssuer($this->config['iss']);
        $this->validator->setAudience($this->config['aud']);
        $this->validator->setId($id, true);

        return $token->validate($this->validator);
    }

    /**
     * Check if token is expired
     *
     * @param string $token
     * @return bool
     */
    public function isExpired(?string $token = null)
    {
        $token = $this->normalizeToken($token);

        return $this->parse($token)->isExpired();
    }

    /**
     * __callStatic
     *
     * @param string $method
     * @param array $args
     *
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
