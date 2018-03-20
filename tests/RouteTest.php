<?php

namespace GitlabSlackUnfurl\Test;

use Gitlab;
use GitlabSlackUnfurl\Route\GitLabRoutes;

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
        $router = new GitLabRoutes($this->domain);
        $match = $router->match($url);
        $this->assertEquals($route, $match[0]);
        $this->assertEquals($parts, $match[1]);
    }

    public function routesProvider()
    {
        return [
            ['issue', 'https://gitlab.com/gitlab-org/gitlab-ce/issues/12733', [
                'namespace' => 'gitlab-org/',
                'project_path' => 'gitlab-org/gitlab-ce',
                'repo' => 'gitlab-ce',
                'number' => '12733',
            ]],
            ['project', 'https://gitlab.com/gitlab-org/gitlab-ce', [
                'namespace' => 'gitlab-org/',
                'project_path' => 'gitlab-org/gitlab-ce',
                'repo' => 'gitlab-ce',
            ]],
            ['account', 'https://gitlab.com/gitlab-org', [
                'namespace' => 'gitlab-org',
            ]],
            ['note', 'https://gitlab.com/gitlab-org/gitlab-ce/issues/31422#note_28249314', [
                'namespace' => 'gitlab-org/',
                'project_path' => 'gitlab-org/gitlab-ce',
                'repo' => 'gitlab-ce',
                'number' => '31422',
                'id' => '28249314',
            ]],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/master/README.md', [
                'namespace' => 'gitlab-org/',
                'project_path' => 'gitlab-org/gitlab-ce',
                'repo' => 'gitlab-ce',
                'ref' => 'master',
                'file_path' => 'README.md',
            ]],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/README.md', [
                'namespace' => 'gitlab-org/',
                'project_path' => 'gitlab-org/gitlab-ce',
                'repo' => 'gitlab-ce',
                'ref' => 'v10.5.4',
                'file_path' => 'README.md',
            ]],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/README.md#L10', [
                'namespace' => 'gitlab-org/',
                'project_path' => 'gitlab-org/gitlab-ce',
                'repo' => 'gitlab-ce',
                'ref' => 'v10.5.4',
                'file_path' => 'README.md',
                'line' => '10',
            ]],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/README.md#L19-28', [
                'namespace' => 'gitlab-org/',
                'project_path' => 'gitlab-org/gitlab-ce',
                'repo' => 'gitlab-ce',
                'ref' => 'v10.5.4',
                'file_path' => 'README.md#L19-28',
            ]],
            ['merge_request', 'https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721', [
                'namespace' => 'gitlab-org/',
                'project_path' => 'gitlab-org/gitlab-ce',
                'repo' => 'gitlab-ce',
                'number' => '6721',
            ]],
            ['note', 'https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721#note_16627667', [
                'namespace' => 'gitlab-org/',
                'project_path' => 'gitlab-org/gitlab-ce',
                'repo' => 'gitlab-ce',
                'number' => '6721',
                'id' => '16627667',
            ]],
        ];
    }
}