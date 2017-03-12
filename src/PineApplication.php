<?php

namespace Pine;

use Pine\Command\BuildCommand;
use Pine\Command\CreateCommand;
use Pine\Command\ServeCommand;
use Pine\Command\UpdateCommand;
use Pine\Command\WatchCommand;
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

        $this->add(new UpdateCommand());
        $this->add(new BuildCommand());
        $this->add(new WatchCommand());
        $this->add(new ServeCommand());
    }
}
