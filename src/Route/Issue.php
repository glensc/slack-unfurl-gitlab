<?php

namespace GitlabSlackUnfurl\Route;

use DateTime;
use DateTimeZone;
use Generator;
use Gitlab;
use Psr\Log\LoggerInterface;
use SlackUnfurl\LoggerTrait;
use SlackUnfurl\SlackClient;

class Issue
{
    use LoggerTrait;

    /** @var Gitlab\Client */
    private $apiClient;
    /** @var SlackClient */
    private $slackClient;
    /** @var DateTimeZone */
    private $utc;

    public function __construct(
        Gitlab\Client $apiClient,
        SlackClient $slackClient,
        LoggerInterface $logger
    ) {
        $this->apiClient = $apiClient;
        $this->slackClient = $slackClient;
        $this->logger = $logger;
        $this->utc = new DateTimeZone('UTC');
    }

    public function unfurl(string $url, array $parts)
    {
        $issue = $this->getIssueDetails($parts);
        $this->debug('issue', ['issue' => $issue]);

        if (!$issue) {
            return null;
        }

        return [
            'title' => sprintf(
                "<%s|#%d>: %s",
                $this->slackClient->urlencode($url),
                $issue['iid'],
                $this->slackClient->escape($issue['title'])
            ),
            'color' => '#E24329',
            'ts' => (new DateTime($issue['created_at'], $this->utc))->getTimestamp(),
            'footer' => "Created by {$this->formatAuthor($issue['author'])}",
            'fields' => iterator_to_array($this->getFields($issue)),
        ];
    }

    /**
     * Skip empty fields, join array and generators
     */
    private function getFields(array $issue)
    {
        foreach ($this->buildFields($issue) as $field) {
            if ($field['value'] instanceof Generator) {
                $field['value'] = iterator_to_array($field['value']);
            }
            if (is_array($field['value'])) {
                $field['value'] = implode(', ', $field['value']);
            }
            if (!isset($field['short'])) {
                $field['short'] = true;
            }

            if ($field['value']) {
                yield $field;
            }
        }
    }

    private function buildFields(array $issue)
    {
        yield [
            'title' => 'Assignees',
            'value' => $this->getAssignees($issue['assignees']),
        ];
        yield [
            'title' => 'Labels',
            'value' => $issue['labels'] ?? null,
        ];
        yield [
            'title' => 'Milestone',
            'value' => $issue['milestone']['title'] ?? null,
        ];
    }

    private function getAssignees(array $assignees)
    {
        foreach ($assignees as $assignee) {
            yield $this->formatAuthor($assignee);
        }
    }

    private function formatAuthor(array $author)
    {
        return sprintf('<%s|%s>',
            $this->slackClient->urlencode($author['web_url']),
            $this->slackClient->escape($author['name'])
        );
    }

    private function getIssueDetails(array $parts)
    {
        return $this->apiClient->issues->show($parts['project_path'], $parts['number']);
    }
}