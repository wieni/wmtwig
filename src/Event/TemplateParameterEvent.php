<?php

namespace Drupal\wmtwig\Event;

use Symfony\Component\EventDispatcher\Event;

class TemplateParameterEvent extends Event
{
    /** @var string */
    protected $key;
    /** @var mixed */
    protected $value;

    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /** @return string */
    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
