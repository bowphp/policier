<?php

use PHPUnit\Framework\Attributes\Depends;
use Policier\Policier;

class PolicierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Policier
     */
    private $policier;

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

    #[Depends('testShouldEncodeData')]
    public function testShouldDecodeData()
    {
        $token = $this->readToFile();

        $this->assertTrue($this->policier->verify($token));

        $token = $this->policier->decode($token);

        $this->assertEquals($token->getHeader('alg'), 'HS512');
        $this->assertEquals($token->getHeader('typ'), 'JWT');
    }

    #[Depends('testShouldDecodeData')]
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

    #[Depends('testShouldDecodeData')]
    public function testTransformTokenToArray()
    {
        $token = policier('encode', 1, [
            'name' => 'policier'
        ]);

        $this->assertInstanceOf(\Policier\Token::class, $token);

        $array = $token->accessToken();

        $this->assertArrayHasKey('access_token', $array);
        $this->assertArrayHasKey('expire_in', $array);
    }

    #[Depends('testShouldEncodeData')]
    public function testShouldDecodeViaHelper()
    {
        $token = $this->readToFile();

        $this->assertTrue(is_string($token));

        $token = policier('decode', $token);

        $this->assertTrue($token->has('name'));
        $this->assertEquals($token->get('name'), 'policier');
    }

    /**
     * A token forged with an attacker's own key must be rejected by the
     * signature check and by validate()/authenticate() (CWE-347 regression).
     */
    public function testValidateRejectsForgedSignature()
    {
        $evil = \Lcobucci\JWT\Configuration::forSymmetricSigner(
            new \Lcobucci\JWT\Signer\Hmac\Sha512(),
            \Lcobucci\JWT\Signer\Key\InMemory::plainText(str_repeat('a', 64))
        );

        $now = new \DateTimeImmutable();
        $forged = $evil->builder()
            ->issuedBy('localhost')
            ->permittedFor('localhost')
            ->identifiedBy('42')
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->getToken($evil->signer(), $evil->signingKey())
            ->toString();

        $this->assertFalse($this->policier->verify($forged));
        $this->assertFalse($this->policier->validate($forged, 42));

        $this->expectException(\Policier\Exception\TokenInvalidException::class);
        $this->policier->authenticate($forged);
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
