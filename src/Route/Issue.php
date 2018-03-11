<?php

namespace GitlabSlackUnfurl\Route;

use Gitlab;
use Psr\Log\LoggerInterface;
use SlackUnfurl\LoggerTrait;

class Issue
{
    use LoggerTrait;

    /** @var Gitlab\Client */
    private $apiClient;

    public function __construct(
        Gitlab\Client $apiClient,
        LoggerInterface $logger
    ) {
        $this->apiClient = $apiClient;
        $this->logger = $logger;
    }

    public function unfurl(string $url, array $parts)
    {
        $issue = $this->getIssueDetails($parts);
        $this->debug('issue', ['issue' => $issue]);

        if (!$issue) {
            return null;
        }

        return [
            'title' => "<$url|#{$issue['iid']}>: {$issue['title']}",
        ];
    }

    private function getIssueDetails(array $parts)
    {
        return $this->apiClient->issues->show($parts['project_path'], $parts['number']);
    }
}