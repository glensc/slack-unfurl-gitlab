<?php

namespace GitlabSlackUnfurl\Route;

use DateTime;
use DateTimeZone;
use Generator;
use Gitlab;
use Psr\Log\LoggerInterface;
use SlackUnfurl\LoggerTrait;
use SlackUnfurl\SlackClient;

class MergeRequest
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
        $mr = $this->getDetails($parts);
        $this->debug('merge_request', ['merge_request' => $mr]);

        if (!$mr) {
            return null;
        }

        return [
            'title' => sprintf(
                '<%s|#%d>: %s',
                $this->slackClient->urlencode($url),
                $mr['iid'],
                $this->slackClient->escape($mr['title'])
            ),
            'color' => '#E24329',
            'ts' => (new DateTime($mr['created_at'], $this->utc))->getTimestamp(),
            'footer' => "Created by {$this->formatAuthor($mr['author'])}",
            'fields' => iterator_to_array($this->getFields($mr)),
        ];
    }

    /**
     * Skip empty fields, join array and generators
     */
    private function getFields(array $object)
    {
        foreach ($this->buildFields($object) as $field) {
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

    private function buildFields(array $object)
    {
        yield [
            'title' => 'Assignee',
            'value' => $this->formatAuthor($object['assignee']),
        ];
        yield [
            'title' => 'Labels',
            'value' => $object['labels'] ?? null,
        ];
        yield [
            'title' => 'Milestone',
            'value' => $object['milestone']['title'] ?? null,
        ];
    }

    private function formatAuthor(array $author)
    {
        return sprintf('<%s|%s>',
            $this->slackClient->urlencode($author['web_url']),
            $this->slackClient->escape($author['name'])
        );
    }

    private function getDetails(array $parts)
    {
        return $this->apiClient->merge_requests->show($parts['project_path'], $parts['number']);
    }
}