<?php

namespace GitlabSlackUnfurl\ServiceProvider;

use Gitlab;
use GitlabSlackUnfurl\Event\Subscriber\GitlabUnfurler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
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

        $app[Gitlab\Client::class] = function ($app) {
            $client = Gitlab\Client::create($app['gitlab.url']);
            $client->authenticate($app['gitlab.api_token'], Gitlab\Client::AUTH_HTTP_TOKEN);

            return $client;
        };

        $app[GitlabUnfurler::class] = function ($app) {
            $domain = parse_url($app['gitlab.url'], PHP_URL_HOST);

            return new GitlabUnfurler(
                $app[Gitlab\Client::class],
                $domain,
                $app['logger']
            );
        };
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app[GitlabUnfurler::class]);
    }
}