<?php
namespace Visca\Bot\Tests\Component\GitHub\PullRequest\Merging;

use PHPUnit_Framework_MockObject_MockObject;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Exceptions\PullRequestCanNotBeMergedException;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergability\Interfaces\MergabilityResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\PullRequestMerger;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Repository\Interfaces\PullRequestMergeRepositoryInterface;

class PullRequestMergerTest extends \PHPUnit_Framework_TestCase
{
    public function testMergerWillMergeIfThePullRequestIsMergable()
    {
        $alwaysMergable = new class implements MergabilityResolverInterface
        {

            public function canBeMerged($username, $repository, $sha)
            {
                return true;
            }
        };

        $username = "bob";
        $repository = "foobar";
        $base = "master";
        $head = "e2a49b2acbfdc0fae6582e35ceb4766042212ae5";
        $message = "let's merge this";

        /** @var PullRequestMergeRepositoryInterface|PHPUnit_Framework_MockObject_MockObject $pullRequestRepository */
        $pullRequestRepository = $this->getMockBuilder(PullRequestMergeRepositoryInterface::class)
            ->setMethods(['merge'])
            ->getMock();

        $pullRequestRepository
            ->expects($this->once())
            ->method('merge')
            ->with(
                $this->equalTo($username),
                $this->equalTo($repository),
                $this->equalTo($base),
                $this->equalTo($head),
                $this->equalTo($message)
            );

        $merger = new PullRequestMerger($alwaysMergable, $pullRequestRepository);

        $merger->merge($username, $repository, $base, $head, $message);
    }

    public function testMergerWillNotMergeIfThePullRequestIsNotMergable()
    {
        self::expectException(PullRequestCanNotBeMergedException::class);

        $neverMergable = new class implements MergabilityResolverInterface
        {

            public function canBeMerged($username, $repository, $sha)
            {
                return false;
            }
        };

        $pullRequestRepository = new class implements PullRequestMergeRepositoryInterface
        {
            public function merge($username, $repository, $base, $head, $message = null)
            {
                // Do nothing
            }
        };

        $merger = new PullRequestMerger($neverMergable, $pullRequestRepository);

        $merger->merge("", "", "", "", "");
    }
}
