<?php

namespace Pine;

use Pine\Configuration\Configuration;
use Pine\Twig\ReusableTwigEnvironment;
use Twig_Environment;
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
            'auto_reload' => true,
            'cache' => false,
        ]);
        $this->twig->addFunction(new Twig_Function('asset', function($url) {
            if (substr($url, 0, 1) === '/') {
                $url = substr($url, 1);
            }

            return sprintf('/theme/%s', $url);
        }));
        $this->config = new Configuration();
        $this->config->processDirectory(joinPaths($this->directory, self::CONFIG_DIRECTORY), $env);
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

    public function clearTwigClassCache()
    {
        $this->twig->increaseEntropy();
    }
}
