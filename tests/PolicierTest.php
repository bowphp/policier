<?php

use Policier\Policier;

class PolicierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Policier
     */
    private $policier;

    /**
     * The id information
     *
     * @var int
     */
    private $id;

    /**
     * On setUp
     */
    public function setUp(): void
    {
        $policier = Policier::configure(
            require __DIR__ . '/../config/policier.php'
        );

        $policier->setConfig([
            'alg' => 'HS512',
            'signkey' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlFdP9pwuj6lYndTuUFO6",
        ]);

        $this->policier = Policier::getInstance();
    }

    public function testShouldEncodeData()
    {
        $token = $this->policier->encode(1, [
            'username' => "papac",
            'logged' => true
        ]);

        $this->assertTrue($this->policier->verify($token));

        $this->assertEquals($token->getHeader('alg'), 'HS512');
        $this->assertEquals($token->getHeader('typ'), 'JWT');
        $this->assertEquals($token->get('username'), 'papac');
        $this->assertTrue($token->get('logged'));

        $this->writeToFile((string) $token);
    }

    /**
     * @depends testShouldEncodeData
     */
    public function testShouldDecodeData()
    {
        $token = $this->readToFile();

        $this->assertTrue($this->policier->verify($token));

        $token = $this->policier->decode($token);

        $this->assertEquals($token->getHeader('alg'), 'HS512');
        $this->assertEquals($token->getHeader('typ'), 'JWT');
    }

    /**
     * @depends testShouldDecodeData
     */
    public function testShouldEncodeViaHelper()
    {
        $token = policier('encode', 1, [
            'name' => 'policier'
        ]);

        $this->assertInstanceOf(\Policier\Token::class, $token);

        $token = policier('parse', $token);

        $this->assertEquals($token->get('name'), 'policier');

        $this->writeToFile((string) $token);
    }

    /**
     * @depends testShouldDecodeData
     */
    public function testTransformTokenToArray()
    {
        $token = policier('encode', 1, [
            'name' => 'policier'
        ]);

        $this->assertInstanceOf(\Policier\Token::class, $token);

        $array = $token->toArray();

        $this->assertArrayHasKey('access_token', $array);
        $this->assertArrayHasKey('expire_in', $array);
    }

    /**
     * @depends testShouldEncodeData
     */
    public function testShouldDecodeViaHelper()
    {
        $token = $this->readToFile();

        $this->assertTrue(is_string($token));

        $token = policier('decode', $token);

        $this->assertEquals($token->get('name'), 'policier');
    }

    /**
     * Write Token
     *
     * @param mixed $token
     */
    public function writeToFile($token)
    {
        file_put_contents(sys_get_temp_dir() . '/testing', (string) $token);
    }

    /**
     * Write Token
     *
     * @return string
     */
    public function readToFile()
    {
        return trim(file_get_contents(sys_get_temp_dir() . '/testing'));
    }
}
