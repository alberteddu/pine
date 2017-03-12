<?php

namespace Pine\Command;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Pine\Server;
use Pine\Site;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Pine\Command
 */
class ServeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('serve')
            ->setDescription('Serve your site')
            ->addOption('host', 'H', InputOption::VALUE_REQUIRED, 'Desired host', Server::DEFAULT_HOST)
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Desired port', Server::DEFAULT_PORT)
            ->addOption('env', null, InputOption::VALUE_REQUIRED, 'Environment', 'dev');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $input->getOption('env') === Site::ENV_PROD ? Site::ENV_PROD : Site::ENV_DEV;
        $path = getcwd();
        $site = new Site($path, $env);
        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $output->writeln(sprintf('<info>Running pine server at http://%s:%s</info>', $host, $port));
        $output->writeln('Press Ctrl-C to quit.');

        $server = new Server($site, $host, $port);
        $server->run();
    }
}
