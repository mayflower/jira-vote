<?php

namespace Mayflower\JiraIssueVoteBundle\Controller;

use Mayflower\JiraIssueVoteBundle\Jira\TokenFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vote controller
 */
class VoteController extends Controller
{
    /**
     * Action which is responsible for the vote of an issue
     *
     * @param Request $request
     *
     * @return Response
     */
    public function voteAction(Request $request)
    {
        /** @var \Mayflower\JiraIssueVoteBundle\Model\IssueManager $issueManager */
        $issueManager = $this->get('mayflower_model_manager_issue');

        return new Response(null, $issueManager->vote($request->attributes->get('issue_id')) ? 204 : 500);
    }

    /**
     * Action which unvotes an issue of a user
     *
     * @param Request $request
     *
     * @return Response
     */
    public function unvoteAction(Request $request)
    {
        /** @var \Mayflower\JiraIssueVoteBundle\Model\IssueManager $issueManager */
        $issueManager = $this->get('mayflower_model_manager_issue');

        return new Response(null, $issueManager->unvote($request->attributes->get('issue_id')) ? 204 : 500);
    }
}
