<?php
namespace Visca\Bot\Bundle\GitHubBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FindNextMergeablePullRequestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('github:pr:mergeable:find')
            ->addArgument('owner', InputArgument::REQUIRED)
            ->addArgument('repo', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pullRequest = $this
            ->getContainer()
            ->get('visca.bot.component.git_hub.pull_request.merging.cascade.merge_cascading')
            ->findNextAlreadyMergeablePullRequest(
                $input->getArgument('owner'),
                $input->getArgument('repo')
            );

        if ($pullRequest) {
            $output->writeln(sprintf('The PR with number "%d" is mergeable', $pullRequest['number']));

            return;
        }

        $pullRequest = $this
            ->getContainer()
            ->get('visca.bot.component.git_hub.pull_request.merging.cascade.merge_cascading')
            ->findNextMergeablePullRequestIfRebasedBefore(
                $input->getArgument('owner'),
                $input->getArgument('repo')
            );

        if ($pullRequest) {
            $output->writeln(sprintf('The PR with number "%d" is mergeable if rebased before', $pullRequest['number']));

            return;
        }

        $output->writeln('No mergeable PR');
    }
}
