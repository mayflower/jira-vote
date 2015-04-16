<?php

namespace Mayflower\JiraIssueVoteBundle\Jira;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

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
     * Runs a JIRA API call
     *
     * @param string $uri
     * @param string $method
     * @param string[] $headers
     * @param boolean $toJson
     */
    public function executeApiCall($uri, $method = 'get', array $headers = [], $toJson = true)
    {

    }
}
