<?php

use Bow\Jwt\Policier;

class PolicierTest extends \PHPUnit\Framework\TestCase
{
    private $policier;

    private $token;

    public function setUp()
    {
        $config = require __DIR__.'/../src/config/policier.php';

        $config['signkey'] = base64_encode('testing');

        $this->policier = Policier::configure($config);
    }

    public function testEncode()
    {
        $id = 1;
        
        $this->token = $this->policier->encode($id, [
            'username' => "papac",
            'logged' => true
        ]);

        $this->assertTrue($this->policier->verify($this->token));

        $token = $this->policier->parse($this->token);

        $this->assertEquals($token->getHeader('alg'), 'HS512');

        $this->assertEquals($token->getHeader('typ'), 'JWT');

        $this->assertEquals($token->getClaim('username'), 'papac');

        $this->assertTrue($token->getClaim('logged'));

        
        $this->assertTrue($this->policier->verify($this->token));

        $token = $this->policier->decode($this->token);

        $this->assertEquals($token['headers']['alg'], 'HS512');

        $this->assertEquals($token['headers']['typ'], 'JWT');
    }
}
