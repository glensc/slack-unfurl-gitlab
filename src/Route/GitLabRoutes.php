<?php

namespace GitlabSlackUnfurl\Route;

use SlackUnfurl\Route\RouteMatcher;

class GitLabRoutes extends RouteMatcher
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
        // https://gitlab.com/gitlab-org/gitlab/-/blob/v12.7.8-ee/lib/gitlab/path_regex.rb#L129
        $PATH_START_CHAR = '[a-zA-Z0-9_\.]';
        $PATH_REGEX_STR = $PATH_START_CHAR . '[a-zA-Z0-9_\-\.]*';
        $NAMESPACE_FORMAT_REGEX_JS = $PATH_REGEX_STR . '[a-zA-Z0-9_\-]|[a-zA-Z0-9_]';

        // subgroups up to 20 levels
        // https://docs.gitlab.com/ce/user/group/subgroups/index.html
        // https://gitlab.com/gitlab-org/gitlab/-/blob/v12.7.8-ee/app/models/namespace.rb#L18
        $NUMBER_OF_ANCESTORS_ALLOWED = 20;

        // NOTE: backref is invalid syntax for php, so leave $NO_SUFFIX_REGEX empty
        $NO_SUFFIX_REGEX = '';
        $NAMESPACE_FORMAT_REGEX = "(?:{$NAMESPACE_FORMAT_REGEX_JS}){$NO_SUFFIX_REGEX}";
        $FULL_NAMESPACE_FORMAT_REGEX = "(?P<namespace>$NAMESPACE_FORMAT_REGEX/){0,$NUMBER_OF_ANCESTORS_ALLOWED}(?P<repo>{$NAMESPACE_FORMAT_REGEX})";

        $base = "(?:https://\Q{$domain}\E/)?";
        $nwo = "${base}(?P<project_path>{$FULL_NAMESPACE_FORMAT_REGEX})";
        $line = 'L(?P<line>\d+)';
        $line2 = 'L(?P<line2>\d+)';

        return [
            'blob' => "^${nwo}/(?:-/)?blob/(?P<ref>[^/]+)/(?P<file_path>.+?)(?:#${line}(?:-${line2})?)?$",
            'note' => "^${nwo}/(?:-/)?(?P<type>issues|merge_requests|commit)/(?P<number>[a-f\d]+)#note_(?P<id>\d+)",
            'issue' => "^${nwo}/(?:-/)?issues/(?P<number>\d+)$",
            'merge_request' => "^${nwo}/(?:-/)?merge_requests/(?P<number>\d+)(/(?P<view>commits|pipelines|diffs))?$",
            'account' => "^${base}(?P<account>{$NAMESPACE_FORMAT_REGEX})$",
            'project' => "^${nwo}$",
        ];
    }
}
