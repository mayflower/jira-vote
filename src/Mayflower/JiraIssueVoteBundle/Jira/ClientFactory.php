<?php

namespace Mayflower\JiraIssueVoteBundle\Jira;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Mayflower\JiraIssueVoteBundle\Jira\Credentials\AccessToken;
use Mayflower\JiraIssueVoteBundle\Jira\Credentials\JiraOAuthCredentials;

/**
 * ClientFactory
 *
 * @author Maximilian Bosch <ma27.git@gmail.com>
 */
class ClientFactory
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var JiraOAuthCredentials
     */
    private $credentials;

    /**
     * Constructor
     *
     * @param JiraOAuthCredentials $credentials
     * @param string $baseUrl
     */
    public function __construct(JiraOAuthCredentials $credentials, $baseUrl)
    {
        $this->credentials = $credentials;
        $this->baseUrl     = $baseUrl;
    }

    /**
     * Creates a Guzzle5 Http Client
     *
     * @param AccessToken $token
     *
     * @return Client
     */
    public function createClient(AccessToken $token = null)
    {
        if ($this->client) {
            return $this->client;
        }

        $config = [
            'consumer_key'     => $this->credentials->getConsumerKey(),
            'consumer_secret'  => $this->credentials->getConsumerSecret(),
            'signature_method' => Oauth1::SIGNATURE_METHOD_RSA
        ];

        if ($token) {
            $config['token']        = $token->getToken();
            $config['token_secret'] = $token->getTokenSecret();
        }

        $clientConfig = ['base_url' => $this->baseUrl];
        $oauthPlugin  = new Oauth1($config);

        $client = new Client($clientConfig);
        $client->getEmitter()->attach($oauthPlugin);

        return $this->client = $client;
    }
}
