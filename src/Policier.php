<?php

namespace Bow\Jwt;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

class Policier
{
    /**
     * @var Policier
     */
    private $config;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var mixed
     */
    private $alg;

    /**
     * @var ValidationData
     */
    private $validator;

    /**
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
     */
    private function __construct(array $config)
    {
        $this->config = $config;

        $this->builder = new Builder;

        if (!isset($this->algs[$this->config['alg']])) {
            throw new Exception\AlgorithmNotFoundException('Algorithm not found');
        }

        $this->alg = new $this->algs[$this->config['alg']];

        $this->validator = new ValidationData;
    }

    /**
     * Configuration
     *
     * @param array $config
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
     * Get the key
     *
     * @return string
     */
    public function getKey($public = false)
    {
        $key = $this->config['signkey'];

        if ($key != null) {
            return $key;
        }

        $alg = $this->config['alg'];

        if (! in_array($alg, ['RS256', 'RS384', 'RS512'])) {
            return $alg;
        }

        $keychain = new Keychain;

        if (!$public) {
            return $keychain->getPrivateKey(
                $this->config['keychain']['private']
            );
        }

        return $keychain->getPublicKey(
            $this->config['keychain']['public']
        );
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
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * Get Config
     *
     * @param string $key
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
     * @return string
     */
    public function encode($id, array $claims)
    {
        $this->builder->setIssuer($this->config['iss']);
        $this->builder->setAudience($this->config['aud']);
        $this->builder->setId($id, true);
        $this->builder->setIssuedAt(time());
        $this->builder->setExpiration(time() + $this->config['exp']);

        if (isset($this->config['nbf']) && !is_null($this->config['nbf'])) {
            $this->builder->setNotBefore(time() + $this->config['nbf']);
        }
        
        foreach ($claims as $key => $value) {
            $value = is_array($value) || is_object($value) ? json_encode($value) : $value;

            $this->builder->set($key, $value);
        }

        $this->builder->sign($this->getSignature(), $this->getKey());

        $token = $this->builder->getToken();

        return (string) $token;
    }

    /**
     * Decode token
     *
     * @param string $token
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
            $claims[$key] = $value;
        }

        return compact('headers', 'claims');
    }

    /**
     * Verify token
     *
     * @param string $token
     * @return bool
     */
    public function verify($token)
    {
        $token = $this->parse((string) $token);

        return $token->verify($this->getSignature(), $this->getKey(true));
    }

    /**
     * Parse token
     *
     * @param string $token
     * @return Token
     */
    public function parse($token)
    {
        return (new Parser)->parse((string) $token);
    }

    /**
     * Validate token
     *
     * @param string $token
     * @param array $claims
     * @return bool
     */
    public function validate($token, $id, array $claims)
    {
        $token = $this->parse($token);

        $this->validator->setIssuer($this->config['iss']);
        $this->validator->setAudience($this->config['aud']);
        $this->validator->setId($id, true);

        foreach ($claims as $key => $value) {
            $this->validator->set($key, $value);
        }

        return $token->validate($this->validator);
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
            return call_user_func_array(
                [$policier, $method],
                $args
            );
        }

        throw new \BadMethodCallException('Method "'.$method.'" not define');
    }
}
