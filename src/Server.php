<?php

namespace Pine;

use Branches\Branches;
use Branches\Node\FileInterface;
use Branches\Node\MimeType;
use Branches\Node\NodeNotFoundException;
use Branches\Node\PostInterface;
use React\EventLoop\Factory;
use React\EventLoop\StreamSelectLoop;
use React\Http\Request;
use React\Http\Response;
use React\Socket\Server as SocketServer;
use React\Http\Server as HttpServer;

class Server
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = '8080';

    /**
     * @var Site
     */
    private $site;

    /**
     * @var Branches
     */
    private $branches;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var StreamSelectLoop
     */
    private $loop;

    /**
     * @var SocketServer
     */
    private $socket;

    /**
     * @var HttpServer
     */
    private $server;

    public function __construct(Site $site, $host = self::DEFAULT_HOST, $port = self::DEFAULT_PORT)
    {
        $this->site = $site;
        $this->branches = $site->getBranches();
        $this->host = $host;
        $this->port = $port;

        $this->loop = Factory::create();
        $this->socket = new SocketServer($this->loop);
        $this->server = new HttpServer($this->socket);

        $this->addHandlers();
    }

    private function addHandlers()
    {
        $this->server->on('request', function (Request $request, Response $response) {
            $path = $request->getPath();
            $staticFileFound = false;
            $contentType = 'text/html';
            $data = '';

            if (strpos($path, '/theme/') === 0) {
                $withoutTheme = substr($path, 7);
                $staticFilePath = $this->site->getTheme()->getStaticFilePath($withoutTheme);

                if (is_readable($staticFilePath) && is_file($staticFilePath)) {
                    $staticFileFound = true;
                    $contentType = MimeType::getMimeTypeForPath($staticFilePath);
                    $data = file_get_contents($staticFilePath);
                }
            }

            if (!$staticFileFound) {
                try {
                    $node = $this->branches->get($path);

                    if ($node instanceof PostInterface) {
                        $this->site->getTheme()->clearTwigClassCache();
                        $data = $this->site->renderPost($node);
                    }

                    if ($node instanceof FileInterface) {
                        $data = file_get_contents($node->getPath());
                        $contentType = MimeType::getMimeTypeForPath($node->getPath());
                    }
                } catch (NodeNotFoundException $e) {
                    $response->writeHead(404, ['Content-Type' => 'text/html']);
                    $response->end('Not found');
                }
            }

            $response->writeHead(200, ['Content-Type' => $contentType]);
            $response->end($data);
        });
    }

    public function run()
    {
        $this->socket->listen($this->port, $this->host);
        $this->loop->run();
    }
}
