<?php

namespace Drupal\wmtwig;

interface TemplateLocatorInterface
{
    /** Get all custom themes */
    public function getThemes(): array;
}
