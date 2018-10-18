# Slack unfurl GitLab Provider

GitLab links unfurler for [slack-unfurl].

[slack-unfurl]: https://github.com/glensc/slack-unfurl

## Installation

1. Install [slack-unfurl]
2. Require this package: `composer require glen/slack-unfurl-gitlab`
3. Merge `env.example` from this project to `.env`
4. Register provider: in `src/Application.php` add `$this->register(new \GitlabSlackUnfurl\ServiceProvider\GitlabUnfurlServiceProvider());`

[slack-unfurl]: https://github.com/glensc/slack-unfurl

## Supported URL handlers

- `issue`
- `merge_request`
- issue or merge request `note` (since 0.6.0)

Technical details:
- route matches are defined in [src/Route/GitLabRoutes.php::buildRoutes()](src/Route/GitLabRoutes.php)
- handlers are defined in [src/Event/Subscriber/GitlabUnfurler.php::ROUTES](src/Event/Subscriber/GitlabUnfurler.php)

For url to be unfurled, url pattern must be defined in `GitLabRoutew`, and handler must be also present in `GitlabUnfurler`.
