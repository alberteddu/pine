<?php

namespace Pine;

use Branches\Branches;
use Branches\Extension\Properties\PropertiesExtension;
use Branches\Node\FileInterface;
use Branches\Node\PostInterface;
use Exception;
use Pine\Configuration\Configuration;
use Pine\Plugin\PluginInterface;
use Pine\Plugin\ThemeLocation;
use Pine\Util\Path;
use Pine\Configuration\Settings;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Site
{
    const ENV_DEV = 'dev';
    const ENV_PROD = 'prod';

    /**
     * @var string
     */
    private $env;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var string
     */
    private $path;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ThemeLocation[]
     */
    private $availableThemes;

    /**
     * @var Theme
     */
    private $theme;

    /**
     * @var Branches
     */
    private $branches;

    /**
     * @var PluginInterface[]
     */
    private $plugins;

    /**
     * @param string $directory
     * @param string $env
     *
     * @throws Exception
     */
    public function __construct($directory, $env = self::ENV_DEV)
    {
        $this->plugins = [];
        $this->path = Path::normalizePath($directory);
        $this->env = $env;

        if (!$this->path) {
            throw new Exception(sprintf('Directory "%s" does not exist', $directory));
        }

        $configurationPath = $this->joinPaths(Settings::FILENAME);
        $this->settings = Settings::createFromFileOrDefaults($configurationPath);
        $this->branches = new Branches($this->joinPaths($this->settings->getContentDirectory()));
        $this->branches->useExtension(new PropertiesExtension());

        $this->loadConfig();
        $this->loadPLugins();
        $this->loadThemes();

        $themeName = $this->settings->getTheme();

        if (!isset($this->availableThemes[$themeName])) {
            throw new Exception(sprintf('Theme %s not found', $themeName));
        }

        $this->theme = Theme::createFromThemeLocation($this->availableThemes[$themeName]);

        $this->validate();

        foreach ($this->plugins as $plugin) {
            $plugin->ready();
        }
    }

    public function loadConfig()
    {
        $this->config = new Configuration();
        $this->config->processDirectory($this->joinPaths(Settings::CONFIG_LOCATION), $this->env);
    }

    private function loadPlugins()
    {
        $autoloadPath = $this->joinPaths('vendor/autoload.php');

        if (is_readable($autoloadPath)) {
            require_once $autoloadPath;
        }

        foreach ($this->settings->getPlugins() as $className) {
            if (!class_exists($className)) {
                continue;
            }

            $pluginInstance = new $className;

            if (!$pluginInstance instanceof PluginInterface) {
                continue;
            }

            $this->plugins[] = $pluginInstance;
            $pluginInstance->setUp($this);

            foreach ($pluginInstance->getBranchesExtensions() as $branchesExtension) {
                $this->branches->useExtension($branchesExtension);
            }
        }
    }

    private function loadThemes()
    {
        foreach (glob($this->joinPaths(Settings::THEMES_LOCATION, '*')) as $path) {
            if (!is_dir($path) || !is_readable($path)) {
                continue;
            }

            $themeName = basename($path);

            $this->availableThemes[$themeName] = ThemeLocation::create($themeName, $path);
        }

        foreach ($this->plugins as $plugin) {
            foreach ($plugin->getThemeLocations() as $themeLocation) {
                $this->availableThemes[$themeLocation->getName()] = $themeLocation;
            }
        }
    }

    public function build()
    {
        $this->loadConfig();
        $this->theme->reloadTheme();
        $this->clearBuild();
        /** @var PostInterface $root */
        $root = $this->branches->get();
        $this->buildPost($root);
        $publicSource = joinPaths($this->theme->getDirectory(), Theme::PUBLIC_DIRECTORY);
        $publicDestination = $this->getPathInBuild('theme');
        xcopy($publicSource, $publicDestination);
    }

    public function renderPost(PostInterface $post)
    {
        $layout = $post->getProperty('layout', 'default');

        return $this->theme->renderTemplate($layout, [
            'site' => $this,
            'theme' => $this->theme,
            'post' => $post,
        ]);
    }

    private function buildPost(PostInterface $post)
    {
        $renderedPost = $this->renderPost($post);

        $directory = $this->getPathInBuild($post->getUrl());
        $path = $this->getPathInBuild($post->getUrl(), 'index.html');

        if (!is_dir($directory)) {
            mkdir($directory);
        }

        file_put_contents($path, $renderedPost);

        foreach ($post->getAttachments() as $file) {
            if ($file->getUrl()->getLastSegment() === $post->getProperty('__content_filename')) {
                continue;
            }

            $this->buildFile($file);
        }

        foreach ($post->getChildren() as $child) {
            if ($child->getProperty('invisible', false)) {
                continue;
            }

            $this->buildPost($child);
        }
    }

    private function buildFile(FileInterface $file)
    {
        $source = $file->getPath();
        $destination = $this->getPathInBuild((string) $file->getUrl());
        copy($source, $destination);
    }

    /**
     * @throws Exception
     */
    private function clearBuild()
    {
        $dir = $this->joinPaths($this->settings->getBuild());

        if (!is_dir($dir)) {
            mkdir($dir);

            return;
        }

        if (!is_writable($dir)) {
            throw new Exception('Build directory is not writable');
        }

        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
            RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
    }

    /**
     * @return string
     */
    private function getPathInBuild()
    {
        $args = func_get_args();
        array_unshift($args, $this->getSettings()->getBuild());

        return call_user_func_array([$this, 'joinPaths'], $args);
    }

    /**
     * @throws Exception
     */
    public function validate()
    {
        if (!is_writable($this->joinPaths())) {
            throw new Exception('Cannot write to build directory');
        }

        if (!is_readable($this->theme->getDirectory())) {
            throw new Exception('Cannot read from theme directory');
        }

        if (!is_readable($this->joinPaths($this->settings->getContentDirectory()))) {
            throw new Exception('Cannot read from content directory');
        }
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return string
     */
    private function joinPaths()
    {
        $args = func_get_args();
        array_unshift($args, $this->path);

        return call_user_func_array('joinPaths', $args);
    }

    /**
     * @return Branches
     */
    public function getBranches()
    {
        return $this->branches;
    }
}
