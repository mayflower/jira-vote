<?php
namespace Mayflower\JiraIssueVoteBundle\Util\Generator;

use Symfony\Component\Routing\Router;

/**
 * Url generator for oauth token urls
 *
 * @author Maximilian Bosch <mtb2000@live.de>
 */
class OAuthUrlGenerator implements OAuthCredentialUrlGeneratorInterface
{
    /**
     * Simple generator for server urls
     * @var Url
     */
    private $generator;

    /**
     * Symfony's url generator
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Host name of the client
     * @var string
     */
    private $clientHost;

    /**
     * Sets the dependencies of the class
     *
     * @param string $jiraHost   Name of the server with JIRA
     * @param Router $router     Symfony's router containing the url generator
     * @param string $clientHost Name of the client host
     */
    public function __construct($jiraHost, Router $router, $clientHost)
    {
        $this->generator = new Url($jiraHost);
        $this->urlGenerator = $router->getGenerator();
        $this->clientHost   = $clientHost;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestTokenUrl($callback = null)
    {
        $path = 'plugins/servlet/oauth/request-token';
        if ($callback) {
            $path .= '?oauth_callback=' . $callback;
        }

        return $this->generator->generateUrl($path);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessUrl($callback, $verifier)
    {
        return $this->generator->generateUrl(
            'plugins/servlet/oauth/access-token?oauth_callback=' . $callback . '&oauth_verifier=' . $verifier
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthUrl($token)
    {
        return $this->generator->generateUrl(
            'plugins/servlet/oauth/authorize?oauth_token=' . $token
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutePath($alias)
    {
        return $this->clientHost . $this->urlGenerator->generate($alias);
    }
} 