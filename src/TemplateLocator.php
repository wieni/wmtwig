<?php

namespace Drupal\wmtwig;

use Drupal\Core\Extension\ThemeHandlerInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class TemplateLocator implements TemplateLocatorInterface
{
    public const TWIG_EXT = '.html.twig';

    /** @var ThemeHandlerInterface */
    protected $themeHandler;
    /** @var array */
    protected $settings;

    public function __construct(
        ThemeHandlerInterface $themeHandler,
        array $settings
    ) {
        if (empty($settings['module'])) {
            throw new \Exception(
                'wmtwig requires a non-empty module entry in wmtwig.settings'
            );
        }

        if (empty($settings['path'])) {
            $settings['path'] = 'templates';
        }

        $this->themeHandler = $themeHandler;
        $this->settings = $settings;
    }

    public function getThemes(): array
    {
        if (empty($this->settings['theme'])) {
            return $this->getThemeFiles('module', $this->settings['module']);
        }

        $allThemes = $this->themeHandler->listInfo();
        $activeTheme = $this->settings['theme'];

        $themes = array_keys($this->themeHandler->getBaseThemes($allThemes, $activeTheme));
        $themes[] = $activeTheme;

        $templates = [];
        foreach ($themes as $theme) {
            if (!isset($allThemes[$theme])) {
                continue;
            }

            $templates = array_merge($templates, $this->getThemeFiles('theme', $theme));
        }

        return $templates;
    }

    /**
     * Locate and create theme arrays in a module
     *
     * @param $type
     *   module or theme
     * @param $location
     *   directory in that module or theme
     */
    protected function getThemeFiles(string $type, string $location): array
    {
        $themes = [];
        $dir = drupal_get_path($type, $location)
            . DIRECTORY_SEPARATOR
            . $this->settings['path'];

        if (!file_exists($dir)) {
            return $themes;
        }

        $files = $this->findTwigFiles($dir);

        foreach ($files as $file) {
            $fileName = $this->stripOutTemplatePathAndExtension($dir, $file);
            // Transform the filename to a template name
            // node/article/index.html.twig => node.article.index
            $templateName = preg_replace('/\/|\\\/', '.', $fileName);
            $themes[$templateName] = [
                'variables' => [
                    '_data' => [],
                ],
                'path' => $dir,
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
            RecursiveRegexIterator::GET_MATCH
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
