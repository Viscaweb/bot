<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Event\Handler;

use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Visca\Bot\Component\GitHub\Event\Event\WebHookReceivedEvent;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Command\MergePullRequestCommand;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

final class OnStatusWebHookReceivedEventHandler
{
    /** @var MessageBus */
    private $commandBus;

    /** @var PullRequestMergeRepositoryInterface */
    private $pullRequestMergeRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        MessageBus $commandBus,
        PullRequestMergeRepositoryInterface $pullRequestMergeRepository,
        LoggerInterface $logger
    ) {
        $this->commandBus = $commandBus;
        $this->pullRequestMergeRepository = $pullRequestMergeRepository;
        $this->logger = $logger;
    }

    public function onWebHookReceived(WebHookReceivedEvent $event)
    {
        // If it's not a status event, exit
        if (!$this->eventIsStatusEvent($event)) {
            return;
        }

        // We can merge only if all state are success
        if (!$this->eventIsSuccess($event)) {
            return;
        }

        $commands = $this->createCommands($event);

        foreach ($commands as $command) {
            $this->commandBus->handle($command);
        }
    }

    private function eventIsStatusEvent(WebHookReceivedEvent $event)
    {
        return $event->getHeaders()['x-github-event'][0] == 'status';
    }

    private function eventIsSuccess(WebHookReceivedEvent $event)
    {
        return $event->getPayload()['state'] == 'success';
    }

    private function createCommands(WebHookReceivedEvent $event)
    {
        $commands = [];

        // We need to find all the PR for this commit
        $username = $event->getPayload()['repository']['owner']['login'];
        $repo = $event->getPayload()['repository']['name'];
        $hash = $event->getPayload()['commit']['sha'];

        $pullRequests = $this
            ->pullRequestMergeRepository
            ->findByCommitHash($username, $repo, $hash);

        foreach ($pullRequests as $pullRequest) {
            $command = new MergePullRequestCommand(
                $username,
                $repo,
                $pullRequest['number']
            );

            $commands[] = $command;
        }

        return $commands;
    }
}
