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

        $this->writeToFile($this->token);
    }

    /**
     * @depends testEncode
     */
    public function testDecode()
    {
        $token = $this->readToFile();

        $this->assertTrue($this->policier->verify($token));

        $token = $this->policier->decode($token);

        $this->assertEquals($token['headers']['alg'], 'HS512');

        $this->assertEquals($token['headers']['typ'], 'JWT');
    }

    public function testHelperEncode()
    {
        $token = policier('encode', $id = 1, [
            'name' => 'policier'
        ]);

        $this->assertTrue(is_string($token));

        $token = policier('parse', $token);

        $this->assertEquals($token->getClaim('name'), 'policier');

        $this->writeToFile($token);
    }

    public function testHelperDecode()
    {
        $token = $this->readToFile();

        $this->assertTrue(is_string($token));

        $token = policier('decode', $token);

        $this->assertEquals($token['claims']['name'], 'policier');
    }

    /**
     * Write Token
     *
     * @param mixed $token
     */
    public function writeToFile($token)
    {
        file_put_contents(sys_get_temp_dir().'/testing', (string) $token);
    }

    /**
     * Write Token
     *
     * @return string
     */
    public function readToFile()
    {
        return trim(file_get_contents(sys_get_temp_dir().'/testing'));
    }
}
