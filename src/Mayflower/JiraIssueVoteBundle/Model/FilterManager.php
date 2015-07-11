<?php

namespace Mayflower\JiraIssueVoteBundle\Model;

use Mayflower\JiraIssueVoteBundle\Jira\RestHandler;

/**
 * FilterManager
 *
 * Model manager for issue filters
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class FilterManager
{
    /**
     * @var RestHandler
     */
    private $handler;

    /**
     * Constructor
     *
     * @param RestHandler $handler
     */
    public function __construct(RestHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Finds all favourite issues of the current user
     *
     * @return Filter[]
     */
    public function findFavouriteFilters()
    {
        $rawFilters = $this->handler->executeApiCall('rest/api/2/filter/favourite');

        return array_map(
            function (array $data) {
                return Filter::fill($data);
            },
            $rawFilters
        );
    }
}
