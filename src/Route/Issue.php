<?php

namespace GitlabSlackUnfurl\Route;

class Issue extends AbstractRouteHandler
{
    protected function getDetails(array $parts): array
    {
        $project_id = $parts['project_path'];
        $issue_iid = $parts['number'];

        $issue = $this->apiClient->issues->show($project_id, $issue_iid);
        $this->debug('issue', ['issue' => $issue]);

        // for formatTitle
        $issue['blurb'] = "#{$issue['iid']}";

        return $issue;
    }

    /**
     * @param array $object
     * @return string
     */
    protected function getText(array $object): string
    {
        return $this->sanitizeText($object['description']);
    }
}