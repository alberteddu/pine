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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $input->getOption('env') === Site::ENV_PROD ? Site::ENV_PROD : Site::ENV_DEV;
        $path = getcwd();
        $site = new Site($path, $env);
        $files = new Filesystem;
        $tracker = new Tracker;
        $watcher = new Watcher($tracker, $files);
        $handler = function (Event $event, $resource, $filePath) use ($site, $env, $output) {
            $output->writeln(sprintf('Detected change at %s', $filePath));

            try {
                $site->build();
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
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

        $output->writeln('Watcher started');

        $site->build();

        $watcher->start();
    }
}
