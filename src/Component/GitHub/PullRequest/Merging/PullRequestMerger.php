<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging;

use Visca\Bot\Component\GitHub\PullRequest\Merging\Exceptions\PullRequestCanNotBeMergedException;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergability\Interfaces\MergabilityResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

/**
 * Class PullRequestMerger
 */
final class PullRequestMerger
{
    /** @var MergabilityResolverInterface */
    private $mergabilityResolver;

    /** @var PullRequestMergeRepositoryInterface */
    private $pullRequestMergeRepository;

    public function __construct(
        MergabilityResolverInterface $mergabilityResolver,
        PullRequestMergeRepositoryInterface $pullRequestMergeRepository
    ) {
        $this->mergabilityResolver = $mergabilityResolver;
        $this->pullRequestMergeRepository = $pullRequestMergeRepository;
    }

    /**
     * @param string      $username
     * @param string      $repository
     * @param string      $base
     * @param string      $head
     * @param string|null $message
     *
     * @throws PullRequestCanNotBeMergedException if the pull request can not be merged
     */
    public function merge($username, $repository, $base, $head, $message = null)
    {
        if (!$this->mergabilityResolver->canBeMerged($username, $repository, $head)) {
            throw new PullRequestCanNotBeMergedException(sprintf('Commit %s is not mergable', $head));
        }

        $this->pullRequestMergeRepository->merge($username, $repository, $base, $head, $message);
    }
}
