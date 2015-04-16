<?php

namespace Mayflower\JiraIssueVoteBundle\Controller;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Mayflower\JiraIssueVoteBundle\Jira\UrlUtils;
use Mayflower\JiraIssueVoteBundle\EventListener\JiraCredentialsListener;
use Mayflower\JiraIssueVoteBundle\Util\OAuthSecurityToken as OAuthSecurityProxy;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Controller which is responsible for the authorization of the jira users
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class AuthorizeController extends Controller
{
    const OAUTH_TOKEN = 'oauth';

    /**
     * Action which renders the "unauthorized"-Template and stores the temporary
     * credentials of the rest users
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /** @var \Mayflower\JiraIssueVoteBundle\Jira\TokenFetcher $tokenFetcher */
        $tokenFetcher = $this->get('mayflower_token_fetcher');
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');

        // destroy legacy session data
        // legacy code @todo to be removed
        $session->remove(JiraCredentialsListener::OAUTH_LOGIN_FLAG);
        $session->remove(OAuthSecurityProxy::TEMP_TOKEN_ID);
        // end legacy code

        $credentialSet = $tokenFetcher->requestTempToken();
        $session->set(self::OAUTH_TOKEN, serialize($credentialSet));

        return $this->render(
            '@MayflowerJiraIssueVote/Authorize/index.html.twig',
            [
                'jira_url' => UrlUtils::getTokenConfirmationUrl(
                    $this->container->getParameter('host'), $credentialSet->getToken()
                )
            ]
        );
    }

    /**
     * Handles the JIRA callback and stores the auth token
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function callbackAction()
    {
        try {
            /** @var \Symfony\Component\HttpFoundation\Request $request */
            $request = $this->get('request');
            /** @var \Mayflower\JiraIssueVoteBundle\Util\OAuthSecurityAccessProvider $provider */
            $provider = $this->get('ma27_jira_issue_vote.oauth.provider');

            $verifyKey = $request->get('oauth_verifier');
            if (!$verifyKey) {
                throw new HttpException(400, 'Param "oauth_verifier" is required!');
            }

            /** @var \Mayflower\JiraIssueVoteBundle\Entity\OAuthToken $token */
            $token = unserialize($request->getSession()->get(self::OAUTH_TOKEN));

            $request->getSession()->set(
                OAuthSecurityProxy::TEMP_TOKEN_ID,
                $provider->getCredentialsFromAccessUri(
                    $token->getToken(),
                    $token->getTokenSecret(),
                    $verifyKey
                )
            );
            $request->getSession()->set(JiraCredentialsListener::OAUTH_LOGIN_FLAG, 1);

            return $this->redirect($this->generateUrl('ma27_jira_issue_vote_homepage'));
        } catch (ClientErrorResponseException $ex) {
            /** @var \Psr\Log\LoggerInterface $logger */
            $logger = $this->get('logger');
            $logger->emergency('OAuth Token Refused! (Exception: ' . json_encode($ex) . ')');

            return $this->forward(
                'MayflowerJiraIssueVoteBundle:Authorize:invalidateTokens',
                array('error_text' => 'Token refused! Please contact an administrator. ', 'type' => 'danger')
            );
        }
    }

    /**
     * Action which removes the oauth tokens
     *
     * @param Request $request Current http request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invalidateTokensAction(Request $request)
    {
        $url = $this->generateUrl('ma27_jira_issue_vote_verify');

        /** @var \Mayflower\JiraIssueVoteBundle\Util\OAuthSecurityToken $proxy */
        $proxy = $this->get('ma27_jira_issue_vote.oauth.proxy');
        $proxy->removeToken();
        foreach (
            array(
                JiraCredentialsListener::OAUTH_LOGIN_FLAG,
                IssueController::SELECTED_FILTER_ID,
                IssueController::SELECT_FILTER_NAME,
                IssueController::DISABLE_RESOLVED_ID,
                IssueController::DISABLE_REPORTED_ID,
                IssueController::DISABLE_VOTED_ID
            ) as $key) {
            $this->get('session')->remove($key);
        }

        if (null !== $target = $request->get('redirect_url')) {
            return $this->redirect($target);
        }

        return $this->render(
            'MayflowerJiraIssueVoteBundle:Authorize:logged_out.html.twig',
            array('login_url' => $url, 'text' => $request->get('error_text'), 'type' => $request->get('type') ?: 'success')
        );
    }
}
