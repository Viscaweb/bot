<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces;

/**
 * Interface PullRequestMergeRepositoryInterface
 */
interface PullRequestMergeRepositoryInterface
{
    /**
     * @param string      $username
     * @param string      $repository
     * @param string      $base
     * @param string      $head
     * @param string|null $message
     *
     * @throws \Exception if merge can not be done
     */
    public function merge($username, $repository, $base, $head, $message = null);
}
