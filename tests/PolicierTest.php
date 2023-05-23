<?php

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
        $this->policier = Policier::configure(
            require __DIR__ . '/config.php'
        );
    }

    /**
     * @dataProvider getAlgoProviders
     */
    public function testShouldEncodeData(string $alg)
    {
        $policier = $this->policier->setConfig([
            'alg' => $alg,
        ]);

        $token = $policier->encode(1, [
            'username' => "papac",
            'logged' => true
        ]);

        $this->assertTrue($policier->verify($token));

        $this->assertEquals($token->getHeader('alg'), $alg);
        $this->assertEquals($token->getHeader('typ'), 'JWT');
        $this->assertEquals($token->get('username'), 'papac');
        $this->assertTrue($token->get('logged'));

        $this->writeToFile((string) $token, $alg);
    }

    /**
     * @dataProvider getAlgoProviders
     */
    public function testShouldDecodeData(string $alg)
    {
        $policier = $this->policier->setConfig([
            'alg' => $alg,
        ]);

        $token = $this->readToFile($alg);

        $this->assertTrue($policier->verify($token));

        $token = $policier->decode($token);

        $this->assertEquals($token->getHeader('alg'), $alg);
        $this->assertEquals($token->getHeader('typ'), 'JWT');
    }

    /**
     * @dataProvider getAlgoProviders
     */
    public function testShouldEncodeViaHelper(string $alg)
    {
        $policier = $this->policier->setConfig([
            'alg' => $alg,
        ]);

        $token = $policier->encode(1, [
            'name' => 'policier'
        ]);

        $this->assertInstanceOf(\Policier\Token::class, $token);

        $token = $policier->parse($token);

        $this->assertEquals($token->get('name'), 'policier');

        $this->writeToFile((string) $token, $alg);
    }

    /**
     * @dataProvider getAlgoProviders
     */
    public function testTransformTokenToArray(string $alg)
    {
        $policier = $this->policier->setConfig([
            'alg' => $alg,
        ]);

        $token = $policier->encode(1, [
            'name' => 'policier'
        ]);

        $this->assertInstanceOf(\Policier\Token::class, $token);

        $array = $token->accessToken();

        $this->assertArrayHasKey('access_token', $array);
        $this->assertArrayHasKey('expire_in', $array);
    }

    /**
     * @dataProvider getAlgoProviders
     */
    public function testShouldDecodeViaHelper(string $alg)
    {
        $policier = $this->policier->setConfig([
            'alg' => $alg,
        ]);

        $token = $this->readToFile($alg);
        $this->assertTrue(is_string($token));

        $policier->setConfig([
            'alg' => $alg,
        ]);

        $token = $policier->decode($token);

        $this->assertTrue($token->has('name'));
        $this->assertEquals($token->get('name'), 'policier');
    }

    /**
     * Write Token
     *
     * @param mixed $token
     */
    public function writeToFile(mixed $token, string $alg)
    {
        file_put_contents(sys_get_temp_dir() . '/testing_' . $alg, (string) $token);
    }

    /**
     * Read Token
     *
     * @return string
     */
    public function readToFile(string $alg)
    {
        return trim(file_get_contents(sys_get_temp_dir() . '/testing_' . $alg));
    }

    public function getAlgoProviders()
    {
        return [
            // ["HS256"],
            // ["HS384"],
            // ["HS512"],
            ["RS256"],
            ["RS384"],
            ["RS512"],
            // ["ES256"],
            // ["ES384"],
            // ["ES512"],
        ];
    }
}
