<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Cascade\Strategy;

use Visca\Bot\Component\GitHub\PullRequest\Merging\Cascade\Strategy\Interfaces\MergeCascadingInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces\MergeableResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

final class MergeCascading implements MergeCascadingInterface
{
    /** @var PullRequestMergeRepositoryInterface */
    private $repository;

    /** @var MergeableResolverInterface */
    private $mergeableResolver;

    /** @var MergeableResolverInterface */
    private $mergeableIfRebasedBeforeResolver;

    public function __construct(
        PullRequestMergeRepositoryInterface $repository,
        MergeableResolverInterface $mergeableResolver,
        MergeableResolverInterface $mergeableIfRebasedBeforeResolver
    ) {
        $this->repository = $repository;
        $this->mergeableResolver = $mergeableResolver;
        $this->mergeableIfRebasedBeforeResolver = $mergeableIfRebasedBeforeResolver;
    }

    public function findNextAlreadyMergeablePullRequest($owner, $repo)
    {
        $pullRequestsWithLabel = $this->repository->findWithMergeLabel(
            $owner,
            $repo
        );

        $mergeablePullRequests = array_filter(
            $pullRequestsWithLabel,
            function ($pullRequest) {
                return $this->mergeableResolver->canBeMerged($pullRequest)->isMergeable();
            }
        );

        if (count($mergeablePullRequests) == 0) {
            return null;
        }

        return $mergeablePullRequests[0];
    }

    public function findNextMergeablePullRequestIfRebasedBefore($owner, $repo)
    {
        $pullRequestsWithLabel = $this->repository->findWithMergeLabel(
            $owner,
            $repo
        );

        $mergeablePullRequests = array_filter(
            $pullRequestsWithLabel,
            function ($pullRequest) {
                return $this->mergeableIfRebasedBeforeResolver->canBeMerged($pullRequest)->isMergeable();
            }
        );

        if (count($mergeablePullRequests) == 0) {
            return null;
        }

        return $mergeablePullRequests[0];
    }
}
