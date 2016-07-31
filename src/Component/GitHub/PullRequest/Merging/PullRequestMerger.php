<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging;

use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Command\RebasePullRequestCommand;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces\MergeableResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\MergeableIfNotBehindBaseBranch;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

/**
 * Class PullRequestMerger
 */
final class PullRequestMerger
{
    /** @var MergeableResolverInterface */
    private $mergeableResolver;

    /** @var PullRequestMergeRepositoryInterface */
    private $pullRequestMergeRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var MessageBus */
    private $commandBus;

    public function __construct(
        MergeableResolverInterface $mergeableResolver,
        PullRequestMergeRepositoryInterface $pullRequestMergeRepository,
        MessageBus $commandBus,
        LoggerInterface $logger
    ) {
        $this->mergeableResolver = $mergeableResolver;
        $this->pullRequestMergeRepository = $pullRequestMergeRepository;
        $this->commandBus = $commandBus;
        $this->logger = $logger;
    }

    /**
     * @param array $pullRequest
     *
     * @return bool true if the PR has been merged or false if the PR could not be merged
     */
    public function merge(array $pullRequest)
    {
        $verdict = $this->mergeableResolver->canBeMerged($pullRequest);

        if (!$verdict->isMergeable()) {
            $this
                ->logger
                ->info(
                    sprintf(
                        'Pull request %d of %s/%s is not mergeable',
                        $pullRequest['number'],
                        $pullRequest['head']['repo']['owner']['login'],
                        $pullRequest['head']['repo']['name']
                    )
                );

            // Detect if it could not be merged because not rebased
            if (get_class($verdict->getResolver()) == MergeableIfNotBehindBaseBranch::class) {
                $this
                    ->logger
                    ->info(
                        sprintf(
                            'Pull request %d of %s/%s is not mergeable but if we rebase it maybe it can be merged',
                            $pullRequest['number'],
                            $pullRequest['head']['repo']['owner']['login'],
                            $pullRequest['head']['repo']['name']
                        )
                    );

                $this->sendRebasePullRequestCommand($pullRequest);
            }

            return false;
        }

        $this->pullRequestMergeRepository->merge(
            $pullRequest['head']['repo']['owner']['login'],
            $pullRequest['head']['repo']['name'],
            $pullRequest['base']['ref'],
            $pullRequest['head']['sha']
        );

        $this
            ->logger
            ->info(
                sprintf(
                    'Pull request %d of %s/%s is merged',
                    $pullRequest['number'],
                    $pullRequest['head']['repo']['owner']['login'],
                    $pullRequest['head']['repo']['name']
                )
            );

        return true;
    }

    private function sendRebasePullRequestCommand(array $pullRequest)
    {
        $command = new RebasePullRequestCommand(
            $pullRequest['head']['repo']['owner']['login'],
            $pullRequest['head']['repo']['name'],
            $pullRequest['number']
        );

        $this->commandBus->handle($command);
    }
}
