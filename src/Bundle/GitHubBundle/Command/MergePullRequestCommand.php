<?php
namespace Visca\Bot\Bundle\GitHubBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Command\MergePullRequestCommand as BusMergePullRequestCommand;

final class MergePullRequestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('github:pr:merge')
            ->addArgument('owner', InputArgument::REQUIRED)
            ->addArgument('repo', InputArgument::REQUIRED)
            ->addArgument('pr', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $command = new BusMergePullRequestCommand(
            $input->getArgument('owner'),
            $input->getArgument('repo'),
            $input->getArgument('pr')
        );

        $this
            ->getContainer()
            ->get('asynchronous_command_bus')
            ->handle($command);
    }
}
