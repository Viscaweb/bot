<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable;

use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces\MergeableResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Verdict\MergeableVerdict;

final class MergeableResolver implements MergeableResolverInterface
{
    /** @var MergeableResolverInterface[] */
    private $mergeableResolvers;

    public function __construct(array $mergeableResolvers)
    {
        $this->mergeableResolvers = $mergeableResolvers;
    }

    public function canBeMerged(array $pullRequest)
    {
        foreach ($this->mergeableResolvers as $mergeableResolver) {
            if (!$mergeableResolver->canBeMerged($pullRequest)->isMergeable()) {
                return new MergeableVerdict(false, $mergeableResolver);
            }
        }

        return new MergeableVerdict(true, $this);
    }
}
