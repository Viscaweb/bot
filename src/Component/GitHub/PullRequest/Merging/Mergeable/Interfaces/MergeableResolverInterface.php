<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces;

use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Verdict\MergeableVerdict;

/**
 * Interface MergeableResolverInterface
 */
interface MergeableResolverInterface
{
    /**
     * @param array $pullRequest (https://developer.github.com/v3/pulls/)
     *
     * @return MergeableVerdict
     */
    public function canBeMerged(array $pullRequest);
}
