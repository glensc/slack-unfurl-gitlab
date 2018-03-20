<?php

namespace GitlabSlackUnfurl\Event\Subscriber;

use GitlabSlackUnfurl\Route;
use Psr\Log\LoggerInterface;
use SlackUnfurl\CommandResolver;
use SlackUnfurl\Event\Events;
use SlackUnfurl\Event\UnfurlEvent;
use SlackUnfurl\LoggerTrait;
use SlackUnfurl\Route\RouteMatcher;
use SlackUnfurl\RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabUnfurler implements EventSubscriberInterface
{
    use LoggerTrait;

    private const ROUTES = [
        'issue' => Route\Issue::class,
        'merge_request' => Route\MergeRequest::class,
    ];

    /** @var string */
    private $domain;

    /** @var RouteMatcher */
    private $routeMatcher;

    /** @var CommandResolver */
    private $commandResolver;

    public function __construct(
        RouteMatcher $routeMatcher,
        CommandResolver $commandResolver,
        string $domain,
        LoggerInterface $logger
    ) {
        $this->domain = $domain;
        $this->routeMatcher = $routeMatcher;
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
            try {
                $unfurl = $this->unfurlByUrl($link['url']);
                if ($unfurl) {
                    $event->addUnfurl($link['url'], $unfurl);
                }
            } catch (RuntimeException $e) {
                $this->debug("gitlab: {$e->getMessage()}");
            }
        }
    }

    private function unfurlByUrl(string $url)
    {
        [$router, $matches] = $this->routeMatcher->match($url);

        $command = $this->commandResolver
            ->configure(self::ROUTES)
            ->resolve($router);

        return $command->unfurl($url, $matches);
    }
}