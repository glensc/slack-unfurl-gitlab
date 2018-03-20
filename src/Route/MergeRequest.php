<?php

namespace GitlabSlackUnfurl\Route;

class MergeRequest extends AbstractRouteHandler
{
    protected function getDetails(array $parts)
    {
        $object = $this->apiClient->merge_requests->show($parts['project_path'], $parts['number']);
        $this->debug('merge_request', ['merge_request' => $object]);

        return $object;
    }
}