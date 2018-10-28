<?php

use Bow\Jwt\Policier;

class PolicierTest extends \PHPUnit\Framework\TestCase
{
	private $policier;

	public function setUp()
	{
		$this->policier = new Policier(require __DIR__.'/../src/config/policier.php');
	}

	public function testConfigure()
	{
		$token = $this->policier->encode(1, [
			'username' => "papac",
			'logged' => true
		]);

		$this->policier->verify((string) $token);

		$this->policier->parse($token);
	}
}