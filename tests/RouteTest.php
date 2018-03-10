<?php

namespace GitlabSlackUnfurl\Test;

use Gitlab;
use GitlabSlackUnfurl\Routes;

class RouteTest extends TestCase
{
    /**
     * @param string $route
     * @param string $url
     * @dataProvider routesProvider
     */
    public function testRoutes($route, $url)
    {
        $router = new Routes($this->domain);
        $match = $router->match($url);
        $this->assertEquals($route, $match[0]);
    }

    public function routesProvider()
    {
        return [
            ['issue', 'https://gitlab.com/gitlab-org/gitlab-ce/issues/12733'],
            ['repo', 'https://gitlab.com/gitlab-org/gitlab-ce'],
            ['account', 'https://gitlab.com/gitlab-org'],
            ['note', 'https://gitlab.com/gitlab-org/gitlab-ce/issues/31422#note_28249314'],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/master/README.md'],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/README.md'],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/README.md#L10'],
            ['blob', 'https://gitlab.com/gitlab-org/gitlab-ce/blob/v10.5.4/README.md#L19-28'],
            ['merge_request', 'https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721'],
            ['note', 'https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721#note_16627667'],
        ];
    }
}