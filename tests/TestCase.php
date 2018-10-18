<?php

namespace GitlabSlackUnfurl\Test;

use Gitlab;
use GitlabSlackUnfurl\Route\MergeRequest;
use Pimple\Container;
use Psr\Log\NullLogger;
use SlackUnfurl\SlackClient;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $url;
    protected $domain;

    /** @var Container */
    protected $app;

    public function setUp()
    {
        $this->url = 'https://gitlab.com';
        $this->domain = parse_url($this->url, PHP_URL_HOST);
        $this->app = $this->createContainer();
    }

    public function createContainer(): Container
    {
        $app = new Container();

        $app[Gitlab\Client::class] = new Gitlab\Client();
        $app[SlackClient::class] = new SlackClient('');
        $app[NullLogger::class] = new NullLogger();

        $app[MergeRequest::class] = function ($app) {
            return new MergeRequest(
                $app[Gitlab\Client::class],
                $app[SlackClient::class],
                $app[NullLogger::class]
            );
        };

        return $app;
    }
}
