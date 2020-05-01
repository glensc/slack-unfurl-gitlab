<?php

namespace GitlabSlackUnfurl\Test;

use ArrayObject;
use Gitlab;
use GuzzleHttp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Http\Adapter\Guzzle6\Client as HttpClient;
use LazyProperty\LazyPropertiesTrait;
use Pimple\Container;
use Psr\Log\NullLogger;
use SlackUnfurl\SlackClient;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use LazyPropertiesTrait;

    protected $url;
    protected $domain;
    /** @var Container */
    protected $container;
    /** @var HandlerStack */
    protected $handlerStack;
    /** @var ArrayObject */
    private $history;

    public function setUp(): void
    {
        $this->url = 'https://gitlab.com';
        $this->domain = parse_url($this->url, PHP_URL_HOST);
        $this->initLazyProperties(['container', 'handlerStack', 'history']);
    }

    protected function getContainer(): Container
    {
        $app = new Container();

        $app['debug'] = true;
        $app['ci'] = ($_SERVER['CI'] ?? '') === 'true';
        $app['gitlab.mock_client'] = $app['ci'] ?: $app['debug'];

        $app['gitlab.url'] = 'https://gitlab.com';
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

    protected function getHandlerStack()
    {
        return $this->container[HandlerStack::class];
    }

    protected function getHistory()
    {
        return $this->container['history'];
    }

    protected function getGitlabClient(array $responses, ArrayObject &$history = null): Gitlab\Client
    {
        $history = $this->history;

        // set mock responses conditionally
        if ($this->container['gitlab.mock_client']) {
            $mock = new MockHandler($responses);
            $this->handlerStack->setHandler($mock);
        }

        return $this->container[Gitlab\Client::class];
    }

    protected function getRouteHandler(string $class, array $responses, ArrayObject &$history = null)
    {
        return new $class(
            $this->getGitlabClient($responses, $history),
            $this->container[SlackClient::class],
            $this->container[NullLogger::class]
        );
    }
}
