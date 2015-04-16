<?php
namespace Mayflower\JiraIssueVoteBundle\Util\Generator;

/**
 * Interface which provides a generator for any oauth host to generate important urls
 * like the request token url
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
interface OAuthCredentialUrlGeneratorInterface
{
    /**
     * Returns the url to request the oauth token
     *
     * @param string $callback Optional oauth callback
     *
     * @return string
     *
     * @api
     */
    public function getRequestTokenUrl($callback = null);

    /**
     * Returns the oauth access token url
     *
     * @param string $callback Callback url
     * @param string $verifier Verifier key
     *
     * @return string
     *
     * @api
     */
    public function getAccessUrl($callback, $verifier);

    /**
     * Returns the url to authorize the consumer
     *
     * @param string $token User token
     *
     * @return string
     *
     * @api
     */
    public function getAuthUrl($token);

    /**
     * Returns the path of any client route
     *
     * @param string $alias Route alias
     *
     * @return string
     *
     * @api
     */
    public function getRoutePath($alias);
} 