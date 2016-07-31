<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable;

use Github\Api\Repo;
use Psr\Log\LoggerInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces\MergeableResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Verdict\MergeableVerdict;

final class MergeableIfNotBehindBaseBranch implements MergeableResolverInterface
{
    /** @var Repo */
    private $repoAPI;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(Repo $repoAPI, LoggerInterface $logger)
    {
        $this->repoAPI = $repoAPI;
        $this->logger = $logger;
    }

    public function canBeMerged(array $pullRequest)
    {
        $comparison = $this
            ->repoAPI
            ->commits()
            ->compare(
                $pullRequest['head']['repo']['owner']['login'],
                $pullRequest['head']['repo']['name'],
                $pullRequest['base']['ref'],
                $pullRequest['head']['ref']
            );

        // If the head branch is behind the base branch, it need to be rebased first
        if ($comparison['behind_by'] > 0) {
            $this->logger->info(
                sprintf(
                    'PR %d for %s/%s is not mergeable because branch "%s" is %d commits behind "%s"',
                    $pullRequest['number'],
                    $pullRequest['head']['repo']['owner']['login'],
                    $pullRequest['head']['repo']['name'],
                    $pullRequest['head']['ref'],
                    $comparison['behind_by'],
                    $pullRequest['base']['ref']
                )
            );

            return new MergeableVerdict(false, $this);
        }

        return new MergeableVerdict(true, $this);
    }
}
