<?php

namespace GitlabSlackUnfurl\Test;

use Gitlab;
use GitlabSlackUnfurl\Route\RouteMatcher;

class RouteTest extends TestCase
{
    /**
     * @param string $route
     * @param string $url
     * @param array $parts
     * @dataProvider routesProvider
     */
    public function testRoutes(string $route, string $url, array $parts)
    {
        $router = new RouteMatcher($this->domain);
        $match = $router->match($url);
        $this->assertEquals($route, $match[0]);
        $this->assertEquals($parts, $match[1]);
    }

    public function routesProvider()
    {
        return [
            ['issue', 'https://gitlab.com/gitlab-org/gitlab-ce/issues/12733', [
                'owner' => 'gitlab-org',
                'repo' => 'gitlab-ce',
                'number' => '12733',
            ]],
            ['repo', 'https://gitlab.com/gitlab-org/gitlab-ce', [
                'owner' => 'gitlab-org',
                'repo' => 'gitlab-ce',
            ]],
            ['account', 'https://gitlab.com/gitlab-org', [
                'owner' => 'gitlab-org',
            ]],
            ['note', 'https://gitlab.com/gitlab-org/gitlab-ce/issues/31422#note_28249314', [
                'owner' => 'gitlab-org',
                'repo' => 'gitlab-ce',
                'number' => '31422',
                'id' => '28249314',
            ]],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/master/README.md', [
                'owner' => 'gitlab-org',
                'repo' => 'gitlab-ce',
                'ref' => 'master',
                'path' => 'README.md',
            ]],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/README.md', [
                'owner' => 'gitlab-org',
                'repo' => 'gitlab-ce',
                'ref' => 'v10.5.4',
                'path' => 'README.md',
            ]],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/README.md#L10', [
                'owner' => 'gitlab-org',
                'repo' => 'gitlab-ce',
                'ref' => 'v10.5.4',
                'path' => 'README.md',
                'line' => '10',
            ]],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/README.md#L19-28', [
                'owner' => 'gitlab-org',
                'repo' => 'gitlab-ce',
                'ref' => 'v10.5.4',
                'path' => 'README.md#L19-28',
            ]],
            ['merge_request', 'https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721', [
                'owner' => 'gitlab-org',
                'repo' => 'gitlab-ce',
                'number' => '6721',
            ]],
            ['note', 'https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721#note_16627667', [
                'owner' => 'gitlab-org',
                'repo' => 'gitlab-ce',
                'number' => '6721',
                'id' => '16627667',
            ]],
        ];
    }
}