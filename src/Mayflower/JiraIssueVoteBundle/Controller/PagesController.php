<?php

namespace Mayflower\JiraIssueVoteBundle\Controller;

use Mayflower\JiraIssueVoteBundle\Jira\TokenFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * PagesController
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class PagesController extends Controller
{
    /**
     * Renders the initial page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->redirectOnLogout();

        return $this->render('@MayflowerJiraIssueVote/Pages/index.html.twig');
    }

    /**
     * Renders the select filter page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectFilterAction()
    {
        $this->redirectOnLogout();

        return $this->render('@MayflowerJiraIssueVote/Pages/select_filter.html.twig');
    }

    /**
     * Redirects the user on a logout
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function redirectOnLogout()
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session   = $this->get('session');

        if (!$session->has(TokenFetcher::OAUTH_TOKEN)) {
            return $this->redirect($this->generateUrl('ma27_jira_issue_vote_verify'));
        }
    }
}
