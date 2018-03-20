<?php

namespace GitlabSlackUnfurl\Route;

use DateTime;
use DateTimeZone;
use Generator;
use Gitlab;
use Psr\Log\LoggerInterface;
use SlackUnfurl\LoggerTrait;
use SlackUnfurl\SlackClient;

abstract class AbstractRouteHandler
{
    use LoggerTrait;

    /** @var Gitlab\Client */
    protected $apiClient;
    /** @var SlackClient */
    protected $slackClient;
    /** @var DateTimeZone */
    protected $utc;

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

    abstract protected function getDetails(array $parts);

    public function unfurl(string $url, array $parts)
    {
        $object = $this->getDetails($parts);
        if (!$object) {
            return null;
        }

        return [
            'title' => $this->formatTitle($url, $object),
            'color' => '#E24329',
            'ts' => $this->formatCreatedDate($object),
            'footer' => "Created by {$this->formatAuthor($object['author'])}",
            'fields' => iterator_to_array($this->getFields($this->buildFields($object))),
        ];
    }

    protected function getAssignees(array $assignees)
    {
        foreach ($assignees as $assignee) {
            yield $this->formatAuthor($assignee);
        }
    }

    protected function formatTitle(string $url, array $object)
    {
        return sprintf(
            '<%s|#%d>: %s',
            $this->slackClient->urlencode($url),
            $object['iid'],
            $this->slackClient->escape($object['title'])
        );
    }

    protected function formatCreatedDate(array $object): int
    {
        return (new DateTime($object['created_at'], $this->utc))->getTimestamp();
    }

    protected function formatAuthor(?array $author)
    {
        if (!$author) {
            return null;
        }

        return sprintf('<%s|%s>',
            $this->slackClient->urlencode($author['web_url']),
            $this->slackClient->escape($author['name'])
        );
    }

    protected function buildFields(array $object)
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

    /**
     * Skip empty fields, join array and generators
     */
    protected function getFields($fields)
    {
        foreach ($fields as $field) {
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
}