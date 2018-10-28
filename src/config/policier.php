<?php

return [
    "lifetime" => 3600,
    "nbf" => 60,
    "iat" => 60,
    "iss" => "localhost",
    "aud" => "localhost",
    "typ": "JWT",
    "alg" => "HS256", // HS256, HS384, HS512, RS256, RS384, RS512, ES256, ES384, ES512,
    'signkey' => null,
    "keychain" => [
        "private" => null,
        "public" => null
    ]
];
