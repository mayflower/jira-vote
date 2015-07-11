<?php

namespace Mayflower\JiraIssueVoteBundle\Controller;

use GuzzleHttp\Exception\RequestException;
use Mayflower\JiraIssueVoteBundle\Jira\TokenFetcher;
use Mayflower\JiraIssueVoteBundle\Jira\UrlUtils;
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
    const TOKEN_DANGER     = 'danger';
    const TOKEN_SUCCESS    = 'success';
    const TOKEN_WARNING    = 'warning';
    const TEMP_OAUTH_TOKEN = 'jira.oauth.temp.token';

    /**
     * Action which renders the "unauthorized"-Template and stores the temporary
     * credentials of the rest users
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /** @var TokenFetcher $tokenFetcher */
        $tokenFetcher = $this->get('mayflower_token_fetcher');
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');

        $credentialSet = $tokenFetcher->requestTempToken();
        $session->set(self::TEMP_OAUTH_TOKEN, serialize($credentialSet));

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
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function callbackAction(Request $request)
    {
        /** @var TokenFetcher $tokenFetcher */
        $tokenFetcher = $this->get('mayflower_token_fetcher');
        /** @var \Mayflower\JiraIssueVoteBundle\Jira\Credentials\AccessToken $token */
        $token = unserialize($request->getSession()->get(self::TEMP_OAUTH_TOKEN));
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');

        if (!$token) {
            return $this->redirect($this->generateUrl('ma27_jira_issue_vote_verify'));
        }

        $verifyKey = $request->get('oauth_verifier');
        if (!$verifyKey) {
            throw new HttpException(400, 'Param "oauth_verifier" is required!');
        }

        try {
            $session->set(TokenFetcher::OAUTH_TOKEN, serialize($tokenFetcher->requestAuthToken($token, $verifyKey)));
        } catch (RequestException $ex) {
            /** @var \Psr\Log\LoggerInterface $logger */
            $logger = $this->get('logger');
            $logger->emergency(sprintf('The oauth token were refused: %s', json_encode($ex)));

            $this->forward(
                'MayflowerJiraIssueVoteBundle:Authorize:invalidateTokens',
                [
                    'error_test' => 'Internal error! Please contact an administrator',
                    'type'       => self::TOKEN_DANGER
                ]
            );
        }

        return $this->redirect($this->generateUrl('ma27_jira_issue_vote_homepage'));
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
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');
        $session->clear();

        if (null !== $target = $request->get('redirect_url')) {
            return $this->redirect($target);
        }

        return $this->render(
            '@MayflowerJiraIssueVote/Authorize/logged_out.html.twig',
            [
                'login_url' => $this->generateUrl('ma27_jira_issue_vote_verify'),
                'text'      => $request->get('error_text'),
                'type'      => $request->get('type') ?: self::TOKEN_SUCCESS
            ]
        );
    }
}
