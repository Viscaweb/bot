<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Event\Handler;

use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Visca\Bot\Component\GitHub\Event\Event\WebHookReceivedEvent;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Command\MergePullRequestCommand;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

final class OnLabelPullRequestWebHookReceivedEventHandler
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
        // If it's not a pull_request event, exit
        if (!$this->eventIsStatusPullRequest($event)) {
            return;
        }

        // If it's not a label action, exit
        if (!$this->eventIsLabeledAction($event)) {
            return;
        }

        $commands = $this->createCommands($event);

        foreach ($commands as $command) {
            $this->commandBus->handle($command);
        }
    }

    private function eventIsStatusPullRequest(WebHookReceivedEvent $event)
    {
        return $event->getHeaders()['x-github-event'][0] == 'pull_request';
    }

    private function eventIsLabeledAction(WebHookReceivedEvent $event)
    {
        return $event->getPayload()['action'] == 'labeled';
    }

    private function createCommands(WebHookReceivedEvent $event)
    {
        $commands = [];

        $pullRequest = $event->getPayload()['pull_request'];

        $command = new MergePullRequestCommand(
            $pullRequest['head']['repo']['owner']['login'],
            $pullRequest['head']['repo']['name'],
            $pullRequest['number']
        );

        $commands[] = $command;

        return $commands;
    }
}
