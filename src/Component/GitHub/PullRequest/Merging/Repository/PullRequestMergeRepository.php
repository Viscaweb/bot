<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Repository;

use Github\Api\Issue;
use Github\Api\PullRequest;
use Github\Api\Repo;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\MergeableIfTagged;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

/**
 * Class PullRequestMergeRepository
 */
final class PullRequestMergeRepository implements PullRequestMergeRepositoryInterface
{
    /** @var Repo */
    private $repoAPI;

    /** @var PullRequest */
    private $pullRequestAPI;

    /** @var Issue */
    private $issuesAPI;

    public function __construct(Repo $repoAPI, PullRequest $pullRequestAPI, Issue $issueAPI)
    {
        $this->repoAPI = $repoAPI;
        $this->pullRequestAPI = $pullRequestAPI;
        $this->issuesAPI = $issueAPI;
    }

    /**
     * @inheritdoc
     */
    public function merge($username, $repository, $base, $head, $message = null)
    {
        // https://developer.github.com/v3/repos/merging/
        $result = $this->repoAPI->merge($username, $repository, $base, $head, $message);

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

    public function findByCommitHash($username, $repository, $hash)
    {
        $pullRequests = $this->pullRequestAPI->all($username, $repository, ['head' => $hash]);

        return $pullRequests;
    }

    public function findWithMergeLabel($username, $repository)
    {
        $issues = $this->issuesAPI->all($username, $repository, ['labels' => MergeableIfTagged::LABEL_NAME]);

        $pullRequests = [];

        foreach ($issues as $issue) {
            $pullRequests[] = $this->findById($username, $repository, $issue['number']);
        }

        return $pullRequests;
    }

    public function findById($username, $repository, $id)
    {
        $pullRequest = $this->pullRequestAPI->show($username, $repository, $id);

        return $pullRequest;
    }
}
