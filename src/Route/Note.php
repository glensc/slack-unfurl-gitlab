<?php

namespace GitlabSlackUnfurl\Route;

class Note extends AbstractRouteHandler
{
    /**
     * @see https://docs.gitlab.com/ce/api/notes.html#get-single-issue-note
     * @param array $parts
     * @return array
     */
    protected function getDetails(array $parts): array
    {
        $project_id = $parts['project_path'];
        $issue_iid = $parts['number'];
        $note_id = $parts['id'];

        $issue = $this->apiClient->issues->show($project_id, $issue_iid);
        $note = $this->apiClient->issues->showComment($project_id, $issue_iid, $note_id);

        // for formatTitle
        $note['iid'] = $note['noteable_iid'];
        $note['title'] = "Note on issue #{$note['iid']}: {$issue['title']}";

        $this->debug('note', ['note' => $note]);

        return $note;
    }

    protected function getFields(array $object): array
    {
        return [];
    }
}