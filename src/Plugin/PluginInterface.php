<?php

namespace Pine\Plugin;

use Branches\Extension\ExtensionInterface;
use Pine\Site;

interface PluginInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param Site $site
     */
    public function setUp(Site $site);

    /**
     * @return ExtensionInterface[]
     */
    public function getBranchesExtensions();

    /**
     * @return ThemeLocation[]
     */
    public function getThemeLocations();

    public function ready();
}
