<?php

namespace GitlabSlackUnfurl;

class Routes
{
    /** @var string */
    private $domain;
    /** @var array */
    private $routes;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    public function match(string $url)
    {
        foreach ($this->getRoutes() as $route => $pattern) {
            if (preg_match("!{$pattern}!", $url, $matches)) {
                return [$route, $matches];
            }
        }

        return null;
    }

    private function getRoutes()
    {
        return $this->routes = $this->routes ?: $this->buildRoutes($this->domain);
    }

    /**
     * @param string $domain
     * @return array
     * @see https://github.com/integrations/slack/blob/d9fc0648c2a0158bf4bf32cf527c5675604501d4/lib/github-url.js
     */
    private function buildRoutes($domain = 'gitlab.com')
    {
        $base = "(?:https://\Q{$domain}\E/)?";
        $owner = '(?P<owner>[^/]+)';
        $repo = '(?P<repo>[^/]+)';
        $nwo = "${base}${owner}/${repo}";
        $line = 'L(?P<line>\d+)';
        $line2 = 'L(?P<line2>\d+)';

        return [
            'blob' => "^${nwo}/blob/(?P<ref>[^/]+)/(?P<path>.+?)(?:#${line}(?:-${line2})?)?$",
            'note' => "^${nwo}/(?:issues|merge_requests)/(?P<number>\d+)#note_(?P<id>\d+)",
            'issue' => "^${nwo}/issues/(?P<number>\d+)$",
            'merge_request' => "^${nwo}/merge_requests/(?P<number>\d+)$",
            'repo' => "^${nwo}$",
            'account' => "^${base}${owner}$",
        ];
    }
}