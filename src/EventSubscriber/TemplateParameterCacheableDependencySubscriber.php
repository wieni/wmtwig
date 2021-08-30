<?php

namespace Drupal\wmtwig\EventSubscriber;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\RendererInterface;
use Drupal\wmtwig\Event\TemplateParameterEvent;
use Drupal\wmtwig\WmTwigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TemplateParameterCacheableDependencySubscriber implements EventSubscriberInterface
{
    /** @var RendererInterface */
    protected $renderer;

    public function __construct(
        RendererInterface $renderer
    ) {
        $this->renderer = $renderer;
    }

    public static function getSubscribedEvents(): array
    {
        $events[WmTwigEvents::TEMPLATE_PARAMETER][] = ['onTemplateParameter'];

        return $events;
    }

    public function onTemplateParameter(TemplateParameterEvent $event): void
    {
        $value = $event->getValue();
        $metadata = new CacheableMetadata();

        if (is_array($value) && reset($value) instanceof CacheableDependencyInterface) {
            foreach ($value as $subValue) {
                if ($subValue instanceof CacheableDependencyInterface) {
                    $metadata->addCacheableDependency($subValue);
                }
            }
        }

        if ($value instanceof CacheableDependencyInterface) {
            $metadata->addCacheableDependency($value);
        }

        $build = [];
        $metadata->applyTo($build);
        $this->renderer->render($build);
    }
}
