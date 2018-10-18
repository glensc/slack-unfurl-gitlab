<?php

namespace GitlabSlackUnfurl\Test;

use GitlabSlackUnfurl\Route;
use GitlabSlackUnfurl\Route\GitLabRoutes;

class UnfurlTest extends TestCase
{
    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @param array $responses
     * @dataProvider issueDataProvider
     */
    public function testIssueUnfurl(string $url, array $parts, array $expected, array $responses): void
    {
        $router = new GitLabRoutes($this->domain);
        $match = $router->match($url)[1];
        $this->assertEquals($match, $parts);

        /** @var Route\Issue $unfurler */
        $unfurler = $this->getRouteHandler(Route\Issue::class, $responses);

        $result = $unfurler->unfurl($url, $parts);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @param array $responses
     * @dataProvider mergeDataProvider
     */
    public function testMergeRequest(string $url, array $parts, array $expected, array $responses): void
    {
        $router = new GitLabRoutes($this->domain);
        $match = $router->match($url)[1];
        $this->assertEquals($match, $parts);

        /** @var Route\MergeRequest $unfurler */
        $unfurler = $this->getRouteHandler(Route\MergeRequest::class, $responses);

        $result = $unfurler->unfurl($url, $parts);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @param array $responses
     * @dataProvider noteDataProvider
     */
    public function testNote(string $url, array $parts, array $expected, array $responses): void
    {
        $router = new GitLabRoutes($this->domain);
        $match = $router->match($url)[1];
        $this->assertEquals($match, $parts);

        /** @var Route\Note $unfurler */
        $unfurler = $this->getRouteHandler(Route\Note::class, $responses);

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
                    'text' => "When a project is private, and you paste it into something like slack, you get the message: \"Sign In\"\r\n" .
                        "\r\n" .
                        "![image](/uploads/861d44489c2de9ef1510b0d44bf8ebc9/image.png)\r\n" .
                        "\r\n" .
                        "This can be solved by disabling the unfurl for the login page.\r\n",
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
                [
                    new MockJsonResponse('GitLab/issue-12733.json'),
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
                    'text' => "## What does this MR do?\n" .
                        "\n" .
                        "Add documentation for gitlab-org/gitlab-shell!93\n\n" .
                        "## Are there points in the code the reviewer needs to double check?\n\n" .
                        "## Why was this MR needed?\n\n" .
                        "## Screenshots (if relevant)\n\n" .
                        "## Does this MR meet the acceptance criteria?\n\n" .
                        "- [x] [CHANGELOG](https://gitlab.com/gitlab-org/gitlab-ce/blob/master/CHANGELOG) entry added\n" .
                        "- [x] [Documentation created/updated](https://gitlab.com/gitlab-org/gitlab-ce/blob/master/doc/development/doc_styleguide.md)\n" .
                        "- [ ] API support added\n" .
                        "- Tests\n" .
                        "  - [ ] Added for this feature/bug\n" .
                        "  - [ ] All builds are passing\n" .
                        "- [ ] Conform by the [merge request performance guides](http://docs.gitlab.com/ce/development/merge_request_performance_guidelines.html)\n" .
                        "- [ ] Conform by the [style guides](https://gitlab.com/gitlab-org/gitlab-ce/blob/master/CONTRIBUTING.md#style-guides)\n" .
                        "- [ ] Branch has no merge conflicts with `master` (if you do - rebase it please)\n" .
                        "- [x] [Squashed related commits together](https://git-scm.com/book/en/Git-Tools-Rewriting-History#Squashing-Commits)\n" .
                        "\n" .
                        '## What are the relevant issue numbers?',
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
                [
                    new MockJsonResponse('GitLab/merge_request-6721.json')
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
                    'type' => 'issues',
                    'repo' => 'gitlab-ce',
                    'number' => '31422',
                    'id' => '28249314',
                ],
                [
                    'title' => '<https://gitlab.com/gitlab-org/gitlab-ce/issues/31422#note_28249314|#31422>: Note on issue #31422: View issue / merge request state in unfurled issue link in Slack',
                    'text' => "@victorwu by default, Slack's proxy servers will make the request to the URL. This means a number of things:\n" .
                        "a) if the server is inaccessible to the general internets, it won't unfurl\n" .
                        "b) if the page requires authentication, it won't unfurl\n" .
                        "\n" .
                        "With a Slack App, you can do [authenticated unfurls](https://api.slack.com/docs/message-link-unfurling#authenticated_unfurls) whereby you listen to an event with a particular URL pattern and then can unfurl the link.\n" .
                        "\n" .
                        "Unfortunately, because the Slack App doesn't have a URL parameter (i.e. you can't tell it that my GitHub instance is https://job.gitlap.com/*) this means that self-hosted GitLab instances will need manual App configuration. Slack are looking into what can be done about this in the future.\n" .
                        "\n" .
                        "For now, we can certainly improve the unfurls for GitLab.com and also provide private issues with authenticated unfurls on GitLab.com. We can also use unfurl events to make links [interactive](https://api.slack.com/docs/message-link-unfurling#interactive) like this:\n" .
                        "\n" .
                        '![app_unfurls_buttons](/uploads/729df963fe3a2e43e5ec6ff449808184/app_unfurls_buttons.png)',
                    'color' => '#E24329',
                    'ts' => 1493192433,
                    'footer' => 'Created by <https://gitlab.com/mydigitalself|Mike Bartlett>',
                    'fields' => [],
                ],
                [
                    new MockJsonResponse('GitLab/issue-31422.json'),
                    new MockJsonResponse('GitLab/note.json'),
                ],
            ],
            [
                'https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721#note_19288529',
                [
                    'namespace' => 'gitlab-org/',
                    'project_path' => 'gitlab-org/gitlab-ce',
                    'type' => 'merge_requests',
                    'repo' => 'gitlab-ce',
                    'number' => '6721',
                    'id' => '19288529',
                ],
                [
                    'title' => '<https://gitlab.com/gitlab-org/gitlab-ce/merge_requests/6721#note_19288529|#6721>: Note on merge request #6721: Update custom_hooks.md for chained hooks support',
                    'text' => "@glensc I've got some grammar nitpicks, but otherwise this is fine by me! I can fix those in a separate branch if you're done with this.",
                    'color' => '#E24329',
                    'ts' => 1480594445,
                    'footer' => 'Created by <https://gitlab.com/smcgivern|Sean McGivern>',
                    'fields' => [],
                ],
                [
                    new MockJsonResponse('GitLab/notes-6721.json'),
                    new MockJsonResponse('GitLab/merge_request-6721.json'),
                ],
            ],
        ];
    }
}
