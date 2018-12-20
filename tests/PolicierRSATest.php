<?php

use Policier\Policier;

class PolicierRSATest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Policier
     */
    private $policier;

    /**
     * The token information
     *
     * @var string
     */
    private $token;

    /**
     * On setUp
     */
    public function setUp()
    {
        $config = require __DIR__.'/../config/policier.php';

        $policier = Policier::configure($config);

        $policier->setConfig([
            'singkey' => null,
            'alg' => 'RS512',
            'keychain' => [
                'private' => trim(file_get_contents(__DIR__.'/seeds/rsa/private.key')),
                'public' => trim(file_get_contents(__DIR__.'/seeds/rsa/public.key')),
            ],
        ]);

        $this->policier = Policier::getInstance();
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

        $this->assertEquals($token->getHeader('alg'), 'RS512');

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

        $this->assertEquals($token['headers']['alg'], 'RS512');

        $this->assertEquals($token['headers']['typ'], 'JWT');
    }

    /**
     * @depends testDecode
     */
    public function testHelperEncode()
    {
        $token = policier('encode', $id = 1, [
            'name' => 'policier'
        ]);

        $this->assertInstanceOf(\Policier\Token::class, $token);

        $token = policier('parse', $token);

        $this->assertEquals($token->getClaim('name'), 'policier');

        $this->writeToFile($token);
    }

    /**
     * @depends testHelperEncode
     */
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
