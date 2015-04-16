<?php
namespace Mayflower\JiraIssueVoteBundle\Entity;

/**
 * Value object which contains the oauth token and token-secret
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class OAuthToken
{
    /**
     * OAuth token
     * @var string
     */
    private $token;

    /**
     * Secret key of the oauth token
     * @var string
     */
    private $tokenSecret;

    /**
     * Sets the oauth token
     *
     * @param string $token OAuth Token
     *
     * @return void
     *
     * @api
     */
    public function setToken($token)
    {
        $this->token = (string)$token;
    }

    /**
     * Returns the oauth token
     *
     * @return string
     *
     * @api
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets the token secret of the oauth token
     *
     * @param string $tokenSecret Secret key of the token
     *
     * @return void
     *
     * @api
     */
    public function setTokenSecret($tokenSecret)
    {
        $this->tokenSecret = (string)$tokenSecret;
    }

    /**
     * Returns the token secret of the oauth token
     *
     * @return string
     *
     * @api
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }
} 