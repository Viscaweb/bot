<?php
namespace Visca\Bot\Tests\Component\GitHub\PullRequest\Merging;

use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces\MergeableResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Verdict\MergeableVerdict;
use Visca\Bot\Component\GitHub\PullRequest\Merging\PullRequestMerger;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

class PullRequestMergerTest extends \PHPUnit_Framework_TestCase
{
    public function testMergerWillMergeIfThePullRequestIsMergable()
    {
        $alwaysMergable = new class implements MergeableResolverInterface
        {

            public function canBeMerged(array $pullRequest)
            {
                return new MergeableVerdict(true, $this);
            }
        };

        $username = "bob";
        $repository = "foobar";
        $base = "master";
        $head = "e2a49b2acbfdc0fae6582e35ceb4766042212ae5";

        $pullRequest = [
            'number' => 1,
            'head' => [
                'repo' => [
                    'owner' => [
                        'login' => $username
                    ],
                    'name' => $repository
                ],
                'sha' => $head
            ],
            'base' => [
                'ref' => $base
            ]
        ];

        /** @var PullRequestMergeRepositoryInterface|PHPUnit_Framework_MockObject_MockObject $pullRequestRepository */
        $pullRequestRepository = $this->getMockBuilder(PullRequestMergeRepositoryInterface::class)
            ->setMethods(['merge', 'findByCommitHash', 'findById', 'findWithMergeLabel'])
            ->getMock();

        $pullRequestRepository
            ->expects($this->once())
            ->method('merge')
            ->with(
                $this->equalTo($username),
                $this->equalTo($repository),
                $this->equalTo($base),
                $this->equalTo($head)
            );

        $merger = new PullRequestMerger(
            $alwaysMergable,
            $pullRequestRepository,
            $this->getMockBuilder(MessageBus::class)->getMock(),
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $merger->merge($pullRequest);
    }

    public function testMergerWillNotMergeIfThePullRequestIsNotMergable()
    {
        $neverMergable = new class implements MergeableResolverInterface
        {

            public function canBeMerged(array $pullRequest)
            {
                return new MergeableVerdict(false, $this);
            }
        };

        $pullRequestRepository = new class implements PullRequestMergeRepositoryInterface
        {
            public function merge($username, $repository, $base, $head, $message = null)
            {
                // Do nothing
            }

            public function findByCommitHash($username, $repository, $hash)
            {
                return [];
            }

            public function findById($username, $repository, $id)
            {
                return null;
            }

            public function findWithMergeLabel($username, $repository)
            {
                return null;
            }
        };

        $merger = new PullRequestMerger(
            $neverMergable,
            $pullRequestRepository,
            $this->getMockBuilder(MessageBus::class)->getMock(),
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $pullRequest = [
            'number' => 1,
            'head' => [
                'repo' => [
                    'owner' => [
                        'login' => ''
                    ],
                    'name' => ''
                ],
                'sha' => ''
            ],
            'base' => [
                'ref' => ''
            ]
        ];

        \PHPUnit_Framework_Assert::assertFalse($merger->merge($pullRequest));
    }
}
