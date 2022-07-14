<?php

namespace Drupal\wmtwig\Twig;

use Drupal\wmtwig\Twig\NodeVisitor\DebugNodeVisitor;
use Drupal\wmtwig\Twig\NodeVisitor\DispatchParameterEventNodeVisitor;
use Twig\Extension\AbstractExtension;

class Extension extends AbstractExtension
{
    public function getNodeVisitors(): array
    {
        return [
            new DebugNodeVisitor(),
            new DispatchParameterEventNodeVisitor(),
        ];
    }
}
