<?php
namespace Visca\Bot\Tests\Component\GitHub\PullRequest\Merging\Mergability;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Assert;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergability\MergableIfAllChecksSuccess;
use Visca\Bot\Component\GitHub\Repositories\Interfaces\StatusesRepositoryInterface;

class MergableIfAllChecksSuccessTest extends TestCase
{
    public function testThePullRequestIsMergableIfAllChecksAreSuccess()
    {
        $statuses = [
            [
                'state' => 'success',
                'target_url' => 'https://example.com/build/status',
                'context' => 'continuous-integration/jenkins',
                "description" => "The build succeeded!",
            ],
        ];

        $canBeMerged = $this->resolve($statuses);

        self::assertTrue($canBeMerged);
    }

    private function resolve($statuses)
    {
        $username = 'bob';
        $repository = 'foobar';
        $hash = '3d24b73b0dd72fd7af760f36942e8c47c20db5a4';

        $statusRepository = $this->createStatusRepository(
            $username,
            $repository,
            $hash,
            $statuses
        );

        $resolver = new MergableIfAllChecksSuccess($statusRepository);

        return $resolver->canBeMerged($username, $repository, $hash);
    }

    private function createStatusRepository($expectedUsername, $expectedRepository, $expectedHash, $statuses)
    {
        $repository = new class ($expectedUsername, $expectedRepository, $expectedHash, $statuses) implements StatusesRepositoryInterface
        {
            private $expectedUsername;
            private $expectedRepository;
            private $expectedHash;
            private $statuses;

            public function __construct($expectedUsername, $expectedRepository, $expectedHash, $statuses)
            {
                $this->expectedUsername = $expectedUsername;
                $this->expectedRepository = $expectedRepository;
                $this->expectedHash = $expectedHash;
                $this->statuses = $statuses;
            }

            public function get($username, $repository, $sha)
            {
                PHPUnit_Framework_Assert::assertEquals($this->expectedUsername, $username);
                PHPUnit_Framework_Assert::assertEquals($this->expectedRepository, $repository);
                PHPUnit_Framework_Assert::assertEquals($this->expectedHash, $sha);

                return $this->statuses;
            }
        };

        return $repository;
    }

    public function testThePullRequestIsNotMergableIfOneChecksFailed()
    {
        $statuses = [
            [
                'state' => 'failure',
                'target_url' => 'https://example.com/build/status',
                'context' => 'continuous-integration/jenkins',
                "description" => "The build succeeded!",
            ],
        ];

        $canBeMerged = $this->resolve($statuses);

        self::assertFalse($canBeMerged);
    }
}
