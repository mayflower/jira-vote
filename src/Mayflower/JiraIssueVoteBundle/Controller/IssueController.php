<?php

namespace Mayflower\JiraIssueVoteBundle\Controller;

use Mayflower\JiraIssueVoteBundle\Jira\TokenFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Issue controller
 */
class IssueController extends Controller
{
    const SELECTED_FILTER_ID   = 'issue.filter.current';
    const SELECTED_FILTER_NAME = 'issue.filter.current.name';

    /**
     * Action which handles the homepage of the issue
     * voter
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function indexAction()
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');
        /** @var \Mayflower\JiraIssueVoteBundle\Model\IssueManager $issueManager */
        $issueManager = $this->get('mayflower_model_manager_issue');
        /** @var \Mayflower\JiraIssueVoteBundle\Model\OAuthConsumerManager $userManager */
        $userManager = $this->get('mayflower_model_manager_user');

        if (!$session->has(TokenFetcher::OAUTH_TOKEN)) {
            return $this->redirect($this->generateUrl('ma27_jira_issue_vote_verify'));
        }

        $filterId = $session->get(self::SELECTED_FILTER_ID);
        if ($filterId === null) {
            return $this->redirect($this->generateUrl('ma27_jira_issue_vote_select_filter'));
        }

        $user = $userManager->findCurrent();
        $list = $issueManager->findRecentByFilterId($filterId);

        return $this->render(
            'MayflowerJiraIssueVoteBundle:Pages:index.html.twig',
            [
                'issues'      => $list,
                'currentUser' => $user,
                'filterName'  => $session->get(self::SELECTED_FILTER_NAME),
            ]
        );
    }

    /**
     * Action which handles the page to register a new filter for the
     * issue voter
     *
     * @param Request $request Request object for this page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function selectFilterAction(Request $request)
    {
        /** @var \Mayflower\JiraIssueVoteBundle\Model\FilterManager $manager */
        $manager = $this->get('mayflower_model_manager_filter');
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session   = $this->get('session');

        if (!$session->has(TokenFetcher::OAUTH_TOKEN)) {
            return $this->redirect($this->generateUrl('ma27_jira_issue_vote_verify'));
        }

        $list      = $manager->findFavouriteFilters();
        $errors    = [];
        $invalidId = false;

        if ('POST' === $request->getMethod()) {
            $filterId  = (integer) $request->get('filter_id');

            foreach ($list as $item) {
                if ($item->getId() === $filterId) {
                    $session->set(self::SELECTED_FILTER_ID, $filterId);
                    $session->set(self::SELECTED_FILTER_NAME, $request->get('filter_name'));

                    return $this->redirect($this->generateUrl('ma27_jira_issue_vote_homepage'));
                }
            }

            $invalidId = true;
        }

        return $this->render(
            'MayflowerJiraIssueVoteBundle:Pages:select_filter.html.twig',
            [
                'filters'                        => $list,
                'subscribe_issue_collection_url' => $this->generateUrl('ma27_jira_issue_vote_select_filter'),
                'errors'                         => $errors,
                'invalidFilterId'                => $invalidId
            ]
        );
    }

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
