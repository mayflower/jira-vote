<?php
namespace Ma27\Jira\IssueVoteBundle\Util;

/**
 * Interface which defines the methods of a rest provider for the jira rest client
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
interface RestProviderInterface
{
    /**
     * Executes a rest request on the jira server
     *
     * @param string   $uri     Target uri
     * @param string   $method  Http method of the request
     * @param string[] $headers List of headers
     *
     * @return mixed
     *
     * @api
     */
    public function executeRequest($uri, $method = 'post', array $headers = array());

    /**
     * Returns the temp credentials from the jira server
     *
     * @return \Ma27\Jira\IssueVoteBundle\Entity\OAuthToken
     *
     * @api
     */
    public function getCredentials();

    /**
     * Returns the auth credentials by the values of the temp credentials
     *
     * @param string $token       Temp token
     * @param string $tokenSecret Temp token secret
     * @param string $verifier    Verifier from the jira server
     *
     * @return \Ma27\Jira\IssueVoteBundle\Entity\OAuthToken
     *
     * @api
     */
    public function getCredentialsFromAccessUri($token, $tokenSecret, $verifier);

    /**
     * Returns a client object from jira
     *
     * @return \Ma27\Jira\IssueVoteBundle\Util\Client\Jira
     *
     * @api
     */
    public function getClient();
} 