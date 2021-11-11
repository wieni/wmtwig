<?php

namespace Drupal\wmtwig;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class TemplateLocator implements TemplateLocatorInterface
{
    public const TWIG_EXT = '.html.twig';

    /** @var ModuleHandlerInterface */
    protected $moduleHandler;
    /** @var ThemeHandlerInterface */
    protected $themeHandler;

    public function __construct(
        ModuleHandlerInterface $moduleHandler,
        ThemeHandlerInterface $themeHandler
    ) {
        $this->moduleHandler = $moduleHandler;
        $this->themeHandler = $themeHandler;
    }

    public function getThemes(): array
    {
        $templates = [];
        $extensions = array_merge(
            $this->themeHandler->listInfo(),
            $this->moduleHandler->getModuleList()
        );

        foreach ($extensions as $extension) {
            $templatePaths = $extension->info['wmtwig']['templates'] ?? [];

            if (is_string($templatePaths)) {
                $templatePaths = [$templatePaths];
            }

            foreach ($templatePaths as $templatePath) {
                $templates = array_merge($templates, $this->getThemeDefinitions($extension, $templatePath));
            }
        }

        return $templates;
    }

    /**
     * Create theme definitions based on templates present in modules and themes
     *
     * @param Extension $extension
     *   The module or theme
     * @param string $templatePath
     *   The path to the templates folder, relative to the extension root
     */
    protected function getThemeDefinitions(Extension $extension, string $templatePath): array
    {
        $themes = [];
        $path = drupal_get_path($extension->getType(), $extension->getName()) . DIRECTORY_SEPARATOR . $templatePath;

        if (!file_exists($path)) {
            return $themes;
        }

        $files = $this->findTwigFiles($path);

        foreach ($files as $file) {
            $fileName = $this->stripOutTemplatePathAndExtension($path, $file);
            // Transform the filename to a template name
            // node/article/index.html.twig => node.article.index
            $templateName = preg_replace('/\/|\\\/', '.', $fileName);
            $themes[$templateName] = [
                'variables' => [
                    '_data' => [],
                ],
                'path' => $path,
                'template' => $fileName,
                'preprocess functions' => [
                    'template_preprocess',
                    'wmtwig_theme_set_variables',
                ],
            ];
        }

        return $themes;
    }

    /**
     * Find all twig files recursively in a directory
     *
     * @return string[]
     */
    protected function findTwigFiles(string $directory): array
    {
        $fileIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
            )
        );

        $matches = new RegexIterator(
            $fileIterator,
            '#^.*' . preg_quote(static::TWIG_EXT, '#') . '$#',
            RegexIterator::GET_MATCH
        );

        // Weed out non-matches
        $files = [];
        foreach ($matches as $match) {
            if (!empty($match[0])) {
                $files[] = $match[0];
            }
        }

        return $files;
    }

    protected function stripOutTemplatePathAndExtension(string $templatePath, string $file): string
    {
        // Strip out the module path
        $file = str_replace($templatePath . DIRECTORY_SEPARATOR, '', $file);
        // Strip out extension
        return preg_replace(
            '#' . preg_quote(static::TWIG_EXT, '#') . '$#',
            '',
            $file
        );
    }
}
