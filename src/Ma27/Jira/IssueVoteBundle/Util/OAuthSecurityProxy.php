<?php
namespace Ma27\Jira\IssueVoteBundle\Util;

use Ma27\Jira\IssueVoteBundle\Entity\OAuthToken;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Proxy which checks and removes the jira access tokens
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class OAuthSecurityProxy
{
    /**
     * Session-key of the token
     *
     * @var string
     *
     * @api
     */
    const TEMP_TOKEN_ID = 'oauth.security.token';

    /**
     * Session container which stores the access tokens
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * Stores the session
     *
     * @param Session $session
     *
     * @api
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Checks whether a token is stored in the
     * session container
     *
     * @return boolean
     *
     * @api
     */
    public function hasToken()
    {
        return $this->session->get(static::TEMP_TOKEN_ID) instanceof OAuthToken;
    }

    /**
     * Removes the token from the session if it's
     * expired or the user invalidates the manually
     *
     * @return boolean
     *
     * @api
     */
    public function removeToken()
    {
        $this->session->remove(static::TEMP_TOKEN_ID);
    }

    /**
     * Returns the used session
     *
     *
     * @return Session
     *
     * @api
     */
    public function getSession()
    {
        return $this->session;
    }
} 