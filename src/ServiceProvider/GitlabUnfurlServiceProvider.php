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
    public function register(Container $app): void
    {
        $app['gitlab.url'] = getenv('GITLAB_URL');
        $app['gitlab.api_token'] = getenv('GITLAB_API_TOKEN');

        $app['gitlab.domain'] = static function ($app) {
            return parse_url($app['gitlab.url'], PHP_URL_HOST);
        };

        $app[Gitlab\Client::class] = static function ($app) {
            $client = Gitlab\Client::create($app['gitlab.url']);
            $client->authenticate($app['gitlab.api_token'], Gitlab\Client::AUTH_HTTP_TOKEN);

            return $client;
        };

        $app[GitlabUnfurler::class] = static function ($app) {
            return new GitlabUnfurler(
                $app[Route\GitLabRoutes::class],
                $app[CommandResolver::class],
                $app['gitlab.domain'],
                $app['logger']
            );
        };

        $app[Route\GitLabRoutes::class] = static function ($app) {
            return new Route\GitLabRoutes($app['gitlab.domain']);
        };

        $routeFactory = static function ($class) use ($app) {
            $app[$class] = static function ($app) use ($class) {
                return new $class(
                    $app[Gitlab\Client::class],
                    $app[SlackClient::class],
                    $app['logger']
                );
            };
        };

        $routeFactory(Route\MergeRequest::class);
        $routeFactory(Route\Issue::class);
        $routeFactory(Route\Note::class);
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher): void
    {
        $dispatcher->addSubscriber($app[GitlabUnfurler::class]);
    }
}