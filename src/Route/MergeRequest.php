<?php

namespace GitlabSlackUnfurl\Route;

class MergeRequest extends AbstractRouteHandler
{
    protected function getDetails(array $parts)
    {
        $merge_request = $this->apiClient->merge_requests->show($parts['project_path'], $parts['number']);
        $this->debug('merge_request', ['merge_request' => $merge_request]);

        return $merge_request;
    }
}