<?php

namespace GitlabSlackUnfurl\Route;

use Gitlab\Api\Projects;
use InvalidArgumentException;
use RuntimeException;

class Note extends AbstractRouteHandler
{
    /**
     * @see https://docs.gitlab.com/ce/api/notes.html#get-single-issue-note
     * @param array $parts
     * @return array
     */
    protected function getDetails(array $parts): array
    {
        $note = $this->getNote($parts);
        $this->debug('note', ['note' => $note]);

        return $note;
    }

    private function getNote(array $parts): array
    {
        switch ($parts['type']) {
            case 'issues':
                return $this->getIssueNote($parts['project_path'], $parts['number'], $parts['id']);

            case 'merge_requests':
                return $this->getMergeRequestNote($parts['project_path'], $parts['number'], $parts['id']);

            case 'commit':
                return $this->getCommitNote($parts['project_path'], $parts['number'], $parts['id']);

            default:
                throw new InvalidArgumentException("Unknown type: {$parts['type']}");

        }
    }

    private function getIssueNote(string $project_id, int $issue_iid, int $note_id): array
    {
        $issue = $this->apiClient->issues->show($project_id, $issue_iid);
        $note = $this->apiClient->issues->showComment($project_id, $issue_iid, $note_id);

        // for formatTitle
        $note['blurb'] = "#{$note['noteable_iid']}";
        $note['title'] = "Note on issue {$note['blurb']}: {$issue['title']}";

        return $note;
    }

    private function getMergeRequestNote(string $project_id, int $merge_request_iid, int $note_id): array
    {
        $api = $this->apiClient->merge_requests;

        // unfortunately no api to get single note
        $notes = $api->showNotes($project_id, $merge_request_iid);
        // re-index with id
        $notes = array_column($notes, null, 'id');
        $note = $notes[$note_id] ?? null;

        if (!$note) {
            throw new RuntimeException("Could not load note: {$note_id}");
        }

        $merge_request = $api->show($project_id, $merge_request_iid);

        // for formatTitle
        $note['blurb'] = "!{$note['noteable_iid']}";
        $note['title'] = "Note on merge request {$note['blurb']}: {$merge_request['title']}";

        return $note;
    }

    private function getCommitNote(string $project_id, string $sha, int $note_id): array
    {
        // unfortunately no api to get single note by id
        $discussions = $this->getRepositoryCommitDiscussions($project_id, $sha);

        // re-index with id
        foreach ($discussions as $discussion) {
            $notes = array_column($discussion['notes'], null, 'id');
            $note = $notes[$note_id] ?? null;
            if ($note) {
                break;
            }
        }

        if (!$note) {
            throw new RuntimeException("Could not load note: {$note_id}");
        }

        $commit = $this->apiClient->repositories->commit($project_id, $sha);

        // for formatTitle
        $note['blurb'] = $commit['short_id'];
        $note['title'] = "Comment on commit {$commit['short_id']}: {$commit['title']}";

        return $note;
    }

    /**
     * @param array $object
     * @return string
     */
    protected function getText(array $object): string
    {
        return $this->sanitizeText($object['body']);
    }

    protected function getFields(array $object): array
    {
        return [];
    }

    /**
     * API to fetch repository/commits/.../discussions.
     *
     * Until upstream has support for this:
     * - https://github.com/m4tthumphrey/php-gitlab-api/issues/347
     *
     * @param string $project_id
     * @param string $commit_id
     * @return mixed
     */
    private function getRepositoryCommitDiscussions(string $project_id, string $commit_id): array
    {
        $api = new class($this->apiClient) extends Projects {
            public function getRepositoryCommitDiscussions($project_id, $commit_id)
            {
                return $this->get($this->getProjectPath($project_id, 'repository/commits/' . $this->encodePath($commit_id)) . '/discussions');
            }
        };

        return $api->getRepositoryCommitDiscussions($project_id, $commit_id);
    }
}
