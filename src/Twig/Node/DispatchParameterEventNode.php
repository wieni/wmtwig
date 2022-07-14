<?php

namespace Drupal\wmtwig\Twig\Node;

use Twig\Compiler;
use Twig\Node\Node;

class DispatchParameterEventNode extends Node
{
    public function compile(Compiler $compiler)
    {
        parent::compile($compiler);

        $compiler->raw('
            foreach ($context as $key => $value) {
                $event = new \Drupal\wmtwig\Event\TemplateParameterEvent($key, $value);
                $this->getDispatcher()->dispatch(
                    $event,
                    \Drupal\wmtwig\WmTwigEvents::TEMPLATE_PARAMETER
                );
    
                $context[$key] = $event->getValue();
            }
        ');
    }
}
