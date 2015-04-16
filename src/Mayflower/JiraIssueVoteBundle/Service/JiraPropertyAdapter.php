<?php
namespace Mayflower\JiraIssueVoteBundle\Service;

use GuzzleHttp\Exception\RequestException;
use Mayflower\JiraIssueVoteBundle\Jira\RestHandler;

/**
 * Concrete implementation of a issue tracker loader for the JIRA rest api
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class JiraPropertyAdapter implements JiraPropertyLoaderInterface
{
    /**
     * @var RestHandler
     */
    private $restHandler;

    /**
     * Sets the rest provider
     *
     * @param RestHandler $restHandler
     *
     * @api
     */
    public function __construct(RestHandler $restHandler)
    {
        $this->restHandler = $restHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function loadFavouriteIssues()
    {
        return $this->restHandler->executeApiCall('rest/api/2/filter/favourite');
    }

    /**
     * {@inheritDoc}
     */
    public function loadIssuesByFilterId($filterId)
    {
        $filterInfo = $this->restHandler->executeApiCall(sprintf('rest/api/2/filter/%d', $filterId));

        return $this->restHandler->executeApiCall($filterInfo['searchUrl'])['issues'];
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentUser()
    {
        return $this->restHandler->executeApiCall('rest/auth/1/session');
    }

    /**
     * {@inheritDoc}
     */
    public function getJiraHost()
    {
        return $this->restHandler->getJira();
    }

    /**
     * {@inheritDoc}
     */
    public function voteIssue($issueId)
    {
        return $this->doVoteIssue($issueId);
    }

    /**
     * {@inheritDoc}
     */
    public function unvoteIssue($issueId)
    {
        return $this->doVoteIssue($issueId, 'delete');
    }

    /**
     * Votes or unvotes an issue depending on the http method
     *
     * @param string|integer $id   Id of the issue to vote
     * @param string         $type Http method (in case of post the issue will be voted, in case of delete the issue will be unvoted)
     *
     * @return boolean
     *
     * @throws RequestException
     *
     * @api
     */
    private function doVoteIssue($id, $type = 'post')
    {
        try {
            $this->restHandler->executeApiCall(sprintf('rest/api/2/issue/%d/votes', $id), $type);
        } catch (RequestException $ex) {
            if ($ex->getResponse()->getStatusCode() === 404) {
                return false;
            }

            throw $ex;
        }

        return true;
    }
}
