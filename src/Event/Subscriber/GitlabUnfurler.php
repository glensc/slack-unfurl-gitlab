<?php

namespace GitlabSlackUnfurl\Event\Subscriber;

use Gitlab;
use Psr\Log\LoggerInterface;
use SlackUnfurl\Event\Events;
use SlackUnfurl\Event\UnfurlEvent;
use SlackUnfurl\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
    ) {
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
            $unfurl = $this->getIssueUnfurl($link['url']);
            if ($unfurl) {
                $event->addUnfurl($link['url'], $unfurl);
            }
        }
    }

    private function getIssueUnfurl(string $url)
    {
        $issue = $this->getIssueDetails($url);
        $this->debug('issue', ['issue' => $issue]);
        if (!$issue) {
            return null;
        }

        return [
            'title' => "<$url|#{$issue['iid']}>: {$issue['title']}",
        ];
    }

    private function getIssueDetails(string $url)
    {
        if (!preg_match("#^https?://\Q{$this->domain}\E/(?P<path>.+)/issues/(?P<id>\d+)#", $url, $m)) {
            return null;
        }

        return $this->apiClient->issues->show($m['path'], $m['id']);
    }
}