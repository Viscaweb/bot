<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable;

use Psr\Log\LoggerInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces\MergeableResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Verdict\MergeableVerdict;

final class MergeableIfNotAlreadyMergedAndMergeable implements MergeableResolverInterface
{
    const EXPECTED_MERGEABLE_STATE = 'clean';

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function canBeMerged(array $pullRequest)
    {
        if ($pullRequest['merged']) {
            $this->logger->info(
                sprintf(
                    'PR %d for %s/%s is not mergeable because it is already merged',
                    $pullRequest['number'],
                    $pullRequest['head']['repo']['owner']['login'],
                    $pullRequest['head']['repo']['name']
                )
            );

            return new MergeableVerdict(false, $this);

        }

        if (!$pullRequest['mergeable']) {
            $this->logger->info(
                sprintf(
                    'PR %d for %s/%s is not mergeable because GitHub say it\'s not...',
                    $pullRequest['number'],
                    $pullRequest['head']['repo']['owner']['login'],
                    $pullRequest['head']['repo']['name']
                )
            );

            return new MergeableVerdict(false, $this);
        }

        if ($pullRequest['mergeable_state'] != self::EXPECTED_MERGEABLE_STATE) {
            $this->logger->info(
                sprintf(
                    'PR %d for %s/%s is not mergeable because it\'s mergeable_state is "%s" instead of "%s"',
                    $pullRequest['number'],
                    $pullRequest['head']['repo']['owner']['login'],
                    $pullRequest['head']['repo']['name'],
                    $pullRequest['mergeable_state'],
                    self::EXPECTED_MERGEABLE_STATE
                )
            );

            return new MergeableVerdict(false, $this);
        }

        return new MergeableVerdict(true, $this);
    }
}
