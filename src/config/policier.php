<?php

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
     * The type of the token, which is JWT
     */
    "typ" => "JWT",

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
