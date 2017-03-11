<?php

namespace Pine\Configuration;

use Branches\Property\PropertyHolderTrait;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use JMS\Serializer\Annotation as Serializer;

/**
 * @package Pine
 */
class Settings
{
    const FILENAME = 'pine.yml';
    const THEMES_LOCATION = 'themes';
    const CONFIG_LOCATION = 'config';

    /**
     * @var string
     *
     * @Serializer\Type(name="string")
     * @Serializer\SerializedName(value="content")
     */
    private $contentDirectory;

    /**
     * @var string
     *
     * @Serializer\Type(name="string")
     * @Serializer\SerializedName(value="theme")
     */
    private $theme;

    /**
     * @var string
     *
     * @Serializer\Type(name="string")
     * @Serializer\SerializedName(value="build")
     */
    private $build;

    /**
     * @param string $path
     *
     * @return Settings
     */
    public static function createFromFileOrDefaults($path)
    {
        $settings = self::getDefaultSettings();

        if (is_file($path) && is_readable($path)) {
            try {
                $customSettings = Yaml::parse(file_get_contents($path));
            } catch (ParseException $e) {
                $customSettings = [];
            }

            if (!is_array($customSettings)) {
                $customSettings = [];
            }

            $settings = PropertyHolderTrait::deepMergeProperties($settings, $customSettings);
        }

        $serializer = SerializerFactory::create();

        return $serializer->fromArray($settings, self::class);
    }

    /**
     * @return Settings
     */
    public static function createFromDefaults()
    {
        $serializer = SerializerFactory::create();

        return $serializer->fromArray(self::getDefaultSettings(), self::class);
    }

    /**
     * @return array
     */
    private static function getDefaultSettings()
    {
        return [
            'content' => 'content',
            'theme' => 'pine',
            'build' => 'build',
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $serializer = SerializerFactory::create();

        return $serializer->toArray($this);
    }

    /**
     * @return string
     */
    public function getContentDirectory()
    {
        return $this->contentDirectory;
    }

    /**
     * @param string $contentDirectory
     *
     * @return Settings
     */
    public function setContentDirectory($contentDirectory)
    {
        $this->contentDirectory = $contentDirectory;

        return $this;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return string
     */
    public function getFullTheme()
    {
        return joinPaths(self::THEMES_LOCATION, $this->theme);
    }

    /**
     * @param string $theme
     *
     * @return Settings
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @param string $build
     *
     * @return Settings
     */
    public function setBuild($build)
    {
        $this->build = $build;

        return $this;
    }
}
