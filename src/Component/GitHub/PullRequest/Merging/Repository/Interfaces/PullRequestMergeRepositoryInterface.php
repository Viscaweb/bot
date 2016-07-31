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

    /**
     * @param string $username
     * @param string $repository
     * @param string $hash
     *
     * @return array of pull requests (https://developer.github.com/v3/pulls/#list-pull-requests)
     */
    public function findByCommitHash($username, $repository, $hash);

    /**
     * @param string $username
     * @param string $repository
     * @param int    $id
     *
     * @return array one pull request (https://developer.github.com/v3/pulls/#list-pull-requests)
     */
    public function findById($username, $repository, $id);

    /**
     * @param string $username
     * @param string $repository
     *
     * @return array one pull request (https://developer.github.com/v3/pulls/#list-pull-requests)
     */
    public function findWithMergeLabel($username, $repository);
}
