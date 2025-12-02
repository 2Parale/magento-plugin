<?php

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

// Only register if the Magento framework is actually present
if (class_exists(ComponentRegistrar::class)) {
    ComponentRegistrar::register(
        ComponentRegistrar::MODULE,
        'TwoPerformant_BusinessLeagueMarketing',
        __DIR__
    );
}
