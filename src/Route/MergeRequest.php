<?php

namespace GitlabSlackUnfurl\Route;

class MergeRequest extends AbstractRouteHandler
{
    protected function getDetails(array $parts): array
    {
        $merge_request = $this->apiClient->merge_requests->show($parts['project_path'], $parts['number']);
        $this->debug('merge_request', ['merge_request' => $merge_request]);

        // for formatTitle
        $merge_request['blurb'] = "!{$merge_request['iid']}";

        return $merge_request;
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
