<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Event\Handler;

use SimpleBus\Message\Bus\MessageBus;
use Visca\Bot\Component\GitHub\Event\Event\WebHookReceivedEvent;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Cascade\Strategy\Interfaces\MergeCascadingInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Command\MergePullRequestCommand;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Command\RebasePullRequestCommand;

final class OnClosedPullRequestWebHookReceivedEventHandler
{
    /** @var MergeCascadingInterface */
    private $cascadingStrategy;

    /** @var MessageBus */
    private $commandBus;

    public function __construct(MergeCascadingInterface $cascadingStrategy, MessageBus $commandBus)
    {
        $this->cascadingStrategy = $cascadingStrategy;
        $this->commandBus = $commandBus;
    }

    public function onWebHookReceived(WebHookReceivedEvent $event)
    {
        // If it's not a pull_request event, exit
        if (!$this->eventIsStatusPullRequest($event)) {
            return;
        }

        if (!$this->isPullRequestClosed($event)) {
            return;
        }

        if (!$this->isPullRequestMerged($event)) {
            return;
        }

        $nextPullRequest = $this->findNextMergeablePullRequest($event);
        if ($nextPullRequest) {
            $command = new MergePullRequestCommand(
                $event->getPayload()['pull_request']['head']['repo']['owner']['login'],
                $event->getPayload()['pull_request']['head']['repo']['name'],
                $event->getPayload()['pull_request']['number']
            );

            $this->commandBus->handle($command);
        }

        $pullRequestToRebase = $this->findNextMergeableIfRebasedBeforePullRequest($event);
        if ($pullRequestToRebase) {
            $command = new RebasePullRequestCommand(
                $event->getPayload()['pull_request']['head']['repo']['owner']['login'],
                $event->getPayload()['pull_request']['head']['repo']['name'],
                $event->getPayload()['pull_request']['number']
            );

            $this->commandBus->handle($command);
        }
    }

    private function eventIsStatusPullRequest(WebHookReceivedEvent $event)
    {
        return $event->getHeaders()['x-github-event'][0] == 'pull_request';
    }

    private function isPullRequestClosed(WebHookReceivedEvent $event)
    {
        return $event->getPayload()['action'] == 'closed';
    }

    private function isPullRequestMerged(WebHookReceivedEvent $event)
    {
        return $event->getPayload()['pull_request']['merged'];
    }

    private function findNextMergeablePullRequest(WebHookReceivedEvent $event)
    {
        $mergedPullRequest = $event->getPayload()['pull_request'];

        $nextPullRequest = $this
            ->cascadingStrategy
            ->findNextAlreadyMergeablePullRequest(
                $mergedPullRequest['head']['repo']['owner']['login'],
                $mergedPullRequest['head']['repo']['name']
            );

        return $nextPullRequest;
    }

    private function findNextMergeableIfRebasedBeforePullRequest(WebHookReceivedEvent $event)
    {
        $mergedPullRequest = $event->getPayload()['pull_request'];

        $nextPullRequest = $this
            ->cascadingStrategy
            ->findNextMergeablePullRequestIfRebasedBefore(
                $mergedPullRequest['head']['repo']['owner']['login'],
                $mergedPullRequest['head']['repo']['name']
            );

        return $nextPullRequest;
    }
}
