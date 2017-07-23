<?php

namespace Pine;

use function PHPSTORM_META\type;
use Pine\Configuration\Configuration;
use Pine\Plugin\ThemeLocation;
use Pine\Twig\ReusableTwigEnvironment;
use Twig_Function;
use Twig_Loader_Filesystem;

class Theme
{
    const LAYOUTS_DIRECTORY = 'templates';
    const CONFIG_DIRECTORY = 'config';
    const PUBLIC_DIRECTORY = 'public';

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
    private $directory;

    /**
     * @var ReusableTwigEnvironment
     */
    private $twig;

    /**
     * Theme constructor.
     *
     * @param string $directory
     * @param string $env
     */
    public function __construct($directory, $env = Site::ENV_DEV)
    {
        $this->env = $env;
        $this->directory = $directory;
        $loader = new Twig_Loader_Filesystem(joinPaths($this->directory, self::LAYOUTS_DIRECTORY));
        $this->twig = new ReusableTwigEnvironment($loader, [
            'strict_variables' => true,
            'auto_reload' => true,
            'cache' => false,
        ]);

        $this->twig->addFunction(new Twig_Function('asset', function ($url) {
            if (substr($url, 0, 1) === '/') {
                $url = substr($url, 1);
            }

            return sprintf('/theme/%s', $url);
        }));

        $this->twig->addFunction(new Twig_Function('get', function ($context, $url) {
            if (!isset($context['site'])) {
                return '';
            }

            $site = $context['site'];

            if (!$site instanceof Site) {
                return '';
            }

            return $site->getBranches()->get($url);
        }, [
            'needs_context' => true
        ]));

        $this->loadConfig();
    }

    /**
     * @param ThemeLocation $themeLocation
     * @param string        $env
     *
     * @return Theme
     */
    public static function createFromThemeLocation(ThemeLocation $themeLocation, $env = Site::ENV_DEV)
    {
        return new self($themeLocation->getPath());
    }

    private function loadConfig()
    {
        $this->config = new Configuration();
        $this->config->processDirectory(joinPaths($this->directory, self::CONFIG_DIRECTORY), $this->env);
    }

    /**
     * @param string $layout
     * @param array  $context
     *
     * @return string
     */
    public function renderTemplate($layout, $context = [])
    {
        return $this->twig->render(sprintf('%s.html.twig', $layout), $context);
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Clear theme class cache by increasing an int value.
     */
    public function reloadTheme()
    {
        $this->loadConfig();
        $this->twig->increaseEntropy();
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getStaticFilePath($path)
    {
        return joinPaths($this->directory, self::PUBLIC_DIRECTORY, $path);
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }
}
