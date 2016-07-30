<?php
namespace Visca\Bot\Component\GitHub\Repositories;

use Github\Api\Repo;
use Visca\Bot\Component\GitHub\Repositories\Interfaces\StatusesRepositoryInterface;

/**
 * Class StatusesRepository
 */
final class StatusesRepository implements StatusesRepositoryInterface
{
    /** @var Repo */
    private $api;

    /**
     * StatusesRepository constructor.
     *
     * @param Repo $api
     */
    public function __construct(Repo $api)
    {
        $this->api = $api;
    }

    /**
     * @inheritdoc
     */
    public function get($username, $repository, $sha)
    {
        $statuses = $this
            ->api
            ->statuses()
            ->show($username, $repository, $sha);

        return $statuses;
    }
}
