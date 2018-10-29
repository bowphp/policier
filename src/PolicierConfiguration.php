<?php

namespace Bow\Jwt;

use Bow\Configuration\Configuration;
use Bow\Configuration\Loader as Config;

class PolicierConfiguration extends Configuration
{
    /**
     * @inheritdoc
     */
    public function create(Config $config)
    {
        $policier = (array) $config['policier'];

        $policier = array_merge(
            $policier,
            require __DIR__.'/config/policier.php'
        );

        $config['policier'] = $policier;

        $this->container->bind('jwt', function () use ($policier) {
            return Policier::configure($policier);
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
