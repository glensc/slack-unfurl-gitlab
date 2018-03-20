<?php

namespace GitlabSlackUnfurl\Route;

class Issue extends AbstractRouteHandler
{
    protected function getDetails(array $parts)
    {
        $object = $this->apiClient->issues->show($parts['project_path'], $parts['number']);
        $this->debug('issue', ['issue' => $object]);

        return $object;
    }
}