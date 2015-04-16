<?php

namespace Mayflower\JiraIssueVoteBundle\Jira;

/**
 * UrlUtils
 *
 * Class which returns the urls of the jira authentication mechanism
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class UrlUtils
{
    /**
     * Generates the url of the temporary token
     *
     * @param string $baseUrl
     * @param string $callback
     *
     * @return string
     */
    public static function getTempTokenUrl($baseUrl, $callback)
    {
        return sprintf('%s/plugins/servlet/oauth/request-token?oauth_callback=%s', $baseUrl, $callback);
    }

    /**
     * Generates the url of the access approve
     *
     * @param string $baseUrl
     * @param string $token
     *
     * @return string
     */
    public static function getTokenConfirmationUrl($baseUrl, $token)
    {
        return sprintf('%s/plugins/servlet/oauth/authorize?oauth_token=%s', $baseUrl, $token);
    }

    /**
     * OAuth access token url
     *
     * @param string $baseUrl
     * @param string $callbackUrl
     * @param string $verifier
     *
     * @return string
     */
    public static function getAccessTokenUrl($baseUrl, $callbackUrl, $verifier)
    {
        return sprintf(
            '%s/plugins/servlet/oauth/access-token?oauth_callback=%s&oauth_verifier=%s',
            $baseUrl,
            $callbackUrl,
            $verifier
        );
    }
}
