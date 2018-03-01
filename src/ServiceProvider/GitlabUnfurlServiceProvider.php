<?php

namespace GitlabSlackUnfurl\ServiceProvider;

use Eventum\SlackUnfurl\Event\Subscriber\EventumUnfurler;
use Eventum_RPC;
use GitlabSlackUnfurl\Event\Subscriber\GitlabUnfurler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Gitlab;

class GitlabUnfurlServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['gitlab.url'] = getenv('GITLAB_URL');

        $app[Gitlab\Client::class] = function ($app) {
            return Gitlab\Client::create($app['gitlab.url']);
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