<?php

namespace GitlabSlackUnfurl\Test;

use GitlabSlackUnfurl\Route;

class IssueUnfurlTest extends TestCase
{
    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @dataProvider dataProvider
     */
    public function testIssueUnfurl(string $url, array $parts, array $expected): void
    {
        $unfurler = $this->app[Route\Issue::class];
        $result = $unfurler->unfurl($url, $parts);
        $this->assertEquals($expected, $result);
    }

    public function dataProvider()
    {
        return [
            [
                'https://gitlab.com/gitlab-org/gitlab-ce/issues/12733',
                [
                    'namespace' => 'gitlab-org/',
                    'project_path' => 'gitlab-org/gitlab-ce',
                    'repo' => 'gitlab-ce',
                    'number' => '12733',
                ],
                [
                    'title' => '<https://gitlab.com/gitlab-org/gitlab-ce/issues/12733|#12733>: Disable Unfurling for the login page',
                    'color' => '#E24329',
                    'ts' => 1453816324,
                    'footer' => 'Created by <https://gitlab.com/jvanbaarsen|Jeroen van Baarsen>',
                    'fields' => [
                        [
                            'title' => 'Labels',
                            'value' => 'Platform, external services, feature proposal',
                            'short' => true,
                        ],
                    ],
                ],
            ],
        ];
    }
}