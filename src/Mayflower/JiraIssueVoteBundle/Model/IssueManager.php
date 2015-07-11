<?php

namespace Mayflower\JiraIssueVoteBundle\Model;

use GuzzleHttp\Exception\RequestException;
use Mayflower\JiraIssueVoteBundle\Jira\RestHandler;

/**
 * IssueManager
 *
 * Model manager of the issue list
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class IssueManager
{
    const VOTE_ISSUE   = 1;
    const UNVOTE_ISSUE = 2;

    /**
     * @var RestHandler
     */
    private $handler;

    /**
     * @var string
     */
    private $host;

    /**
     * Constructor
     *
     * @param RestHandler $handler
     * @param string $host
     */
    public function __construct(RestHandler $handler, $host)
    {
        $this->handler = $handler;
        $this->host    = (string) $host;
    }

    /**
     * Loads the latest 50 issue from the jira api
     *
     * @param integer $filterId
     *
     * @return Issue[]
     */
    public function findRecentByFilterId($filterId)
    {
         $issues = $this->handler->executeApiCall(
             $this->handler->executeApiCall(
                 sprintf('rest/api/2/filter/%d', $filterId)
             )['searchUrl']
         )['issues'];

        $host = $this->host;

        return array_map(
            function ($data) use ($host) {
                return Issue::fill($data, $host);
            },
            $issues
        );
    }

    /**
     * Votes on an issue
     *
     * @param integer $issueId
     *
     * @return boolean
     */
    public function vote($issueId)
    {
        return $this->processVote($issueId, self::VOTE_ISSUE);
    }

    /**
     * Unvotes on an issue
     *
     * @param integer $issueId
     *
     * @return boolean
     */
    public function unvote($issueId)
    {
        return $this->processVote($issueId, self::UNVOTE_ISSUE);
    }

    /**
     * Generalizes the vote/unvote process
     *
     * @param integer $issueId
     * @param integer $strategy
     *
     * @return boolean
     *
     * @throws RequestException If the request was not successful
     */
    private function processVote($issueId, $strategy)
    {
        $this->handler->executeApiCall(
            sprintf('rest/api/2/issue/%d/votes', $issueId),
            $strategy === self::VOTE_ISSUE ? 'POST' : 'DELETE'
        );

        return true;
    }
}