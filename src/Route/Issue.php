<?php

namespace GitlabSlackUnfurl\Route;

class Issue extends AbstractRouteHandler
{
    protected function getDetails(array $parts)
    {
        $project_id = $parts['project_path'];
        $issue_iid = $parts['number'];

        $issue = $this->apiClient->issues->show($project_id, $issue_iid);
        $this->debug('issue', ['issue' => $issue]);

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