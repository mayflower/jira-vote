<?php
namespace Mayflower\JiraIssueVoteBundle\Util;

use Mayflower\JiraIssueVoteBundle\Util\JiraClient;
use Mayflower\JiraIssueVoteBundle\Util\Generator\OAuthCredentialUrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Provider for oauth to communicate with Jira
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 *
 * @deprecated
 */
class OAuthSecurityAccessProvider implements RestProviderInterface
{
    /**
     * Http client
     * @var JiraClient
     */
    private $client;

    /**
     * Factory which creates the oauth request plugin
     * @var OAuthPluginFactory
     */
    private $oauthFactory;

    /**
     * Application consumer key
     * @var string
     */
    private $consumerKey;

    /**
     * Application consumer secret
     * @var string
     */
    private $consumerSecret;

    /**
     * Concrete url generator for the rest api
     * @var OAuthCredentialUrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Sets the class dependencies
     *
     * @param JiraClient                           $client     HTTP client
     * @param OAuthCredentialUrlGeneratorInterface $generator  Generator which provides the credential urls of an oauth client
     *
     * @api
     */
    public function __construct(JiraClient $client, OAuthCredentialUrlGeneratorInterface $generator)
    {
        $this->client = $client;
        $this->urlGenerator = $generator;
    }

    /**
     * Stores the whole config of the oauth plugin
     *
     * @param OAuthPluginFactory $factory
     *
     * @return void
     *
     * @api
     */
    public function setPluginFactory(OAuthPluginFactory $factory)
    {
        $this->oauthFactory = $factory;

        $pluginConfig         = $factory->getPluginConfig();
        $this->consumerKey    = $pluginConfig['consumer_key'];
        $this->consumerSecret = $pluginConfig['consumer_secret'];
    }

    /**
     * Executes an request with the oauth plugin and formats the result to json
     * (JIRA uses by default json as response type)
     *
     * @param string   $uri     Target uri on the server
     * @param string   $method  Method of the request
     * @param string[] $headers List of headers
     * @param boolean  $json    Converts result to json
     *
     * @return mixed
     *
     * @api
     */
    public function processJiraRequest($uri, $method = 'post', array $headers = array(), $json = true)
    {
        $this->client->addSubscriber($this->oauthFactory->getPlugin());
        $request = $this->client->createRequest(
            mb_strtoupper($method),
            $uri,
            array_merge(array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept'       => 'application/json, application/xml, text/html, text/plain'
            ), $headers)
        );

        $response = $request->send();
        if ($json) {
            return $response->json();
        }
        return $response->getBody(true);
    }

    /**
     * Returns the application client
     *
     * @return JiraClient
     *
     * @api
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Returns the credentials of a logged-in user
     *
     * @return \Mayflower\JiraIssueVoteBundle\Entity\OAuthToken
     *
     * @api
     */
    public function getCredentials()
    {
        return $this->fetchTokensFromJira(
            $this->urlGenerator->getRequestTokenUrl(
                $this->urlGenerator->getRoutePath('ma27_jira_issue_vote_verify_callback')
            )
        );
    }

    /**
     * Accesses the auth credentials by the requested credentials and the verifier
     *
     * @param string $token       Requested token
     * @param string $tokenSecret Secret key of the token
     * @param string $verifier    Verifier key from jira
     *
     * @return \Ma27\Jira\IssueVoteBundle\Entity\OAuthToken
     *
     * @api
     */
    public function getCredentialsFromAccessUri($token, $tokenSecret, $verifier)
    {
        return $this->fetchTokensFromJira(
            $this->urlGenerator->getAccessUrl(
                $this->urlGenerator->getRoutePath('ma27_jira_issue_vote_verify_callback'),
                $verifier
            ),
            $token,
            $tokenSecret
        );
    }

    /**
     * Returns the tokens from jira with a
     *
     * @param string $uri         Uri to fetch from
     * @param string $token       Token (optional in case of the access request)
     * @param string $tokenSecret Token-secret (optional in case of access requests)
     *
     * @return \Mayflower\JiraIssueVoteBundle\Entity\OAuthToken
     *
     * @api
     */
    protected function fetchTokensFromJira($uri, $token = null, $tokenSecret = null)
    {
        if (!in_array(null, array($token, $tokenSecret))) {
            $this->client->getEventDispatcher()->removeSubscriber($this->oauthFactory->getPlugin());

            $this->oauthFactory->appendConfiguration(array('token' => $token, 'token_secret' => $tokenSecret));
            $this->client->addSubscriber($this->oauthFactory->getPlugin());
            $this->oauthFactory->resetConfiguration($this->consumerKey, $this->consumerSecret);
        } else {
            $this->client->addSubscriber($this->oauthFactory->getPlugin());
        }

        $tokenString = $this->client->post($uri, array('Content-Type: application/json'))->send()->getBody(true);
        return $this->client->storeToken($tokenString);
    }

    /**
     * Generates the authentication url
     *
     * @return string
     *
     * @api
     */
    public function getAuthUrl()
    {
        return $this->urlGenerator->getAuthUrl($this->client->getOAuthToken());
    }
}
