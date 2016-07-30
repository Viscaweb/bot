<?php
namespace Visca\Bot\Component\GitHub\Repositories\Interfaces;

/**
 * Interface StatusesRepositoryInterface
 */
interface StatusesRepositoryInterface
{
    /**
     * @param string $username
     * @param string $repository
     * @param string $sha
     *
     * @return array cf https://developer.github.com/v3/repos/statuses/
     */
    public function get($username, $repository, $sha);
}
