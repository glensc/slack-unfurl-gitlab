<?php

namespace GitlabSlackUnfurl\Route;

use SlackUnfurl\Route\RouteMatcher as BaseRouteMatcher;

class RouteMatcher extends BaseRouteMatcher
{
    /** @var string */
    private $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    protected function getRoutes(): array
    {
        return $this->routes = $this->routes ?: $this->buildRoutes($this->domain);
    }

    /**
     * @param string $domain
     * @return array
     * @see https://github.com/integrations/slack/blob/d9fc0648c2a0158bf4bf32cf527c5675604501d4/lib/github-url.js
     */
    protected function buildRoutes($domain = 'gitlab.com'): array
    {
        // https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/lib/gitlab/path_regex.rb#L125
        $path = '[a-zA-Z0-9_.][a-zA-Z0-9_.-]*';

        // subgroups up to 20 levels
        // https://docs.gitlab.com/ce/user/group/subgroups/index.html
        $namespace = "(?P<namespace>(?:$path)((?:$path)/){0,19})";

        $base = "(?:https://\Q{$domain}\E/)?";
        $repo = '(?P<repo>[^/]+)';
        $nwo = "${base}(?P<project_path>${namespace}${repo})";
        $line = 'L(?P<line>\d+)';
        $line2 = 'L(?P<line2>\d+)';

        return [
            'blob' => "^${nwo}/blob/(?P<ref>[^/]+)/(?P<file_path>.+?)(?:#${line}(?:-${line2})?)?$",
            'note' => "^${nwo}/(?:issues|merge_requests)/(?P<number>\d+)#note_(?P<id>\d+)",
            'issue' => "^${nwo}/issues/(?P<number>\d+)$",
            'merge_request' => "^${nwo}/merge_requests/(?P<number>\d+)$",
            'account' => "^${base}${namespace}$",
            'project' => "^${nwo}$",
        ];
    }
}