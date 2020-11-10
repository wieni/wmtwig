<?php

namespace Drupal\wmtwig;

final class WmTwigEvents
{
    /**
     * Will be triggered for every parameter passed to a Twig template.
     *
     * The event object is an instance of
     * @see \Drupal\wmtwig\Event\TemplateParameterEvent
     */
    public const TEMPLATE_PARAMETER = 'wmtwig.template.parameter';
}
