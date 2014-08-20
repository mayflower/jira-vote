<?php
namespace Ma27\Jira\IssueVoteBundle\Controller;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Symfony\Component\HttpFoundation\Request;
use Ma27\Jira\IssueVoteBundle\EventListener\JiraCredentialsListener;
use Ma27\Jira\IssueVoteBundle\Util\OAuthSecurityProxy;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller which is responsible for the authorization of the
 * users
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class AuthorizeController extends Controller
{
    /**
     * Action which renders the "unauthorized"-Template and stores the temporary
     * credentials of the rest users
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @api
     */
    public function indexAction()
    {
        /** @var \Ma27\Jira\IssueVoteBundle\Util\OAuthSecurityAccessProvider $provider */
        $provider = $this->get('ma27_jira_issue_vote.oauth.provider');
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');

        if ($session->has(OAuthSecurityProxy::TEMP_TOKEN_ID)) {
            $session->remove(OAuthSecurityProxy::TEMP_TOKEN_ID);
        }
        $session->remove(JiraCredentialsListener::OAUTH_LOGIN_FLAG);

        $credentials = $provider->getCredentials();
        $session->set(OAuthSecurityProxy::TEMP_TOKEN_ID, $credentials);

        return $this->render(
            'Ma27JiraIssueVoteBundle:Authorize:index.html.twig',
            array(
                'jira_url' => $provider->getAuthUrl()
            )
        );
    }

    /**
     * Handles the JIRA callback and stores the auth token
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @api
     */
    public function callbackAction()
    {
        try {
            /** @var \Symfony\Component\HttpFoundation\Request $request */
            $request = $this->get('request');
            /** @var \Ma27\Jira\IssueVoteBundle\Util\OAuthSecurityAccessProvider $provider */
            $provider = $this->get('ma27_jira_issue_vote.oauth.provider');

            $verifyKey = $request->get('oauth_verifier');
            /** @var \Ma27\Jira\IssueVoteBundle\Entity\OAuthToken $token */
            $token     = $request->getSession()->get(OAuthSecurityProxy::TEMP_TOKEN_ID);
            $request->getSession()->set(OAuthSecurityProxy::TEMP_TOKEN_ID, null);

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
                'Ma27JiraIssueVoteBundle:Authorize:invalidateTokens',
                ['error_text' => 'Token refused! Please contact an administrator. ', 'type' => 'danger']
            );
        }
    }

    /**
     * Action which removes the oauth tokens
     *
     * @param Request $request Current http request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @api
     */
    public function invalidateTokensAction(Request $request)
    {
        $url = $this->generateUrl('ma27_jira_issue_vote_verify');

        /** @var \Ma27\Jira\IssueVoteBundle\Util\OAuthSecurityProxy $proxy */
        $proxy = $this->get('ma27_jira_issue_vote.oauth.proxy');
        $proxy->removeToken();
        $this->get('session')->remove(JiraCredentialsListener::OAUTH_LOGIN_FLAG);
        $this->get('session')->remove(IssueController::SELECTED_FILTER_ID);

        if (null !== $target = $request->get('redirect_url')) {
            return $this->redirect($target);
        }

        return $this->render(
            'Ma27JiraIssueVoteBundle:Authorize:logged_out.html.twig',
            array('login_url' => $url, 'text' => $request->get('error_text'), 'type' => $request->get('type') ?: 'success')
        );
    }
} 