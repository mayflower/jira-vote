<?php

namespace Mayflower\JiraIssueVoteBundle\Service\Filter\Iterator;

use Mayflower\JiraIssueVoteBundle\Controller\IssueController;
use Mayflower\JiraIssueVoteBundle\Entity\Issue;
use Mayflower\JiraIssueVoteBundle\Service\Filter\AbstractIterator;
use Symfony\Component\HttpFoundation\Session\Session;

class IssueVotedIterator extends AbstractIterator
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        parent::__construct();
        $this->session = $session;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return true === $this->session->get(IssueController::DISABLE_VOTED_ID);
    }

    /**
     * @return boolean
     */
    public function accept()
    {
        $current = $this->getInnerIterator()->current();
        if (!$current instanceof Issue) {
            throw new \LogicException('All iterator elements must be an issue entity');
        }

        return false === $current->hasUserVoted();
    }
}
