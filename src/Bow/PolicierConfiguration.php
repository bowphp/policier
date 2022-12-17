<?php

namespace Policier\Bow;

use Bow\Configuration\Configuration;
use Bow\Configuration\Loader as Config;
use Policier\Policier;

class PolicierConfiguration extends Configuration
{
    /**
     * @inheritdoc
     */
    public function create(Config $config): void
    {
        $policier = (array) $config['policier'];

        $policier = array_merge(
            require __DIR__ . '/../../config/policier.php',
            $policier
        );

        $config['policier'] = $policier;

        $this->container->bind('policier', function () use ($policier, $config) {
            if (is_null($policier['signkey']) && is_null($policier['keychain']['private'])) {
                $policier['signkey'] = file_get_contents($config['security.key']);
            }

            return Policier::configure($policier);
        });
    }

    /**
     * @inheritdoc
     */
    public function run(): void
    {
        $this->container->make('policier');
    }
}
