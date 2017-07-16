<?php

namespace Pine;

use Pine\Command\BuildCommand;
use Pine\Command\NewCommand;
use Pine\Command\ServeCommand;
use Pine\Command\WatchCommand;
use Deployer\Component\PharUpdate\Console\Command;
use Deployer\Component\PharUpdate\Console\Helper;
use Symfony\Component\Console\Application;

class PineApplication extends Application
{
    /**
     * Application constructor.
     *
     * @param string $name
     * @param string $version
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->add(new BuildCommand());
        $this->add(new WatchCommand());
        $this->add(new ServeCommand());
        $this->add(new NewCommand());

        $command = new Command('update');
        $command->setManifestUri('https://alberteddu.github.io/pine/manifest.json');
        $this->getHelperSet()->set(new Helper());
        $this->add($command);
    }
}
