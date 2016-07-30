<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Mergability\Interfaces;

/**
 * Interface MergabilityResolverInterface
 */
interface MergabilityResolverInterface
{
    /**
     * @param string $username
     * @param string $repository
     * @param string $sha
     *
     * @return bool
     */
    public function canBeMerged($username, $repository, $sha);
}
