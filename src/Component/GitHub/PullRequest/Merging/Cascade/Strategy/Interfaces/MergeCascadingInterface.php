<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Cascade\Strategy\Interfaces;

/**
 * This interface define a strategy about how to find next PR to merge
 * after the previous one has been successfully merged
 */
interface MergeCascadingInterface
{
    /**
     * @param string $owner
     * @param string $repo
     *
     * @return array|null next pull request to merge or null if there are none
     */
    public function findNextAlreadyMergeablePullRequest($owner, $repo);

    /**
     * @param string $owner
     * @param string $repo
     *
     * @return array|null next pull request to merge or null if there are none
     */
    public function findNextMergeablePullRequestIfRebasedBefore($owner, $repo);
}
