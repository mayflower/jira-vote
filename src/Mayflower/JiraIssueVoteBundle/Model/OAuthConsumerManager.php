<?php

namespace Mayflower\JiraIssueVoteBundle\Model;

use Mayflower\JiraIssueVoteBundle\Jira\RestHandler;

/**
 * OAuthConsumerManager
 *
 * Model manager of the oauth user
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class OAuthConsumerManager
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
     * Finds the current user
     *
     * @return OAuthConsumer
     */
    public function findCurrent()
    {
        $consumer = new OAuthConsumer();
        $consumer->setName($this->handler->executeApiCall('rest/auth/1/session')['name']);

        return $consumer;
    }
}
