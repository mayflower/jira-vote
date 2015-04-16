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
    public static function getTempTokenUrl($baseUrl, $callback)
    {
        return sprintf('%s/plugins/servlet/oauth/request-token?callback=%s', $baseUrl, $callback);
    }

    public static function getTokenConfirmationUrl($baseUrl, $token)
    {
        return sprintf('%s/plugins/servlet/oauth/authorize?oauth_token=%s', $baseUrl, $token);
    }
}
