<?php

namespace Pine\Command;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Pine\Site;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Pine\Command
 */
class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Build your site')
            ->addOption('env', null, InputOption::VALUE_REQUIRED, 'Environment', 'dev')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $input->getOption('env') === Site::ENV_PROD ? Site::ENV_PROD : Site::ENV_DEV;

        $site = new Site(getcwd(), $env);
        $site->build();
    }
}
