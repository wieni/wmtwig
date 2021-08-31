<?php

namespace Drupal\wmtwig\Event;

use Drupal\Component\EventDispatcher\Event;

class TemplateParameterEvent extends Event
{
    protected string $key;
    protected mixed $value;

    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }
}
