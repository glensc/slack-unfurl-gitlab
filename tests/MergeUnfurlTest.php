<?php

namespace GitlabSlackUnfurl\Test;

use GitlabSlackUnfurl\Route\MergeRequest;

class MergeUnfurlTest extends TestCase
{
    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @dataProvider dataProvider
     */
    public function testMergeRequest(string $url, array $parts, array $expected): void
    {
        $unfurler = $this->app[MergeRequest::class];
        $result = $unfurler->unfurl($url, $parts);
        $this->assertEquals($expected, $result);
    }

    public function dataProvider()
    {
        return [
            [
                'https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721/commits',
                [
                    'namespace' => 'gitlab-org/',
                    'project_path' => 'gitlab-org/gitlab-ce',
                    'repo' => 'gitlab-ce',
                    'number' => '6721',
                ],
                [
                    'title' => '<https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721/commits|#6721>: Update custom_hooks.md for chained hooks support',
                    'color' => '#E24329',
                    'ts' => 1475776449,
                    'footer' => 'Created by <https://gitlab.com/glensc|Elan Ruusamäe>',
                    'fields' => [
                        [
                            'title' => 'Assignee',
                            'value' => '<https://gitlab.com/smcgivern|Sean McGivern>',
                            'short' => true,
                        ],
                        [
                            'title' => 'Labels',
                            'value' => 'Community contribution, Documentation',
                            'short' => true,
                        ],
                        [
                            'title' => 'Milestone',
                            'value' => '8.15',
                            'short' => true,
                        ],
                    ],
                ],
            ],
        ];
    }
}