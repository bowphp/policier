<?php

namespace Policier\Jwt\Bow;

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
            require __DIR__.'/../../config/policier.php',
            $policier
        );

        $config['policier'] = $policier;

        $this->container->bind('policier', function () use ($policier, $config) {
            $name = isset($policier['middleware_name']) ? $policier['middleware_name'] : 'api';

            $config->pushMiddleware([
                $name => PolicierMiddleware::class
            ]);
    
            return Policier::configure($policier);
        });
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->container->make('policier');
    }
}
