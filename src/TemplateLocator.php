<?php

namespace Drupal\wmtwig;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Theme\ThemeManager;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class TemplateLocator implements TemplateLocatorInterface
{
    public const TWIG_EXT = '.html.twig';

    /** @var ModuleExtensionList */
    protected $moduleList;
    /** @var ThemeExtensionList */
    protected $themeList;

    public function __construct(
        ModuleExtensionList $moduleList,
        ThemeExtensionList $themeList
    ) {
        $this->moduleList = $moduleList;
        $this->themeList = $themeList;
    }

    public function getThemes(): array
    {
        $templates = [];
        $extensions = [
            'module' => $this->moduleList->getAllInstalledInfo(),
            'theme' => $this->themeList->getAllInstalledInfo(),
        ];

        foreach ($extensions as $type => $extensionsByType) {
            foreach ($extensionsByType as $name => $info) {
                $templatePaths = $info['wmtwig']['templates'] ?? [];

                if (is_string($templatePaths)) {
                    $templatePaths = [$templatePaths];
                }

                foreach ($templatePaths as $templatePath) {
                    $templates = array_merge($templates, $this->getThemeDefinitions($type, $name, $templatePath));
                }
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
    protected function getThemeDefinitions(string $type, string $name, string $templatePath): array
    {
        $themes = [];
        $path = null;

        if ($type === 'module') {
            $path = $this->moduleList->getPath($name) . DIRECTORY_SEPARATOR . $templatePath;
        }

        if ($type === 'theme') {
            $path = $this->themeList->getPath($name) . DIRECTORY_SEPARATOR . $templatePath;
        }

        if ($path === null || !file_exists($path)) {
            return $themes;
        }

        $files = $this->findTwigFiles($path);
        $preprocessFunctions = $this->getPreprocessFunctions();

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
                'preprocess functions' => $preprocessFunctions,
            ];
        }

        return $themes;
    }

    /**
     * Get the preprocess functions of the theme hooks defined by wmtwig
     *
     * Since these theme hooks define their own preprocess functions, core does
     * not add its default ones. Before Drupal 11.2, template_preprocess() has to
     * be added explicitly so the default template variables (attributes,
     * directory, logged_in, ...) are set before the other preprocess functions
     * run.
     *
     * Since Drupal 11.2, ThemeManager::render() sets these variables for every
     * theme hook before calling any preprocess function. template_preprocess()
     * is deprecated since then and removed in Drupal 12.
     *
     * @see https://www.drupal.org/node/2340341
     */
    protected function getPreprocessFunctions(): array
    {
        $functions = [];

        if (
            function_exists('template_preprocess')
            && !method_exists(ThemeManager::class, 'getDefaultTemplateVariables')
        ) {
            $functions[] = 'template_preprocess';
        }

        $functions[] = 'wmtwig_theme_set_variables';

        return $functions;
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
