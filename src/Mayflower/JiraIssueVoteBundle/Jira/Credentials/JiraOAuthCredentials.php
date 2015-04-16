<?php

namespace Mayflower\JiraIssueVoteBundle\Jira\Credentials;

/**
 * JiraOAuthCredentials
 *
 * Value object which contains the consumer key and consumer secret of the Jira oAuth API
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class JiraOAuthCredentials
{
    /**
     * @var string
     */
    private $consumerKey;

    /**
     * @var string
     */
    private $consumerSecret;

    /**
     * Constructor
     *
     * @param string $consumerKey
     * @param string $consumerSecret
     */
    public function __construct($consumerKey, $consumerSecret)
    {
        $this->consumerKey    = (string) $consumerKey;
        $this->consumerSecret = (string) $consumerSecret;
    }

    /**
     * @return string
     */
    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * @return string
     */
    public function getConsumerSecret()
    {
        return $this->consumerSecret;
    }
}
