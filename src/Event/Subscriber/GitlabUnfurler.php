<?php

namespace GitlabSlackUnfurl\Event\Subscriber;

use GitlabSlackUnfurl\Route;
use Psr\Log\LoggerInterface;
use SlackUnfurl\CommandResolver;
use SlackUnfurl\Event\Events;
use SlackUnfurl\Event\UnfurlEvent;
use SlackUnfurl\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabUnfurler implements EventSubscriberInterface
{
    use LoggerTrait;

    private const ROUTES = [
        'issue' => Route\Issue::class,
    ];

    /** @var string */
    private $domain;

    /** @var Route\RouteMatcher */
    private $routes;

    /** @var CommandResolver */
    private $commandResolver;

    public function __construct(
        Route\RouteMatcher $routes,
        CommandResolver $commandResolver,
        string $domain,
        LoggerInterface $logger
    ) {
        $this->domain = $domain;
        $this->routes = $routes;
        $this->commandResolver = $commandResolver;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SLACK_UNFURL => ['unfurl', 10],
        ];
    }

    public function unfurl(UnfurlEvent $event)
    {
        foreach ($event->getMatchingLinks($this->domain) as $link) {
            $unfurl = $this->unfurlByUrl($link['url']);
            if ($unfurl) {
                $event->addUnfurl($link['url'], $unfurl);
            }
        }
    }

    private function unfurlByUrl(string $url)
    {
        $match = $this->routes->match($url);
        if (!$match) {
            return null;
        }

        [$router, $matches] = $match;

        $command = $this->commandResolver
            ->configure(self::ROUTES)
            ->resolve($router);

        return $command->unfurl($url, $matches);
    }
}