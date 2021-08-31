<?php

namespace Drupal\wmtwig\Twig;

use Drupal\wmtwig\Event\TemplateParameterEvent;
use Drupal\wmtwig\WmTwigEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class Template extends \Twig\Template
{
    protected static EventDispatcherInterface $dispatcher;
    protected static array $dispatched = [];

    public function display(array $context, array $blocks = []): void
    {
        if ($this->env->isDebug()) {
            $source = $this->getSourceContext();
            $name = $source->getName();
            $path = str_replace(DRUPAL_ROOT . '/', '', $source->getPath());

            echo '<!-- TWIG DEBUG -->';
            printf('<!-- Template: %s -->', $name);

            if ($name !== $path) {
                printf('<!-- Path: %s -->', $path);
            }
        }

        foreach ($context as $key => $value) {
            $event = new TemplateParameterEvent($key, $value);
            $this->getDispatcher()->dispatch(
                $event,
                WmTwigEvents::TEMPLATE_PARAMETER
            );

            $context[$key] = $event->getValue();
        }

        parent::display($context, $blocks);
    }

    protected function getDispatcher()
    {
        if (isset(static::$dispatcher)) {
            return static::$dispatcher;
        }

        return static::$dispatcher = \Drupal::service('event_dispatcher');
    }
}
