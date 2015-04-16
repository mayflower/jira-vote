<?php
namespace Mayflower\JiraIssueVoteBundle\Service;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Mayflower\JiraIssueVoteBundle\Util\RestProviderInterface;

/**
 * Concrete implementation of a issue tracker loader for the JIRA rest api
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class JiraPropertyAdapter implements JiraPropertyLoaderInterface
{
    /**
     * Provider which handles the http requests
     * @var \Mayflower\JiraIssueVoteBundle\Util\RestProviderInterface
     */
    private $oauthProvider;

    /**
     * Sets the rest provider
     *
     * @param RestProviderInterface $oauthProvider
     *
     * @api
     */
    public function __construct(RestProviderInterface $oauthProvider)
    {
        $this->oauthProvider = $oauthProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function loadFavouriteIssues()
    {
        $res = $this->oauthProvider->processJiraRequest('rest/api/2/filter/favourite', 'get');
        return $res;
    }

    /**
     * {@inheritDoc}
     */
    public function loadIssuesByFilterId($filterId)
    {
        $filterProps = $this->oauthProvider->processJiraRequest('rest/api/2/filter/' . $filterId, 'get');
        $result = $this->oauthProvider->processJiraRequest($filterProps['searchUrl'], 'get');
        $issues = $result['issues'];

        return $issues;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentUser()
    {
        return $this->oauthProvider->processJiraRequest('rest/auth/1/session', 'get');
    }

    /**
     * {@inheritDoc}
     */
    public function getJiraHost()
    {
        return $this->oauthProvider->getClient()->getBaseUrl();
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
     * @throws ClientErrorResponseException
     *
     * @api
     */
    private function doVoteIssue($id, $type = 'post')
    {
        try {
            $this->oauthProvider->processJiraRequest('rest/api/2/issue/' . $id . '/votes', $type);
            return true;
        } catch (ClientErrorResponseException $ex) {
            if ($ex->getResponse()->getStatusCode() === 404) {
                return false;
            }

            throw $ex;
        }
    }
} 
