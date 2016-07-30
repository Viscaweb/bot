<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Repository;

use Github\Api\Repo;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

/**
 * Class PullRequestMergeRepository
 */
final class PullRequestMergeRepository implements PullRequestMergeRepositoryInterface
{
    /** @var Repo */
    private $api;

    /**
     * StatusesRepository constructor.
     *
     * @param Repo $api
     */
    public function __construct(Repo $api)
    {
        $this->api = $api;
    }

    /**
     * @inheritdoc
     */
    public function merge($username, $repository, $base, $head, $message = null)
    {
        // https://developer.github.com/v3/repos/merging/
        $result = $this->api->merge($username, $repository, $base, $head, $message);

        if (!$result) {
            throw new \Exception("Empty response");
        }

        if (!isset($result['sha'])) {
            if (isset($result['message'])) {
                throw new \Exception($result['message']);
            } else {
                throw new \Exception("Unknown error");
            }
        }
    }
}
