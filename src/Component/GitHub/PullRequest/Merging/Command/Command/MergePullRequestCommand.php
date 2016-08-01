<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Command\Command;

final class MergePullRequestCommand
{
    /** @var string */
    private $owner;

    /** @var string */
    private $repo;

    /** @var int */
    private $id;

    public function __construct($owner, $repo, $id)
    {
        $this->owner = $owner;
        $this->repo = $repo;
        $this->id = $id;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getRepo(): string
    {
        return $this->repo;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
