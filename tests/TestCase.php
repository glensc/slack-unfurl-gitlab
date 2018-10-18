<?php

namespace GitlabSlackUnfurl\Test;

use Gitlab;
use GuzzleHttp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
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
        $app['gitlab.api_token'] = getenv('GITLAB_TOKEN') ?: '';
        $app['gitlab.mock_client'] = getenv('CI') ?: true;

        $app[Gitlab\Client::class] = function ($app) {
            $client = new Gitlab\Client();
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

    protected function getRouteHandler(string $class, array $responses)
    {
        $app = $this->createContainer();

        if ($app['gitlab.mock_client']) {
            $gitlabClient = $this->getGitLabMock($responses);
        } else {
            $gitlabClient = $app[Gitlab\Client::class];
        }

        return new $class(
            $gitlabClient,
            $app[SlackClient::class],
            $app[NullLogger::class]
        );
    }

    /**
     * Create GitLab client with each API call mocked in $responses.
     *
     * @param array $responses
     * @return Gitlab\Client
     */
    private function getGitLabMock(array $responses)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $guzzle = new GuzzleHttp\Client(['handler' => $handler]);
        $httpClient = new HttpClient($guzzle);

        return Gitlab\Client::createWithHttpClient($httpClient);
    }
}
