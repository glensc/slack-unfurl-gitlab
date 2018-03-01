<?php

namespace GitlabSlackUnfurl\Event\Subscriber;

use DateTime;
use DateTimeZone;
use Eventum_RPC;
use Eventum_RPC_Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SlackUnfurl\Event\Events;
use SlackUnfurl\Event\UnfurlEvent;
use SlackUnfurl\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Gitlab;

class GitlabUnfurler implements EventSubscriberInterface
{
    use LoggerTrait;

    /** @var Gitlab\Client */
    private $apiClient;

    /** @var string */
    private $domain;

    public function __construct(
        Gitlab\Client $apiClient,
        string $domain,
        LoggerInterface $logger
    )
    {
        $this->domain = $domain;
        $this->apiClient = $apiClient;
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
            $url = $link['url'];
            $unfurl = [];
            $event->addUnfurl($url, $unfurl);
        }
    }
}