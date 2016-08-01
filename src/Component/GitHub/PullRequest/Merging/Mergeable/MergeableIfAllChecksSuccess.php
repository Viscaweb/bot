<?php
namespace Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable;

use Psr\Log\LoggerInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Interfaces\MergeableResolverInterface;
use Visca\Bot\Component\GitHub\PullRequest\Merging\Mergeable\Verdict\MergeableVerdict;
use Visca\Bot\Component\GitHub\Repositories\Interfaces\StatusesRepositoryInterface;

/**
 * Class MergeableIfAllChecksSuccess
 */
final class MergeableIfAllChecksSuccess implements MergeableResolverInterface
{
    /** @var StatusesRepositoryInterface */
    private $statusRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(StatusesRepositoryInterface $statusRepository, LoggerInterface $logger)
    {
        $this->statusRepository = $statusRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function canBeMerged(array $pullRequest)
    {
        $statuses = $this->statusRepository->get(
            $pullRequest['head']['repo']['owner']['login'],
            $pullRequest['head']['repo']['name'],
            $pullRequest['head']['sha']
        );

        $allContexts = array_unique(array_column($statuses, 'context'));

        foreach ($allContexts as $context) {
            $expectedSuccessStatus = ['state' => 'success', 'context' => $context];
            $statusesFound = $this->multi_array_search($statuses, $expectedSuccessStatus);

            if (empty($statusesFound)) {
                $this
                    ->logger
                    ->info(
                        sprintf(
                            'PR %d for %s/%s is not mergeable because no success check for "%s" can be found',
                            $pullRequest['number'],
                            $pullRequest['head']['repo']['owner']['login'],
                            $pullRequest['head']['repo']['name'],
                            $context
                        )
                    );

                return new MergeableVerdict(false, $this);
            }
        }

        return new MergeableVerdict(true, $this);
    }

    /**
     * http://stackoverflow.com/questions/13923524/php-search-an-array-for-multiple-key-value-pairs
     *
     * Multi-array search
     *
     * @param array $array
     * @param array $search
     *
     * @return array
     */
    private function multi_array_search($array, $search)
    {

        // Create the result array
        $result = array();

        // Iterate over each array element
        foreach ($array as $key => $value) {

            // Iterate over each search condition
            foreach ($search as $k => $v) {

                // If the array element does not meet the search condition then continue to the next element
                if (!isset($value[$k]) || $value[$k] != $v) {
                    continue 2;
                }

            }

            // Add the array element's key to the result array
            $result[] = $key;

        }

        // Return the result array
        return $result;

    }
}
