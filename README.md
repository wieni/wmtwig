Wieni Twig
======================

[![Latest Stable Version](https://poser.pugx.org/wieni/wmtwig/v/stable)](https://packagist.org/packages/wieni/wmtwig)
[![Total Downloads](https://poser.pugx.org/wieni/wmtwig/downloads)](https://packagist.org/packages/wieni/wmtwig)
[![License](https://poser.pugx.org/wieni/wmtwig/license)](https://packagist.org/packages/wieni/wmtwig)

> Improves the integration of Twig with component and entity-oriented projects.

## Why?
_TODO_

## Installation

This package requires PHP 7.1 and Drupal 8 or higher. It can be
installed using Composer:

```bash
 composer require wieni/wmtwig
```

## How does it work?
### Registering Twig templates as theme implementations
This module automatically registers your Twig templates as theme implementations. Modules and themes can indicate in 
their info.yml file which paths should be included:

```yaml
name: Some theme
type: theme

wmtwig:
    templates: pages
```

If the module or theme has multiple paths, an array can be passed:

```yaml
name: Some theme
type: theme

wmtwig:
    templates:
        - components
        - pages
```

The advantage against the default way of rendering Twig templates
([see documentation](https://www.drupal.org/docs/theming-drupal/twig-in-drupal/create-custom-twig-templates-for-custom-module))
is that you don't have to manually define every template in a theme hook. Also, the folder structure of your templates is respected. This makes it possible to refer to templates in the same
way as in Laravel projects, eg. `node.product` for the `node/product.html.twig` template.

It is recommended to not use the default `templates` folder for these templates, because they might clash with other 
Drupal theme implementations. Eg. when creating a template at path `templates/node/page.html.twig`, it will override 
the core page template.

## Changelog
All notable changes to this project will be documented in the
[CHANGELOG](CHANGELOG.md) file.

## Security
If you discover any security-related issues, please email
[security@wieni.be](mailto:security@wieni.be) instead of using the issue
tracker.

## License
Distributed under the MIT License. See the [LICENSE](LICENSE) file
for more information.
