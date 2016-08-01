<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Handler;

use Github\Api\PullRequest;
use Github\Api\Repo;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Command\RebasePullRequestCommand;

final class RebasePullRequestCommandHandler
{
    /** @var Repo */
    private $repoAPI;

    /** @var PullRequest */
    private $pullRequestAPI;

    public function __construct(Repo $repoAPI, PullRequest $pullRequestAPI)
    {
        $this->repoAPI = $repoAPI;
        $this->pullRequestAPI = $pullRequestAPI;
    }

    public function handle(RebasePullRequestCommand $command)
    {
        $pullRequest = $this->pullRequestAPI->show(
            $command->getOwner(),
            $command->getRepo(),
            $command->getId()
        );

        $this->repoAPI->merge(
            $pullRequest['head']['repo']['owner']['login'],
            $pullRequest['head']['repo']['name'],
            $pullRequest['head']['ref'],
            $pullRequest['base']['ref'],
            sprintf("Merge branch '%s' into %s", $pullRequest['base']['ref'], $pullRequest['head']['ref'])
        );
    }
}
