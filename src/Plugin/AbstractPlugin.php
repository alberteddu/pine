<?php

namespace Pine\Plugin;

use Pine\Site;

abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @var Site
     */
    protected $site;

    /**
     * {@inheritdoc}
     */
    public function setUp(Site $site)
    {
        $this->site = $site;
    }

    public function ready() {

    }
}
