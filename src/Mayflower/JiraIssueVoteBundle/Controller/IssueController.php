<?php
namespace Mayflower\JiraIssueVoteBundle\Controller;

use Closure;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\Response;
use Mayflower\JiraIssueVoteBundle\EventListener\JiraCredentialsListener;
use Mayflower\JiraIssueVoteBundle\Util\OAuthSecurityToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $that = &$this;
        return static::performHandledJiraAction(
            function () use ($that)
            {
                /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
                $session = $that->get('session');
                /** @var \Mayflower\JiraIssueVoteBundle\Service\IssueDataViewContext $manager */
                $manager = $that->get('ma27_jira_issue_vote.issue.manager');
                /** @var \Mayflower\JiraIssueVoteBundle\Service\Filter\FilterContainer $filter */
                $filter = $that->get('ma27_jira_issue_vote.filter.container');

                $filterId = $session->get(IssueController::SELECTED_FILTER_ID);
                if ($filterId === null) {
                    return $that->redirect($that->generateUrl('ma27_jira_issue_vote_select_filter'));
                }

                $issues = $manager->findAndConvertIssuesToDataCollection($filterId);

                $user = $manager->getCurrentUser();
                $list = $filter->process(iterator_to_array($issues->all()), array('consumer' => $user));

                return $that->render('MayflowerJiraIssueVoteBundle:Pages:index.html.twig', array(
                    'issues' => $list,
                    'currentUser' => $user,
                    'filterName' => $session->get(IssueController::SELECT_FILTER_NAME),
                    'disable_voted_issues' => $session->get(IssueController::DISABLE_VOTED_ID),
                    'disable_reported_issues' => $session->get(IssueController::DISABLE_REPORTED_ID),
                    'disable_resolved_issues' => $session->get(IssueController::DISABLE_RESOLVED_ID)
                ));
            },
            array($this, 'handleRequestErrorException'),
            $this->get('ma27_jira_issue_vote.oauth.proxy')
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
            array(
                'hideVoted'    => $session->get(static::DISABLE_VOTED_ID),
                'hideReported' => $session->get(static::DISABLE_REPORTED_ID),
                'hideResolved' => $session->get(static::DISABLE_RESOLVED_ID)
            )
        );
    }

    /**
     * Action which handles the page to register a new filter for the
     * issue voter
     *
     * @param Request $request Request object for this page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function selectFilterAction(Request $request)
    {
        $that = &$this;
        return static::performHandledJiraAction(
            function () use ($that, $request)
            {
                /** @var \Mayflower\JiraIssueVoteBundle\Service\IssueDataViewContext $manager */
                $manager = $that->get('ma27_jira_issue_vote.issue.manager');
                $list    = $manager->getFavouriteFilters()->getAll(true);
                $errors  = array();
                $isIdValid = false;

                if ('POST' === $request->getMethod()) {
                    $filterId  = (int)$request->get('filter_id');

                    foreach ($list as $item) {
                        if ($item['id'] === $filterId) {
                            $that->get('session')->set(IssueController::SELECTED_FILTER_ID, $filterId);
                            $that->get('session')->set(IssueController::SELECT_FILTER_NAME, $request->get('filter_name'));
                            return $that->redirect($that->generateUrl('ma27_jira_issue_vote_homepage'));
                        }
                    }
                }

                return $that->render(
                    'MayflowerJiraIssueVoteBundle:Pages:select_filter.html.twig',
                    array(
                        'filters' => $list,
                        'subscribe_issue_collection_url' => $that->generateUrl('ma27_jira_issue_vote_select_filter'),
                        'errors' => $errors,
                        'invalidFilterId' => $isIdValid
                    )
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
     */
    public function voteAction(Request $request)
    {
        $that = &$this;
        return static::performHandledJiraAction(
            function () use ($that, $request)
            {
                /** @var \Mayflower\JiraIssueVoteBundle\Service\IssueDataViewContext $manager */
                $manager = $that->get('ma27_jira_issue_vote.issue.manager');

                $result = $manager->voteIssue($id = $request->get('issue_id'));
                if (!$result) {
                    return new HttpResponse(null, 500);
                }
                return new HttpResponse(null, 200);
            },
            array($this, 'handleRequestErrorException')
        );
    }

    /**
     * Action which unvotes an issue of a user
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function unvoteAction(Request $request)
    {
        $that = &$this;
        return static::performHandledJiraAction(
            function () use ($that, $request)
            {
                /** @var \Mayflower\JiraIssueVoteBundle\Service\IssueDataViewContext $manager */
                $manager = $that->get('ma27_jira_issue_vote.issue.manager');

                $result = $manager->unvoteIssue($id = $request->get('issue_id'));
                if (!$result) {
                    return new HttpResponse(null, 500);
                }
                return new HttpResponse(null, 200);
            },
            array($this, 'handleRequestErrorException'),
            $this->get('ma27_jira_issue_vote.oauth.proxy')
        );
    }

    /**
     * Action which is responsible for the voted hide setting
     *
     * @param Request $request Http Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setHideIssuesAction(Request $request)
    {
        return $this->storeSetting($request->get('option'), static::DISABLE_VOTED_ID);
    }

    /**
     * Action which is responsible for the reported hide setting
     *
     * @param Request $request Http request
     *
     * @return HttpResponse
     */
    public function setReportedIssuesAction(Request $request)
    {
        return $this->storeSetting($request->get('option'), static::DISABLE_REPORTED_ID);
    }

    /**
     * @param Request $request
     * @return HttpResponse
     */
    public function setResolvedIssuesAction(Request $request)
    {
        return $this->storeSetting($request->get('option'), static::DISABLE_RESOLVED_ID);
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
        if ('true' === $option = mb_strtolower($option)) {
            $this->get('session')->set($sessionKey, true);
        } else {
            $this->get('session')->set($sessionKey, false);
        }

        // just send an empty response to the client in order to mark mark as success
        return new HttpResponse(null);
    }

    /**
     * Wraps a command and catches curl exceptions from guzzle
     *
     * @param callable           $command      Command to execute
     * @param callable           $errorHandler Handler which will be triggered if a curl exception occurs
     * @param OAuthSecurityToken $proxy        Additional proxy to check credentials
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Guzzle\Http\Exception\ClientErrorResponseException
     *
     * @api
     */
    public static function performHandledJiraAction(Closure $command, $errorHandler = null, OAuthSecurityToken $proxy = null)
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

            return call_user_func($command);
        } catch (ClientErrorResponseException $ex) {
            if (null !== $errorHandler) {
                if (!is_callable($errorHandler)) {
                    throw new \LogicException('Errorhandler must be a callable or null!');
                }
                
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
     */
    private function handleRequestErrorException(ClientErrorResponseException $ex)
    {
        return $this->forward(
            'MayflowerJiraIssueVoteBundle:Authorize:invalidateTokens',
            array('redirect_url' => $this->generateUrl('ma27_jira_issue_vote_verify'))
        );
    }
}
