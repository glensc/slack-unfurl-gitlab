<?php

namespace GitlabSlackUnfurl\Test;

use GitlabSlackUnfurl\Route;

class UnfurlTest extends TestCase
{
    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @dataProvider issueDataProvider
     */
    public function testIssueUnfurl(string $url, array $parts, array $expected): void
    {
        $unfurler = $this->app[Route\Issue::class];
        $result = $unfurler->unfurl($url, $parts);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @dataProvider mergeDataProvider
     */
    public function testMergeRequest(string $url, array $parts, array $expected): void
    {
        $unfurler = $this->app[Route\MergeRequest::class];
        $result = $unfurler->unfurl($url, $parts);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @dataProvider noteDataProvider
     */
    public function testNote(string $url, array $parts, array $expected): void
    {
        /** @var Route\Note $unfurler */
        $unfurler = $this->app[Route\Note::class];
        $result = $unfurler->unfurl($url, $parts);
        $this->assertEquals($expected, $result);
    }

    public function issueDataProvider(): array
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

    public function mergeDataProvider(): array
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

    public function noteDataProvider(): array
    {
        return [
            [
                'https://gitlab.com/gitlab-org/gitlab-ce/issues/31422#note_28249314',
                [
                    'namespace' => 'gitlab-org/',
                    'project_path' => 'gitlab-org/gitlab-ce',
                    'repo' => 'gitlab-ce',
                    'number' => '31422',
                    'id' => '28249314',
                ],
                [
                    'title' => '<https://gitlab.com/gitlab-org/gitlab-ce/issues/31422#note_28249314|#31422>: Note 28249314 for 31422',
                    'color' => '#E24329',
                    'ts' => 1493192433,
                    'footer' => 'Created by <https://gitlab.com/mydigitalself|Mike Bartlett>',
                    'fields' => [],
                ],
            ],
        ];
    }
}