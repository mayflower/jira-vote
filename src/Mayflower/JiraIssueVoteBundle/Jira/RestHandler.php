<?php

namespace Mayflower\JiraIssueVoteBundle\Jira;

use Mayflower\JiraIssueVoteBundle\Exception\InvalidTokenException;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * RestHandler
 *
 * Handler which executes the JIRA API calls
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class RestHandler
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var string
     */
    private $jiraHost;

    /**
     * @var boolean
     */
    private $skipOnEmptyToken;

    /**
     * @param Session $session
     * @param ClientFactory $factory
     * @param string $jiraHost
     * @param boolean $skipOnEmptyToken
     */
    public function __construct(Session $session, ClientFactory $factory, $jiraHost, $skipOnEmptyToken = true)
    {
        $this->session          = $session;
        $this->clientFactory    = $factory;
        $this->jiraHost         = (string) $jiraHost;
        $this->skipOnEmptyToken = (boolean) $skipOnEmptyToken;
    }

    /**
     * Runs a JIRA API call
     *
     * @param string $uri
     * @param string $method
     * @param string[] $headers
     * @param string $requestBody
     * @param string $storeRequestTarget
     *
     * @return mixed The result of the api call
     *
     * @throws \InvalidArgumentException If the http method is invalid
     * @throws \InvalidArgumentException If the request target file is invalid
     * @throws InvalidTokenException     If the token is empty
     */
    public function executeApiCall(
        $uri,
        $method = 'GET',
        array $headers = null,
        $requestBody = null,
        $storeRequestTarget = null)
    {
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS', 'TRACE'])) {
            throw new \InvalidArgumentException(sprintf('The methods get, post, put and delete are allowed only!'));
        }

        $token = unserialize($this->session->get(TokenFetcher::OAUTH_TOKEN));
        if (!$token && $this->skipOnEmptyToken) {
            throw new InvalidTokenException;
        }

        $client = $this->clientFactory->createClient($token);

        $options = [];
        if ($token) {
            $options['config'] = ['auth' => 'oauth'];
        }

        if ($headers) {
            $options['headers'] = $headers;
        }

        if ($requestBody) {
            $options['body'] = $requestBody;
        }

        if ($storeRequestTarget) {
            if (!file_exists($storeRequestTarget)) {
                throw new \InvalidArgumentException(
                    sprintf('Cannot store file "%s" since the filepath is invalid', $storeRequestTarget)
                );
            }

            $options['save_to'] = $storeRequestTarget;
        }

        return $client->send(
            $client->createRequest($method, sprintf('%s/%s', $this->jiraHost, $uri), $options)
        )->json();
    }

    /**
     * Returns the url of the jira host
     *
     * @return string
     */
    public function getJira()
    {
        return $this->jiraHost;
    }
}
