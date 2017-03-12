<?php

namespace Pine\Command;

use Illuminate\Filesystem\Filesystem;
use JasonLewis\ResourceWatcher\Event;
use JasonLewis\ResourceWatcher\Tracker;
use JasonLewis\ResourceWatcher\Watcher;
use Pine\Configuration\Settings;
use Pine\Site;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Pine\Command
 */
class WatchCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('watch')
            ->setDescription('Watch for changes')
            ->addOption('env', null, InputOption::VALUE_REQUIRED, 'Environment', 'dev');
    }

    private function build(Site $site, OutputInterface $output) {
        try {
            $site->build();
            $output->writeln('<info>Site rebuilt.</info>');
        } catch (\Exception $e) {
            $this->getApplication()->renderException($e, $output);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $input->getOption('env') === Site::ENV_PROD ? Site::ENV_PROD : Site::ENV_DEV;
        $path = getcwd();
        $site = new Site($path, $env);
        $files = new Filesystem;
        $tracker = new Tracker;
        $watcher = new Watcher($tracker, $files);
        $app = $this->getApplication();

        $handler = function (Event $event, $resource, $filePath) use ($app, $site, $env, $output) {
            $output->writeln(sprintf('<comment>Detected change at %s</comment>', $filePath));

            $this->build($site, $output);
        };

        $paths = [
            Settings::CONFIG_LOCATION,
            Settings::FILENAME,
            Settings::THEMES_LOCATION,
            $site->getSettings()->getContentDirectory()
        ];

        foreach ($paths as $eachPath) {
            $completePath = joinPaths($path, $eachPath);

            $listener = $watcher->watch($completePath);
            $listener->anything($handler);
        }

        $output->writeln('<comment>Watcher started</comment>');

        $this->build($site, $output);

        $watcher->start();
    }
}
