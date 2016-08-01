<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable;

use Github\Api\Issue;
use Psr\Log\LoggerInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces\MergeableResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Verdict\MergeableVerdict;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

final class MergeableIfTagged implements MergeableResolverInterface
{
    const LABEL_NAME = 'merge-asap';

    /** @var PullRequestMergeRepositoryInterface */
    private $pullRequestMergeRepository;

    /** @var Issue */
    private $issueAPI;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        PullRequestMergeRepositoryInterface $pullRequestMergeRepository,
        Issue $issueAPI,
        LoggerInterface $logger
    ) {
        $this->pullRequestMergeRepository = $pullRequestMergeRepository;
        $this->issueAPI = $issueAPI;
        $this->logger = $logger;
    }

    public function canBeMerged(array $pullRequest)
    {
        $issue = $this->issueAPI->show(
            $pullRequest['head']['repo']['owner']['login'],
            $pullRequest['head']['repo']['name'],
            $pullRequest['number']
        );

        if (!$issue) {
            throw new \Exception(sprintf('Can not find issue with number %d', $pullRequest['number']));
        }

        $hasLabel = $this->hasMergeASAPLabel($issue);

        if (!$hasLabel) {
            $this->logger->info(
                sprintf(
                    'PR %d for %s/%s is not mergeable because the issue does not have the expected label',
                    $pullRequest['number'],
                    $pullRequest['head']['repo']['owner']['login'],
                    $pullRequest['head']['repo']['name']
                )
            );

            return new MergeableVerdict(false, $this);
        }

        return new MergeableVerdict(true, $this);
    }

    private function hasMergeASAPLabel(array $issue)
    {
        foreach ($issue['labels'] as $label) {
            if ($label['name'] == self::LABEL_NAME) {
                return true;
            }
        }

        return false;
    }
}
