<?php
namespace Mayflower\JiraIssueVoteBundle\Util;

use Guzzle\Http\Client;
use Mayflower\JiraIssueVoteBundle\Entity\OAuthToken;

/**
 * Customized jira client
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 *
 * @deprecated
 */
class JiraClient extends Client
{
    /**
     * Instance of the oauth token
     * @var \Mayflower\JiraIssueVoteBundle\Entity\OAuthToken
     */
    private $oauthToken;

    /**
     * Configures the client
     *
     * @param string $baseUrl Base url of the jira server
     * @param mixed  $config  Optional config list
     *
     * @api
     */
    public function __construct($baseUrl = '', $config = null)
    {
        parent::__construct($baseUrl, $config);

        $this->oauthToken = new OAuthToken();
    }

    /**
     * Parses the token from the request
     *
     * @param string $requestResult Result of the request
     *
     * @return OAuthToken
     *
     * @api
     */
    public function storeToken($requestResult)
    {
        parse_str($requestResult, $params);

        $this->oauthToken->setToken($params['oauth_token']);
        $this->oauthToken->setTokenSecret($params['oauth_token_secret']);

        return $this->oauthToken;
    }

    /**
     * Sets the oauth token
     *
     * @param string $token Token to set
     *
     * @api
     */
    public function setOAuthToken($token)
    {
        $this->oauthToken->setToken($token);
    }

    /**
     * Returns the oauth token
     *
     * @return string
     *
     * @api
     */
    public function getOAuthToken()
    {
        return $this->oauthToken->getToken();
    }

    /**
     * Sets the secret key of the oauth access token
     *
     * @param string $tokenSecret Secret key
     *
     * @api
     */
    public function setOAuthTokenSecret($tokenSecret)
    {
        $this->oauthToken->setTokenSecret($tokenSecret);
    }

    /**
     * Returns the secret key
     *
     * @return string
     *
     * @api
     */
    public function getOAuthTokenSecret()
    {
        return $this->oauthToken->getTokenSecret();
    }
} 