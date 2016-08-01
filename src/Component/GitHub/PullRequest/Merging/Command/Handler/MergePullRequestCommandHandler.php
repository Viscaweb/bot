<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Handler;

use SimpleBus\Message\Bus\MessageBus;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Command\MergePullRequestCommand;
use Visca\Bot\Component\GitHub\PullRequest\Merging\PullRequestMerger;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

final class MergePullRequestCommandHandler
{
    /** @var PullRequestMergeRepositoryInterface */
    private $repository;

    /** @var PullRequestMerger */
    private $merger;

    /** @var MessageBus */
    private $commandBus;

    public function __construct(
        PullRequestMergeRepositoryInterface $repository,
        PullRequestMerger $merger,
        MessageBus $commandBus
    ) {
        $this->repository = $repository;
        $this->merger = $merger;
        $this->commandBus = $commandBus;
    }

    public function handle(MergePullRequestCommand $command)
    {
        $this->mergePullRequest($command);
    }

    private function mergePullRequest(MergePullRequestCommand $command)
    {
        $pullRequest = $this
            ->repository
            ->findById(
                $command->getOwner(),
                $command->getRepo(),
                $command->getId()
            );

        return $this->merger->merge($pullRequest);
    }
}
