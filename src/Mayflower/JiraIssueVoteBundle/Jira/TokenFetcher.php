<?php

namespace Mayflower\JiraIssueVoteBundle\Jira;

use GuzzleHttp\Client;
use Mayflower\JiraIssueVoteBundle\Jira\Credentials\AccessToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * TokenFetcher
 *
 * Service which fetches the credentials from jira
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class TokenFetcher
{
    const OAUTH_TOKEN = 'oauth';

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var ClientFactory
     */
    private $factory;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @param ClientFactory $factory
     * @param string $baseUrl
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, ClientFactory $factory, $baseUrl)
    {
        $this->baseUrl   = $baseUrl;
        $this->factory   = $factory;
        $this->generator = $urlGenerator;
    }

    /**
     * Requests a temporary oauth token from JIRA
     *
     * @return AccessToken
     */
    public function requestTempToken()
    {
        return $this->getToken(
            $this->factory->createClient(),
            UrlUtils::getTempTokenUrl(
                $this->baseUrl,
                $this->generator->generate(
                    'ma27_jira_issue_vote_verify_callback',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            )
        );
    }

    /**
     * Requests the authentication token
     *
     * @param AccessToken $token
     * @param string $verifier
     *
     * @return AccessToken
     */
    public function requestAuthToken(AccessToken $token, $verifier)
    {
        return $this->getToken(
            $this->factory->createClient($token),
            UrlUtils::getAccessTokenUrl(
                $this->baseUrl,
                $this->generator->generate(
                    'ma27_jira_issue_vote_verify_callback',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                $verifier
            )
        );
    }

    /**
     * Requests the token from jira oauth
     *
     * @param Client $client
     * @param string $uri
     *
     * @return AccessToken
     */
    private function getToken(Client $client, $uri)
    {
        $raw = (string) $client->post($uri, ['config' => ['auth' => 'oauth']])->getBody();
        parse_str($raw, $params);

        return new AccessToken($params['oauth_token'], $params['oauth_token_secret']);
    }
}
