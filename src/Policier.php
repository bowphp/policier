<?php

namespace Policier;

use Policier\Token as EncodedToken;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

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
     * The algorithm defined
     *
     * @var mixed
     */
    private $alg;

    /**
     * The RSA and ECDSA key loader
     *
     * @var mixed
     */
    private $keychain;

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

        if (!isset($this->algs[$this->config['alg']])) {
            throw new Exception\AlgorithmNotFoundException(
                $this->config['alg'] . ': Algorithm not found'
            );
        }

        $this->alg = new $this->algs[$this->config['alg']]();

        $this->keychain = new Keychain();

        $this->validator = new ValidationData();
    }

    /**
     * Configuration
     *
     * @param array $config
     *
     * @return void
     */
    public static function configure(array $config)
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
     * @param string $token
     *
     * @return void
     */
    public function plug($token)
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
     * @return string
     */
    public function getKey($public = false)
    {
        $alg = $this->config['alg'];

        if (! in_array($alg, ['RS256', 'RS384', 'RS512', 'ES256', 'ES384', 'ES512'])) {
            $keystring = $this->config['signkey'];

            if (is_null($keystring)) {
                throw new Exception\InvalidSecretKeyException("You secret key is invalid.");
            }

            return $this->config['signkey'];
        }

        if (!$public) {
            return $this->keychain->getPrivateKey(
                $this->getKeyContents($this->config['keychain']['private'])
            );
        }

        return $this->keychain->getPublicKey(
            $this->getKeyContents($this->config['keychain']['public'])
        );
    }

    /**
     * Get key content
     *
     * @param string $key
     *
     * @return string
     */
    private function getKeyContents($key)
    {
        if (is_file($key)) {
            return file_get_contents($key);
        }

        if (is_null($key)) {
            throw new Exception\InvalidSecretKeyException("The special your keys path.");
        }

        return $key;
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
     *
     * @return mixed
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);

        static::$instance = new static($this->config);
    }

    /**
     * Get Config
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->config[$key] ?? null;
    }

    /**
     * Create new token
     *
     * @param mixed $id
     * @param array $claims
     *
     * @return string
     */
    public function encode($id, array $claims)
    {
        $this->builder->unsign();
        $this->builder->setIssuer($this->config['iss']);
        $this->builder->setAudience($this->config['aud']);
        $this->builder->setId($id, true);
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
            $value = is_array($value) || is_object($value) ? json_encode($value) : $value;

            $this->builder->set($key, $value);
        }

        // Make signature
        $this->builder->sign($this->getSignature(), $this->getKey());

        $token = $this->builder->getToken();

        return new EncodedToken(
            (string) $token,
            $token->getClaim('exp')
        );
    }

    /**
     * Decode token
     *
     * @param string $token
     *
     * @return array
     */
    public function decode($token)
    {
        $token = $this->parse($token);

        $headers = [];

        $claims = [];

        foreach ($token->getHeaders() as $key => $value) {
            $headers[$key] = $value;
        }

        foreach ($token->getClaims() as $key => $value) {
            $claims[$key] = (string) $value;
        }

        return compact('headers', 'claims');
    }

    /**
     * Verify token
     *
     * @param string $token
     *
     * @return bool
     */
    public function verify($token)
    {
        $token = $this->parse((string) $token);

        return $token->verify(
            $this->getSignature(),
            $this->getKey(true)
        );
    }

    /**
     * Parse token
     *
     * @param string $token
     *
     * @return Token
     */
    public function parse($token)
    {
        return (new Parser())
            ->parse((string) $token);
    }

    /**
     * Validate token
     *
     * @param string $token
     * @param array $claims
     *
     * @return bool
     */
    public function validate($token, $id, array $claims)
    {
        $token = $this->parse($token);

        $this->validator->setIssuer($this->config['iss']);
        $this->validator->setAudience($this->config['aud']);
        $this->validator->setId($id, true);

        foreach ($claims as $key => $value) {
            $this->validator
                ->set($key, $value);
        }

        return $token->validate(
            $this->validator
        );
    }

    /**
     * Check if token is expired
     *
     * @param string $token
     *
     * @return bool
     */
    public function isExpired($token)
    {
        $token = $this->parse($token);

        return $token->isExpired();
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
            return call_user_func_array(
                [$policier, $method],
                $args
            );
        }

        throw new \BadMethodCallException('Method "' . $method . '" not define');
    }
}
