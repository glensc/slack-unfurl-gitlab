<?php

namespace GitlabSlackUnfurl\Test;

use ArrayObject;
use Gitlab;
use GuzzleHttp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Http\Adapter\Guzzle6\Client as HttpClient;
use Pimple\Container;
use Psr\Log\NullLogger;
use SlackUnfurl\SlackClient;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $url;
    protected $domain;

    public function setUp()
    {
        $this->url = 'https://gitlab.com';
        $this->domain = parse_url($this->url, PHP_URL_HOST);
    }

    private function createContainer(): Container
    {
        $app = new Container();

        $app['gitlab.url'] = 'https://gitlab.com';
        $app['gitlab.mock_client'] = (bool)($_SERVER['CI'] ?? true);
        $app['gitlab.api_token'] = $app['gitlab.mock_client'] ? null : ($_SERVER['GITLAB_API_TOKEN'] ?? '');

        $app['history'] = new ArrayObject();
        $app[HandlerStack::class] = static function () {
            return HandlerStack::create();
        };
        $app[Gitlab\Client::class] = static function ($app) {
            /** @var HandlerStack $handlerStack */
            $handlerStack = $app[HandlerStack::class];
            $handlerStack->push(Middleware::history($app['history']));

            $guzzle = new GuzzleHttp\Client(['handler' => $handlerStack]);
            $httpClient = new HttpClient($guzzle);

            $client = Gitlab\Client::createWithHttpClient($httpClient);
            $client->setUrl($app['gitlab.url']);

            if ($app['gitlab.api_token']) {
                $client->authenticate($app['gitlab.api_token'], Gitlab\Client::AUTH_HTTP_TOKEN);
            }

            return $client;
        };

        $app[SlackClient::class] = new SlackClient('');
        $app[NullLogger::class] = new NullLogger();

        return $app;
    }

    protected function getRouteHandler(string $class, array $responses, ArrayObject &$history = null)
    {
        $app = $this->createContainer();

        /** @var HandlerStack $handlerStack */
        $handlerStack = $app[HandlerStack::class];
        $history = $app['history'];

        // set mock responses conditionally
        if ($app['gitlab.mock_client']) {
            $mock = new MockHandler($responses);
            $handlerStack->setHandler($mock);
        }

        return new $class(
            $app[Gitlab\Client::class],
            $app[SlackClient::class],
            $app[NullLogger::class]
        );
    }
}
