<?php
namespace Ma27\Jira\IssueVoteBundle\Controller;

use Closure;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\Response;
use Ma27\Jira\IssueVoteBundle\EventListener\JiraCredentialsListener;
use Ma27\Jira\IssueVoteBundle\Util\OAuthSecurityProxy;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Issue controller
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class IssueController extends Controller
{
    const SELECTED_FILTER_ID  = 'issue.filter.current';
    const DISABLE_VOTED_ID    = 'issue.view.disable.voted';
    const DISABLE_REPORTED_ID = 'issue.view.disable.reported';

    /**
     * Action which handles the homepage of the issue
     * voter
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @api
     */
    public function indexAction()
    {
        $that = &$this;
        return static::wrapCommand(
            function () use ($that)
            {
                /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
                $session = $that->get('session');
                /** @var \Ma27\Jira\IssueVoteBundle\Service\IssueManager $manager */
                $manager = $that->get('ma27_jira_issue_vote.issue.manager');

                $filterId = $session->get(static::SELECTED_FILTER_ID);
                if ($filterId === null) {
                    return $that->redirect($that->generateUrl('ma27_jira_issue_vote_select_filter'));
                }

                $issues = $manager->getIssuesByFilterId($filterId);
                $list = $issues->filter(
                    $that->get('session')->get(static::DISABLE_VOTED_ID),
                    $that->get('session')->get(static::DISABLE_REPORTED_ID),
                    $user = $manager->getCurrentUser()
                )->all();

                return $that->render('Ma27JiraIssueVoteBundle:Pages:index.html.twig', [
                    'issues' => $list,
                    'currentUser' => $user
                ]);
            },
            array($this, 'handleRequestErrorException'),
            $this->get('ma27_jira_issue_vote.oauth.proxy')
        );
    }

    /**
     * Action which handles the page to register a new filter for the
     * issue voter
     *
     * @param Request $request Request object for this page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @api
     */
    public function selectFilterAction(Request $request)
    {
        $that = &$this;
        return static::wrapCommand(
            function () use ($that, $request)
            {
                /** @var \Ma27\Jira\IssueVoteBundle\Service\IssueManager $manager */
                $manager = $that->get('ma27_jira_issue_vote.issue.manager');
                $list    = $manager->getFavouriteIssues()->getAll(true);
                $errors  = array();
                $isIdValid = false;

                if ('POST' === $request->getMethod()) {
                    $filterId  = (int)$request->get('filter_id');

                    foreach ($list as $item) {
                        if ($item['id'] === $filterId) {
                            $isIdValid = true;
                        }
                    }

                    if ($isIdValid) {
                        $that->get('session')->set(static::SELECTED_FILTER_ID, $filterId);
                        return $that->redirect($that->generateUrl('ma27_jira_issue_vote_homepage'));
                    }
                }

                return $that->render(
                    'Ma27JiraIssueVoteBundle:Pages:select_filter.html.twig',
                    [
                        'filters' => $list,
                        'subscribe_issue_collection_url' => $that->generateUrl('ma27_jira_issue_vote_select_filter'),
                        'errors' => $errors,
                        'invalidFilterId' => $isIdValid
                    ]
                );
            },
            array($this, 'handleRequestErrorException'),
            $this->get('ma27_jira_issue_vote.oauth.proxy')
        );
    }

    /**
     * Action which is responsible for the vote of an issue
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @api
     */
    public function voteAction(Request $request)
    {
        $that = &$this;
        return static::wrapCommand(
            function () use ($that, $request)
            {
                /** @var \Ma27\Jira\IssueVoteBundle\Service\IssueManager $manager */
                $manager = $that->get('ma27_jira_issue_vote.issue.manager');

                $result = $manager->voteIssue($id = $request->get('issue_id'));
                if (!$result) {
                    return $that->render('Ma27JiraIssueVoteBundle:Pages:cannot_vote.html.twig', array('issue_id' => $id));
                }
                return $that->redirect($that->generateUrl('ma27_jira_issue_vote_homepage') . '#' . $id);
            },
            array($this, 'handleRequestErrorException'),
            $this->get('ma27_jira_issue_vote.oauth.proxy')
        );
    }

    /**
     * Action which unvotes an issue of a user
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @api
     */
    public function unvoteAction(Request $request)
    {
        $that = &$this;
        return static::wrapCommand(
            function () use ($that, $request)
            {
                /** @var \Ma27\Jira\IssueVoteBundle\Service\IssueManager $manager */
                $manager = $that->get('ma27_jira_issue_vote.issue.manager');

                $result = $manager->unvoteIssue($id = $request->get('issue_id'));
                if (!$result) {
                    return $that->render('Ma27JiraIssueVoteBundle:Pages:cannot_unvote.html.twig', array('issue_id' => $id));
                }
                return $that->redirect($this->generateUrl('ma27_jira_issue_vote_homepage') . '#' . $id);
            },
            array($this, 'handleRequestErrorException'),
            $this->get('ma27_jira_issue_vote.oauth.proxy')
        );
    }

    /**
     * This action is responsible for the custom settings of this application
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @api
     */
    public function settingsAction(Request $request)
    {
        $that = &$this;
        return static::wrapCommand(
            function () use ($that, $request)
            {
                if ($request->getMethod() === 'POST') {
                    $disableVotedIssue    = (boolean)$request->get('disable_voted_issues');
                    $disableReportedIssue = (boolean)$request->get('disable_reported_issues');

                    $request->getSession()->set(static::DISABLE_VOTED_ID, $disableVotedIssue);
                    $request->getSession()->set(static::DISABLE_REPORTED_ID, $disableReportedIssue);

                    return $that->redirect($that->generateUrl('ma27_jira_issue_vote_homepage'));
                }

                return $that->render(
                    'Ma27JiraIssueVoteBundle:Pages:settings.html.twig',
                    array(
                        'disable_reported_issues' => (boolean)$request->getSession()->get(static::DISABLE_REPORTED_ID),
                        'disable_voted_issues'    => (boolean)$request->getSession()->get(static::DISABLE_VOTED_ID)
                    )
                );
            },
            array($this, 'handleRequestErrorException'),
            $this->get('ma27_jira_issue_vote.oauth.proxy')
        );
    }

    /**
     * Wraps a command and catches curl exceptions from guzzle
     *
     * @param callable           $command      Command to execute
     * @param callable           $errorHandler Handler which will be triggered if a curl exception occurs
     * @param OAuthSecurityProxy $proxy        Additional proxy to check credentials
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Guzzle\Http\Exception\ClientErrorResponseException
     *
     * @api
     */
    public static function wrapCommand(Closure $command, callable $errorHandler = null, OAuthSecurityProxy $proxy = null)
    {
        try {
            if (
                null !== $proxy
                && !$proxy->hasToken()
                || null !== $proxy
                && false === $proxy->getSession()->has(JiraCredentialsListener::OAUTH_LOGIN_FLAG)
            ) {
                $exception = new ClientErrorResponseException();
                $exception->setResponse(new Response(401));

                throw $exception;
            }

            return $command();
        } catch (ClientErrorResponseException $ex) {
            if (null !== $errorHandler) {
                return call_user_func($errorHandler, $ex);
            }

            throw $ex;
        }
    }

    /**
     * Simple handler for exceptions which invalidates the tokens and renders an error message
     *
     * @param ClientErrorResponseException $ex Caught exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @api
     */
    private function handleRequestErrorException(ClientErrorResponseException $ex)
    {
        return $this->forward(
            'Ma27JiraIssueVoteBundle:Authorize:invalidateTokens',
            array('redirect_url' => $this->generateUrl('ma27_jira_issue_vote_verify'))
        );
    }
}