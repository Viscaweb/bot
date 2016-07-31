<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Verdict;

use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces\MergeableResolverInterface;

final class MergeableVerdict
{
    /** @var bool */
    private $mergeable;

    /** @var MergeableResolverInterface */
    private $resolver;

    public function __construct($mergeable, MergeableResolverInterface $resolver)
    {
        $this->mergeable = $mergeable;
        $this->resolver = $resolver;
    }

    public function isMergeable(): bool
    {
        return $this->mergeable;
    }

    public function getResolver(): MergeableResolverInterface
    {
        return $this->resolver;
    }
}
