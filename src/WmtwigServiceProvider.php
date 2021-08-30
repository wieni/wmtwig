<?php

namespace Drupal\wmtwig;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

class WmtwigServiceProvider implements ServiceModifierInterface
{
    public function alter(ContainerBuilder $container)
    {
        $container->setParameter(
            'twig.config',
            $container->getParameter('twig.config') +
            [
                'base_template_class' => '\\Drupal\\wmtwig\\Twig\\Template',
            ]
        );
    }
}
