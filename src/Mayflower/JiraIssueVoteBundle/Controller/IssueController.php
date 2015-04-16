<?php

namespace Mayflower\JiraIssueVoteBundle\Controller;

use Mayflower\JiraIssueVoteBundle\Jira\TokenFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Issue controller
 */
class IssueController extends Controller
{
    const SELECTED_FILTER_ID  = 'issue.filter.current';
    const SELECT_FILTER_NAME  = 'issue.filter.current.name';
    const DISABLE_VOTED_ID    = 'issue.view.disable.voted';
    const DISABLE_REPORTED_ID = 'issue.view.disable.reported';
    const DISABLE_RESOLVED_ID = 'issue.view.disable.resolved';

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
        /** @var \Mayflower\JiraIssueVoteBundle\Service\IssueDataViewContext $manager */
        $manager = $this->get('ma27_jira_issue_vote.issue.manager');
        /** @var \Mayflower\JiraIssueVoteBundle\Service\Filter\FilterContainer $filter */
        $filter = $this->get('ma27_jira_issue_vote.filter.container');

        if (!$session->has(TokenFetcher::OAUTH_TOKEN)) {
            return $this->redirect($this->generateUrl('ma27_jira_issue_vote_verify'));
        }

        $filterId = $session->get(self::SELECTED_FILTER_ID);
        if ($filterId === null) {
            return $this->redirect($this->generateUrl('ma27_jira_issue_vote_select_filter'));
        }

        $issues = $manager->findAndConvertIssuesToDataCollection($filterId);

        $user = $manager->getCurrentUser();
        $list = $filter->process(iterator_to_array($issues->all()), ['consumer' => $user]);

        return $this->render(
            'MayflowerJiraIssueVoteBundle:Pages:index.html.twig',
            [
                'issues'                  => $list,
                'currentUser'             => $user,
                'filterName'              => $session->get(self::SELECT_FILTER_NAME),
                'disable_voted_issues'    => $session->get(self::DISABLE_VOTED_ID),
                'disable_reported_issues' => $session->get(self::DISABLE_REPORTED_ID),
                'disable_resolved_issues' => $session->get(self::DISABLE_RESOLVED_ID)
            ]
        );
    }

    /**
     * Returns a JSON Response containing the issue settings
     *
     * @return JsonResponse
     */
    public function settingsListAction()
    {
        $session = $this->get('session');

        return new JsonResponse(
            [
                'hideVoted'    => $session->get(self::DISABLE_VOTED_ID),
                'hideReported' => $session->get(self::DISABLE_REPORTED_ID),
                'hideResolved' => $session->get(self::DISABLE_RESOLVED_ID)
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
        /** @var \Mayflower\JiraIssueVoteBundle\Service\IssueDataViewContext $manager */
        $manager = $this->get('ma27_jira_issue_vote.issue.manager');
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session   = $this->get('session');
        $list      = $manager->getFavouriteFilters()->getAll(true);
        $errors    = [];
        $invalidId = false;

        if (!$session->has(TokenFetcher::OAUTH_TOKEN)) {
            return $this->redirect($this->generateUrl('ma27_jira_issue_vote_verify'));
        }

        if ('POST' === $request->getMethod()) {
            $filterId  = (int) $request->get('filter_id');

            foreach ($list as $item) {
                if ($item['id'] === $filterId) {
                    $session->set(self::SELECTED_FILTER_ID, $filterId);
                    $session->set(self::SELECT_FILTER_NAME, $request->get('filter_name'));

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
        return $this->doVote($request, 'vote');
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
        return $this->doVote($request, 'unvote');
    }

    /**
     * Action which is responsible for the voted hide setting
     *
     * @param Request $request Http Request
     *
     * @return Response
     */
    public function setHideIssuesAction(Request $request)
    {
        return $this->storeSetting($request->get('option'), self::DISABLE_VOTED_ID);
    }

    /**
     * Action which is responsible for the reported hide setting
     *
     * @param Request $request Http request
     *
     * @return Response
     */
    public function setReportedIssuesAction(Request $request)
    {
        return $this->storeSetting($request->get('option'), self::DISABLE_REPORTED_ID);
    }

    /**
     * Action which is responsible for the reported resolved setting
     *
     * @param Request $request
     *
     * @return Response
     */
    public function setResolvedIssuesAction(Request $request)
    {
        return $this->storeSetting($request->get('option'), self::DISABLE_RESOLVED_ID);
    }

    /**
     * Setting action which wraps the setting handlers
     *
     * @param string $option     Option name
     * @param string $sessionKey Session key of the option
     *
     * @return HttpResponse
     */
    private function storeSetting($option, $sessionKey)
    {
        if ('true' === $option = strtolower($option)) {
            $this->get('session')->set($sessionKey, true);
        } else {
            $this->get('session')->set($sessionKey, false);
        }

        return new Response(null, 204);
    }

    /**
     * Internal action that votes or unvotes a ticket.
     * In order to prevent ugly code duplication, this action was implemented
     *
     * @param Request $request
     * @param string $type
     *
     * @return Response
     *
     * @throws \InvalidArgumentException If an invalid vote type is given
     */
    private function doVote(Request $request, $type = 'vote')
    {
        if (!in_array($type, ['vote', 'unvote'])) {
            throw new \InvalidArgumentException(sprintf('Only the types vote and unvote are allowed!'));
        }

        /** @var \Mayflower\JiraIssueVoteBundle\Service\IssueDataViewContext $manager */
        $manager = $this->get('ma27_jira_issue_vote.issue.manager');

        $function = sprintf('%sIssue', $type);
        $result   = $manager->{$function}($request->get('issue_id'));
        if (!$result) {
            return new Response(null, 500);
        }

        return new Response(null, 204);
    }
}
