<?php

namespace Pine;

use Pine\Command\CreateCommand;
use Pine\Command\UpdateCommand;
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

        $this->add(new CreateCommand());
        $this->add(new UpdateCommand());
    }
}
