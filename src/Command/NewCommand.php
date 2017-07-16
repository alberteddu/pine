<?php

namespace Pine\Command;

use Alberteddu\Octopus\DTO\Configuration;
use Alberteddu\Octopus\DTO\Environment;
use Alberteddu\Octopus\Octopus;
use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Pine\Site;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Pine\Command
 */
class NewCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Start a new site')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of your website')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $path = joinPaths(getcwd(), $name);

        if (is_dir($path)) {
            $output->writeln('<error>Directory already exists</error>');
        }

        $octopus = new Octopus(new ArgvInput(), new ConsoleOutput());
        $configuration = $octopus->getConfigurationFromFile(__DIR__ . '/../../config/new-site.octopus.json');
        $variables = $configuration->getVariables();
        $variables['name'] = $name;
        $configuration->setTarget($name);
        $configuration->setVariables($variables);

        $environment = new Environment($configuration, $path);
        $environment->setTemplatePath(__DIR__ . '/../../templates');
        $octopus->build($environment);
    }
}
