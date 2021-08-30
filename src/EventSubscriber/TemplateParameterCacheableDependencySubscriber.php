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
    /** @var CacheableMetadata */
    protected $metadata;

    public function __construct(
        RendererInterface $renderer
    ) {
        $this->renderer = $renderer;
        $this->metadata = new CacheableMetadata();
    }

    public static function getSubscribedEvents(): array
    {
        $events[WmTwigEvents::TEMPLATE_PARAMETER][] = ['onTemplateParameter'];

        return $events;
    }

    public function onTemplateParameter(TemplateParameterEvent $event): void
    {
        $value = $event->getValue();

        if (is_array($value) && reset($value) instanceof CacheableDependencyInterface) {
            foreach ($value as $subValue) {
                if ($subValue instanceof CacheableDependencyInterface) {
                    $this->metadata->addCacheableDependency($subValue);
                }
            }
        }

        if ($value instanceof CacheableDependencyInterface) {
            $this->metadata->addCacheableDependency($value);
        }
    }

    public function getCacheableMetadata(): CacheableMetadata
    {
        return $this->metadata;
    }
}
