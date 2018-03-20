<?php

namespace GitlabSlackUnfurl\ServiceProvider;

use Gitlab;
use GitlabSlackUnfurl\Event\Subscriber\GitlabUnfurler;
use GitlabSlackUnfurl\Route;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use SlackUnfurl\CommandResolver;
use SlackUnfurl\SlackClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GitlabUnfurlServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['gitlab.url'] = getenv('GITLAB_URL');
        $app['gitlab.api_token'] = getenv('GITLAB_API_TOKEN');

        $app['gitlab.domain'] = function ($app) {
            return parse_url($app['gitlab.url'], PHP_URL_HOST);
        };

        $app[Gitlab\Client::class] = function ($app) {
            $client = Gitlab\Client::create($app['gitlab.url']);
            $client->authenticate($app['gitlab.api_token'], Gitlab\Client::AUTH_HTTP_TOKEN);

            return $client;
        };

        $app[CommandResolver::class] = function ($app) {
            return new CommandResolver($app);
        };

        $app[GitlabUnfurler::class] = function ($app) {
            return new GitlabUnfurler(
                $app[Route\GitLabRoutes::class],
                $app[CommandResolver::class],
                $app['gitlab.domain'],
                $app['logger']
            );
        };

        $app[Route\GitLabRoutes::class] = function ($app) {
            return new Route\GitLabRoutes($app['gitlab.domain']);
        };

        $app[Route\Issue::class] = function ($app) {
            return new Route\Issue(
                $app[Gitlab\Client::class],
                $app[SlackClient::class],
                $app['logger']
            );
        };

        $app[Route\MergeRequest::class] = function ($app) {
            return new Route\MergeRequest(
                $app[Gitlab\Client::class],
                $app[SlackClient::class],
                $app['logger']
            );
        };
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app[GitlabUnfurler::class]);
    }
}