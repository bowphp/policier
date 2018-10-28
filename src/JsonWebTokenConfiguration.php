<?php

namespace Bow\JWT;

use Bow\Configuration\Configuration;
use Bow\Configuration\Loader as Config;

class JsonWebTokenConfiguration extends Configuration
{
	/**
	 * @inheritdoc
	 */
	public function create(Config $config)
	{
		$this->container->bind('jwt', function () {

		});
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		$this->container->make('jwt');
	}
}