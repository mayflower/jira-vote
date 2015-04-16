<?php

namespace Mayflower\JiraIssueVoteBundle\Jira\Credentials;

/**
 * AccessToken
 *
 * Value object which contains the credentials of a user for the JIRA Api
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class AccessToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $tokenSecret;

    /**
     * Constructor
     *
     * @param string $token
     * @param string $tokenSecret
     */
    public function __construct($token, $tokenSecret)
    {
        $this->token       = (string) $token;
        $this->tokenSecret = (string) $tokenSecret;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }
}
