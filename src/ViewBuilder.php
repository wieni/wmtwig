<?php

namespace Drupal\wmtwig;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\CacheableResponseTrait;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Render\AttachmentsTrait;
use Drupal\Core\Render\MainContent\MainContentRendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ViewBuilder implements AttachmentsInterface, CacheableResponseInterface
{
    use AttachmentsTrait;
    use CacheableResponseTrait;

    protected MainContentRendererInterface $renderer;
    protected RequestStack $requestStack;
    protected RouteMatchInterface $routeMatch;

    protected ?string $templateDir = null;
    protected ?string $template = null;
    protected array $data = [];

    public function __construct(
        MainContentRendererInterface $renderer,
        RequestStack $requestStack,
        RouteMatchInterface $routeMatch
    ) {
        $this->renderer = $renderer;
        $this->requestStack = $requestStack;
        $this->routeMatch = $routeMatch;
    }

    public function setTemplateDir(?string $templateDir): self
    {
        $this->templateDir = $templateDir;

        return $this;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Set the data passed to the view
     * Has to be an associative array
     *
     * When passed [myVariable => 'I am a teapot'], the view will
     * have access to the variable 'myVariable'
     *
     * @see wmcontroller_theme_set_variables
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function addCacheContext(string $context): self
    {
        $this->getCacheableMetadata()->addCacheContexts([$context]);

        return $this;
    }

    public function addCacheContexts(array $contexts): self
    {
        $this->getCacheableMetadata()->addCacheContexts($contexts);

        return $this;
    }

    public function addCacheTag($tag): self
    {
        $this->getCacheableMetadata()->addCacheTags([$tag]);

        return $this;
    }

    public function addCacheTags(array $tags): self
    {
        $this->getCacheableMetadata()->addCacheTags($tags);

        return $this;
    }

    public function toRenderArray(): array
    {
        $view = [];
        $view['#_data'] = $this->data;
        $view['#attached'] = $this->attachments;

        if ($this->template) {
            $view['#theme']
                = ($this->templateDir ? $this->templateDir . '.' : '')
                . $this->template;
        }

        $this->getCacheableMetadata()->applyTo($view);

        return $view;
    }

    public function toResponse(): Response
    {
        return $this->renderer->renderResponse(
            $this->toRenderArray(),
            $this->requestStack->getCurrentRequest(),
            $this->routeMatch
        );
    }
}
