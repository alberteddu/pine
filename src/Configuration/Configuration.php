<?php

namespace Pine\Configuration;

use Branches\Property\PropertyHolderTrait;
use DirectoryIterator;
use Symfony\Component\Yaml\Yaml;

/**
 * @package Pine\Configuration
 */
class Configuration
{
    use PropertyHolderTrait;

    /**
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        $this->setProperties($properties);
    }

    /**
     * @return array
     */
    protected function getSupportedExtensions()
    {
        return array('yml', 'yaml');
    }

    /**
     * @inheritdoc
     */
    public function supportsFile(DirectoryIterator $file)
    {
        return in_array($file->getExtension(), $this->getSupportedExtensions());
    }

    /**
     * @param DirectoryIterator $file
     */
    public function processFile(DirectoryIterator $file)
    {
        if ($file->getExtension() != 'yml' and $file->getExtension() != 'yaml') {
            return;
        }

        $properties = Yaml::parse(file_get_contents($file->getRealPath()));

        if (!is_array($properties)) {
            return;
        }

        $this->mergeProperties($properties);
    }

    /**
     * @param string $path
     * @param string $env
     */
    public function processDirectory($path, $env)
    {
        $configFiles = new \DirectoryIterator($path);

        foreach ($configFiles as $file) {
            if ($file->isDot()) {
                continue;
            }

            $name         = $file->getBasename('.' . $file->getExtension());
            $nameSegments = explode('_', $name);

            if (count($nameSegments) > 1) {
                $lastSegment = $nameSegments[count($nameSegments) - 1];

                if ($lastSegment != $env) {
                    continue;
                }
            }

            if (!$this->supportsFile($file)) {
                continue;
            }

            $this->processFile($file);
        }
    }
}
